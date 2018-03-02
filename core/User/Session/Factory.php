<?php

namespace tiFy\Core\User\Session;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

final class Factory extends \tiFy\App\FactoryConstructor
{
    /**
     * Identifiant de qualification du cookie de stockage de session
     * @var string
     */
    private $CookieName = '';

    /**
     * Listes des attributs de qualification de session portés par le cookie
     * @var string[]
     */
    private $CookieArgs = ['session_key', 'session_expiration', 'session_expiring', 'cookie_hash'];

    /**
     * Liste des attributs de qualification de session
     * @var array
     */
    private $Session = [];

    /**
     * Liste des variables de session enregistrées en base
     * @var array
     */
    private $Datas = [];

    /**
     * Indicateur de modification des variables de session
     * @var bool
     */
    private $Changed = false;

    /**
     * CONSTRUCTEUR
     *
     * @param string $id Identifiant de qualification
     * @param array $attrs Attributs de configuration
     *
     * @return void
     */
    public function __construct($id, $attrs = [])
    {
        // Bypass
        /*if (is_admin() && !defined('DOING_AJAX')) :
            return;
        endif;
        if (defined('DOING_CRON')) :
            return;
        endif;
        */
        parent::__construct($id, $attrs);

        // Définition de l'identifiant de qualification du cookie de stockage de session
        $this->CookieName = $this->getId() . "-" . COOKIEHASH;

        // Déclaration des événements
        $this->appAddAction('init');
        $this->appAddAction('wp', null, 99);
        $this->appAddAction('wp_logout');
        $this->appAddAction('shutdown');
    }

    /**
     * EVENEMENTS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public function init()
    {
        // Initialisation de la liste des attributs de qualification de la session
        $this->initSession();
    }

    /**
     * A l'issue de la définition de l'environnement Wordpress
     *
     * @return void
     */
    public function wp()
    {
        // Définition du cookie de session
        $this->setCookie();
    }

    /**
     * Au moment de la deconnection
     *
     * @return void
     */
    public function wp_logout()
    {
        // Destruction de la session
        $this->destroy();
    }

    /**
     * A l'issue de l'execution de PHP
     *
     * @return void
     */
    public function shutdown()
    {
        // Sauvegarde des données en base
        $this->save();
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération de la classe de rappel de la table de base de données
     *
     * @return \tiFy\Core\Db\Factory
     */
    private function getDb()
    {
        try {
            $Db = Session::getDb();

            return $Db;
        } catch (\Exception $e) {
            wp_die($e->getMessage(), __('ERREUR SYSTEME', 'tify'), $e->getCode());
            exit;
        }
    }

    /**
     * Récupération du nom de qualification du cookie d'enregistrement de correspondance de session
     *
     * @return string
     */
    private function getCookieName()
    {
        return $this->CookieName;
    }

    /**
     * Récupération du hashage de cookie
     *
     * @param int|string $session_key Identifiant de qualification de l'utilisateur courant
     * @param int $expiration Timestamp d'expiration du cookie
     *
     * @return string
     */
    private function getCookieHash($session_key, $expiration)
    {
        $to_hash = $session_key . '|' . $expiration;

        return hash_hmac('md5', $to_hash, \wp_hash($to_hash));
    }

    /**
     * Récupération de l'identifiant de qualification.
     *
     * @return string
     */
    private function getKey()
    {
        require_once(ABSPATH . 'wp-includes/class-phpass.php');
        $hasher = new \PasswordHash(8, false);

        return md5($hasher->get_random_bytes(32));
    }

    /**
     * Récupération d'un, plusieurs ou tous les attributs de qualification de la session.
     *
     * @param array $session_args Liste des attributs de retour session_key|session_expiration|session_expiring|cookie_hash. Renvoi tous si vide.
     *
     * @return mixed
     */
    public function getSession($session_args = [])
    {
        // Récupération des attributs de qualification de la session
        if (!$session = $this->Session) :
            return null;
        endif;
        extract($session);

        if (empty($session_args)) :
            $session_args = $this->CookieArgs;
        elseif (!is_array($session_args)) :
            $session_args = (array)$session_args;
        endif;

        // Limitation des attributs retournés à la liste des attributs autorisés
        $session_args = array_intersect($session_args, $this->CookieArgs);

        if (count($session_args) > 1) :
            return compact($session_args);
        else :
            return ${reset($session_args)};
        endif;
    }

    /**
     * Récupération de la prochaine date de définition d'expiration de session
     *
     * @return int
     */
    private function nextSessionExpiration()
    {
        return time() + intval(60 * 60 * 48);
    }

    /**
     * Récupération de la prochaine date de définition de session expirée
     *
     * @return int
     */
    private function nextSessionExpiring()
    {
        return time() + intval(60 * 60 * 47);
    }

    /**
     * Initialisation des attributs de qualification de session
     *
     * @return array
     */
    private function initSession()
    {
        /**
         * @var array $cookie {
         *      Attribut de session contenu dans le cookie
         *
         *      @var string|int $session_key
         *      @var int $session_expiration
         *      @var int $session_expiring
         *      @var string $cookie_hash
         * }
         */
        if ($cookie = $this->getCookie()) :
            extract($cookie);

            if (time() > $session_expiring) :
                $session_expiration = $this->nextSessionExpiration();
                $this->updateExpiration($session_key, $session_expiration);
            endif;

            $this->Datas = $this->getDatas($session_key);
        else :
            $session_key = $this->getKey();
            $session_expiration = $this->nextSessionExpiration();
        endif;

        $session_expiring = $this->nextSessionExpiring();
        $cookie_hash = $this->getCookieHash($session_key, $session_expiration);

        return $this->Session = compact($this->CookieArgs);
    }

    /**
     * Récupération du cookie de session
     *
     * @return mixed
     */
    private function getCookie()
    {
        if (!$cookie = $this->appRequestGet($this->getCookieName(), '', 'COOKIE')) :
            return false;
        endif;

        if (!$cookie = (array)json_decode(rawurldecode($cookie), true)) :
            return false;
        endif;

        // Vérification de correspondance entre les attributs servis par le cookie et les données attendues.
        if (array_diff(array_keys($cookie), $this->CookieArgs)) :
            return false;
        endif;

        /**
         * @var string|int $session_key
         * @var int $session_expiration
         * @var int $session_expiring
         * @var string $cookie_hash
         */
        extract($cookie);

        // Contrôle de validité du cookie
        $hash = $this->getCookieHash($session_key, $session_expiration);
        if (empty($cookie_hash) || !\hash_equals($hash, $cookie_hash)) :
            return false;
        endif;

        return compact($this->CookieArgs);
    }

    /**
     * Définition d'un cookie de session
     *
     * @param $string $name Identifiant de qualification de l'attribut de session
     * @param $string $value Valeur d'affectation de l'attribut de session
     *
     * @return void
     */
    private function setCookie()
    {
        // Récupération des attributs de qualification de la session
        $session = $this->getSession();

        // Définition du cookie
        $response = new Response();
        $response->headers->setCookie(
            new Cookie(
                $this->getCookieName(),
                rawurlencode(json_encode($session)),
                time() + 3600,
                ((COOKIEPATH != SITECOOKIEPATH) ? SITECOOKIEPATH : COOKIEPATH),
                COOKIE_DOMAIN,
                ('https' === parse_url(home_url(), PHP_URL_SCHEME))
            )
        );
        $response->send();
    }

    /**
     * Suppression du cookie de session
     *
     * @return void
     */
    private function clearCookie()
    {
        $response = new Response();
        $response->headers->clearCookie(
            $this->getCookieName(),
            ((COOKIEPATH != SITECOOKIEPATH) ? SITECOOKIEPATH : COOKIEPATH),
            COOKIE_DOMAIN,
            ('https' === parse_url(home_url(), PHP_URL_SCHEME))
        );
        $response->send();
    }

    /**
     * Récupération de la liste des variables de session enregistrés en base.
     *
     * @param mixed $session_key Clé de qualification de la session
     *
     * @return array
     */
    private function getDatas($session_key)
    {
        if (defined('WP_SETUP_CONFIG')) :
            return [];
        endif;

        $value = $this->getDb()->select()->cell(
            'session_value',
            [
                'session_name' => $this->getId(),
                'session_key'  => $session_key
            ]
        );

        return maybe_unserialize($value);
    }

    /**
     * Enregistrement des variables de session en base.
     *
     * @return mixed
     */
    private function save()
    {
        if (!$this->Changed) :
            return false;
        endif;

        // Récupération des attributs de session
        $session = $this->getSession();

        $this->getDb()->handle()->replace(
            [
                'session_name'   => $this->getId(),
                'session_key'    => $session['session_key'],
                'session_value'  => maybe_serialize($this->Datas),
                'session_expiry' => $session['session_expiration']
            ],
            ['%s', '%s', '%s', '%d']
        );

        $this->Changed = false;
    }

    /**
     * Mise à jour de la date d'expiration de la session en base.
     *
     * @param mixed $session_key Clé de qualification de la session
     * @param string $expiration Timestamp d'expiration de la session
     *
     * @return void
     */
    private function updateExpiration($session_key, $expiration)
    {
        $this->getDb()->sql()->update(
            $this->getDb()->getName(),
            [
                'session_expiry' => $expiration
            ],
            [
                'session_name' => $this->getId(),
                'session_key'  => $session_key
            ]
        );
    }

    /**
     * Destruction de la session
     *
     * @return void
     */
    private function destroy()
    {
        // Suppression du cookie
        $this->clearCookie();

        // Suppression de la session en base
        $this->getDb()->handle()->delete(
            [
                'session_key' => $this->getSession('session_key'),
            ]
        );

        // Réinitialisation des variables de classe
        $this->Session = [];
        $this->Datas = [];
        $this->Changed = false;
    }

    /**
     * Récupération d'une variable de session.
     *
     * @param string $name Identifiant de qualification de la variable
     * @param mixed $default Valeur de retour par défaut
     *
     * @return array|string
     */
    public function get($name, $default = '')
    {
        return isset($this->Datas[$name]) ? \maybe_unserialize($this->Datas[$name]) : $default;
    }

    /**
     * Définition d'une variable de session.
     *
     * @param string $name Identifiant de qualification de la variable
     * @param mixed $value Valeur de la variable
     *
     * @return $this
     */
    public function add($name, $value)
    {
        if ($value !== $this->get($name)) :
            $this->Datas[$name] = \maybe_serialize($value);
            $this->Changed = true;
        endif;

        return $this;
    }
}