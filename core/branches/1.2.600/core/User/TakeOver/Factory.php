<?php

namespace tiFy\Core\User\TakeOver;

use League\Event\Event;
use League\Event\Emitter;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class Factory extends \tiFy\App\FactoryConstructor
{
    /**
     * Emetteur d'évenéments.
     * @return \League\Event\Emitter
     */
    private $Emitter = null;

    /**
     * Nom du cookie d'authentication pour la conservation de l'utilisateur principal
     * @var string
     */
    private $AuthCookieName = '';

    /**
     * Nom du cookie de connection pour la conservation de l'utilisateur principal
     * @var string
     */
    private $LoggedInCookieName = '';

    /**
     * Droit de prise de contrôle de compte d'un utilisateur par l'utilisateur courant
     * @var bool
     */
    private $CanSwitch = false;

    /**
     * CONSTRUCTEUR
     *
     * @param string $id Identifiant de qualification
     * @param array $attrs {
     *      Liste des attributs de configuration
     *
     *      @var array $auth_roles Liste des rôles autorisés à prendre le contrôle d'un utilsateur. ['administrator'] si non pas défini.
     *      @var array $auth_users Liste des utilisateurs autorisés à prendre le contrôle d'un autre utlisateur. vide par défaut.
     *      @var array $allowed_roles Liste des rôles utilisateurs pour lesquels la prise de contrôle est autorisés. ['subscriber'] si non pas défini.
     *      @var array $allowed_users Liste des utilisateurs pour lesquels la prise de contrôle est permise. vide par défaut.
     * }
     *
     * @return void
     */
    public function __construct($id, $attrs = [])
    {
        parent::__construct($id, $attrs);

        // Définition des Noms de cookie pour la conservation de l'utilisateur principal
        $this->AuthCookieName = 'tify_takeover_auth_cookie_' . COOKIEHASH;
        $this->LoggedInCookieName = 'tify_takeover_logged_in_' . COOKIEHASH;

        // Instanciation des événements
        $this->Emitter = new Emitter();

        // Déclaration des événements
        $this->Emitter->addListener('tiFy.Core.User.TakeOver.canSwitch.' . $this->getId(), [$this, 'eventCanSwitch']);

        // Déclaration des événements de déclenchement
        $this->tFyAppAddAction('wp_loaded');

        if (!is_user_logged_in()) :
            $this->clearCookies();
        endif;
    }

    /**
     * EVENEMENTS
     */
    /**
     * Evenement de vérification de permission d'appel de prise de contrôle du compte d'un utilisateur (called) par un autre (caller)
     * @see \tiFy\Core\User\TakeOver\TakeOver::canSwitch
     *
     * @param \WP_User $caller Objet utilisateur de l'appelant
     * @param \WP_User $called Objet utilisateur de l'appelé
     *
     * @return void
     */
    final public function eventCanSwitch(Event $event, \WP_User $caller, \WP_User $called)
    {
        if ($event->getName() !== 'tiFy.Core.User.TakeOver.canSwitch.' . $this->getId()) :
            $this->CanSwitch = false;
        endif;

        $this->CanSwitch = $this->canSwitch($caller, $called);
    }

    /**
     * DECLENCHEURS
     */
    /**
     * A l'issue du chargement complet
     *
     * @return void
     */
    final public function wp_loaded()
    {
        if (self::tFyAppGetRequestVar('tfy_take_over_id', '') !== $this->getId()) :
            return;
        endif;

        // Traitement de l'action
        switch(self::tFyAppGetRequestVar('action', '')) :
            // Prise de contrôle du compte d'un utilisateur
            case 'switch' :
                check_admin_referer('tiFyTakeOver-switch');

                $user_id = self::tFyAppGetRequestVar('user_id', 0);

                if (!$this->_canSwitch($user_id)) :
                    \wp_die(__('Vous ne disposez pas des habilitations suffisantes pour effectuer cette action. ', 'tify'), __('Habilitations insuffisantes', 'tify'), 500);
                endif;

                $this->handleSwitch($user_id);

                wp_redirect(home_url('/'));
                break;

            // Récupération de l'utilisateur principal
            case 'restore' :
                check_admin_referer('tiFyTakeOver-restore');

                if (!$this->handleRestore()) :
                    \wp_die(__('Vous ne disposez pas des habilitations suffisantes pour effectuer cette action. ', 'tify'), __('Habilitations insuffisantes', 'tify'), 500);
                endif;

                wp_redirect(home_url('/'));
                break;

            // Action non définie
            default :
                \wp_die(__('Il semblerait que tout ne se soit pas vraiment déroulé comme prévu ?!', 'tify'), __('Erreur de traitement', 'tify'), 500);
                break;
        endswitch;

        exit;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Traitement des attributs de configuration
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return array
     */
    protected function parseAttrs($attrs = [])
    {
        if (!isset($attrs['auth_roles'])) :
            $attrs['auth_roles'] = ['administrator'];
        endif;
        if (!isset($attrs['allowed_roles'])) :
            $attrs['allowed_roles'] = ['subscriber'];
        endif;

        return $attrs;
    }

    /**
     * Vérification de la permission de prise de contrôle d'un utilisateur par l'utilisateur principal courant
     *
     * @param WP_User $wp_user Utilisateur à contrôler
     *
     * @return
     */
    private function _canSwitch($user)
    {
        if (!$this->isAuth('switch')) :
            $this->CanSwitch = false;
        elseif (!$this->isAllowed($user)) :
            $this->CanSwitch = false;
        else :
            $caller = \wp_get_current_user();
            $called = $this->getUserData($user);

            $this->Emitter->emit('tiFy.Core.User.TakeOver.canSwitch.'. $this->getId(), $caller, $called);
        endif;

        return $this->CanSwitch;
    }

    /**
     * Prise de contrôle d'un compte utilisateur
     *
     * @param int|WP_User $user
     *
     * @return void
     */
    private function handleSwitch($user)
    {
        if (!is_user_logged_in()) :
            return;
        endif;

        if ($user instanceof \WP_User) :
            $user_id = $user->ID;
        else :
            $user_id = $user;
        endif;

        if ($this->setCookies()) :
            \wp_set_auth_cookie((int)$user_id);
        endif;
    }

    /**
     * Récupération de l'utilisateur principal courant
     *
     * @return int
     */
    private function handleRestore()
    {
        if (!$user = $this->isAuth('restore')) :
            return 0;
        endif;

        $this->clearCookies();
        \wp_clear_auth_cookie();
        \wp_set_auth_cookie((int)$user->ID);

        return true;
    }

    /**
     * Définition des Cookies
     *
     * @var string $cookie_name Identification de qualification du cookie
     * @var int $cookie_expire Nombre de secondes avant expiration du cookie
     *
     * @return bool
     */
    private function setCookies()
    {
        // Bypass - Vérification des autorisations utilisateur
        if (is_blog_admin() || is_network_admin() || empty($_COOKIE[LOGGED_IN_COOKIE])) :
            return false;
        endif;

        // Bypass - Récupération des données d'authentification
        if (!$auth_datas = wp_parse_auth_cookie()) :
            return false;
        endif;

        // Bypass - Récupération des données de connection
        if (!$logged_in_datas = wp_parse_auth_cookie($_COOKIE[LOGGED_IN_COOKIE], 'logged_in')) :
            return false;
        endif;

        // Définition des données de cookie d'authentification et de connection
        $auth_cookie = $auth_datas['username'] . '|' . $auth_datas['expiration'] . '|' . $auth_datas['token'] . '|' . $auth_datas['hmac'];
        $logged_in_cookie = $logged_in_datas['username'] . '|' . $logged_in_datas['expiration'] . '|' . $logged_in_datas['token'] . '|' . $logged_in_datas['hmac'];

        // Définition de la sécurité des cookies
        $secure = ('https' === parse_url(home_url(), PHP_URL_SCHEME));

        // Génération des cookies de conservation de l'utilisateur principal
        $response = new Response();
        $response->headers->setCookie(
            new Cookie(
                $this->AuthCookieName,
                $auth_cookie,
                0,
                PLUGINS_COOKIE_PATH,
                COOKIE_DOMAIN,
                $secure
            )
        );
        $response->headers->setCookie(
            new Cookie(
                $this->AuthCookieName,
                $auth_cookie,
                0,
                ADMIN_COOKIE_PATH,
                COOKIE_DOMAIN,
                $secure
            )
        );
        $response->headers->setCookie(
            new Cookie(
                $this->LoggedInCookieName,
                $logged_in_cookie,
                0,
                COOKIEPATH,
                COOKIE_DOMAIN,
                $secure
            )
        );
        if (COOKIEPATH != SITECOOKIEPATH) :
            $response->headers->setCookie(
                new Cookie(
                    $this->LoggedInCookieName,
                    $logged_in_cookie,
                    0,
                    SITECOOKIEPATH,
                    COOKIE_DOMAIN,
                    $secure
                )
            );
        endif;

        // Envoi de la réponse
        $send = $response->send();

        // Récupération de la liste des cookies
        $cookies = $send->headers->getCookies();

        // Retour du succès de création des cookies
        return !empty($cookies);
    }

    /**
     *
     */
    private function clearCookies()
    {
        // Définition de la sécurité des cookies
        $secure = ('https' === parse_url(home_url(), PHP_URL_SCHEME));

        // Suppression des cookies de conservation de l'utilisateur principal
        $response = new Response();
        $response->headers->clearCookie(
            $this->AuthCookieName,
            PLUGINS_COOKIE_PATH,
            COOKIE_DOMAIN,
            $secure
        );
        $response->headers->clearCookie(
            $this->AuthCookieName,
            ADMIN_COOKIE_PATH,
            COOKIE_DOMAIN,
            $secure
        );
        $response->headers->clearCookie(
            $this->LoggedInCookieName,
            COOKIEPATH,
            COOKIE_DOMAIN,
            $secure
        );
        if (COOKIEPATH != SITECOOKIEPATH) :
            $response->headers->clearCookie(
                $this->LoggedInCookieName,
                SITECOOKIEPATH,
                COOKIE_DOMAIN,
                $secure
            );
        endif;

        // Envoi de la réponse
        $response->send();
    }

    /**
     * Contrôle des cookies d'authentification et récupération de l'utilisateur principal
     *
     * @return int
     */
    private function checkCookies()
    {
        if (
            (!$auth_cookie = self::tFyAppGetRequestVar($this->AuthCookieName, '', 'COOKIE')) ||
            (!$logged_in_cookie = self::tFyAppGetRequestVar($this->LoggedInCookieName, '', 'COOKIE'))
        ) :
            return 0;
        endif;

        if (!wp_validate_auth_cookie($auth_cookie, (is_ssl() ? 'secure_auth' : 'auth'))) :
            return 0;
        endif;

        if (!$user_id = wp_validate_auth_cookie($logged_in_cookie, 'logged_in')) :
            return 0;
        endif;

        return $user_id;
    }

    /**
     * Récupération des données utilisateurs selon son ID, son login ou l'object Wordpress \WP_User
     *
     * @param int|string|\WP_User $user
     *
     * @return \WP_User
     */
    final public function getUserData($user)
    {
        if (is_a($user, 'WP_User')) :
            return $user;
        elseif (is_numeric($user)) :
            return \get_userdata((int)$user);
        elseif (is_string($user)) :
            return \get_user_by('login', $user);
        endif;
    }

    /**
     * Vérification des autorisations de l'utilisateur principal courant
     *
     * @param string $action Type d'action. 'switch': prise de contrôle d'un utilisateur|'restore': Récupération de l'utilsateur principal.
     *
     * @return bool|WP_User
     */
    final public function isAuth($action = 'switch')
    {
        if (!is_user_logged_in()) :
            return false;
        endif;

        // Récupération de l'utilisateur principale courant
        if ($action === 'switch') :
            $user = \wp_get_current_user();
        elseif(($action === 'restore') && ($user_id = $this->checkCookies())) :
            $user = get_userdata($user_id);
        else :
            return false;
        endif;

        // Test d'intégrité de l'utilisateur récupéré
        if (!is_a($user, 'WP_User')) :
            return false;
        endif;

        // Vérification des autorisations pour le rôle de l'utilisateur courant
        if (!array_intersect($user->roles, $this->getAttr('auth_roles', []))) :
            return false;
        endif;

        // Vérification des autorisations parmis la liste des utilisateurs habilités
        if ($auth_users = $this->getAttr('auth_users', [])) :
            $users = [];

            foreach($auth_users as $auth_user) :
                if (!$user_data = $this->getUserData($auth_user)) :
                    continue;
                endif;
                $users[] = $user_data;
            endforeach;

            if (!in_array($user, $users)) :
                return false;
            endif;
        endif;

        return $user;
    }

    /**
     * Vérification des permissions de prise de contrôle d'un utilisateur
     *
     * @param WP_User $user Utilisateur à contrôler
     *
     * @return
     */
    final public function isAllowed($user)
    {
        if (!$user = $this->getUserData($user)) :
            return false;
        endif;

        // Vérification des autorisations pour le rôle de l'utilisateur courant
        if (!array_intersect($user->roles, $this->getAttr('allowed_roles', []))) :
            return false;
        endif;

        // Vérification des autorisations parmis la liste des utilisateurs habilités
        if ($allowed_users = $this->getAttr('allowed_users', [])) :
            $users = [];

            foreach($allowed_users as $allowed_user) :
                if (!$user_data = $this->getUserData($allowed_user)) :
                    continue;
                endif;
                $users[] = $user_data;
            endforeach;

            if (!in_array($user, $users)) :
                return false;
            endif;
        endif;

        return true;
    }

    /**
     * Récupération de la liste des rôles
     */
    final public function getAllowedRoleList()
    {
        return $this->getAttr('allowed_roles');
    }

    /**
     * SURCHARGE
     */
    /**
     * Vérifie de permission d'appel de prise de contrôle du compte d'un utilisateur (called) par un autre (caller)
     *
     * @param \WP_User $caller Objet utilisateur de l'appelant
     * @param \WP_User $called Objet utilisateur de l'appelé
     *
     * @return bool
     */
    public function canSwitch($caller, $called)
    {
        return true;
    }
}