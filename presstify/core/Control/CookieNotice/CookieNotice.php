<?php
/**
 * @name CookieNotice
 * @desc Controleur d'affichage de notice désactivable par le biais d'un cookie
 * @package presstiFy
 * @namespace tiFy\Core\Control\CookieNotice
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\CookieNotice;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * @Overrideable \App\Core\Control\CookieNotice\CookieNotice
 *
 * <?php
 * namespace \App\Core\Control\CookieNotice
 *
 * class CookieNotice extends \tiFy\Core\Control\CookieNotice\CookieNotice
 * {
 *
 * }
 */

class CookieNotice extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     * @var string
     */
    protected $ID = 'cookie_notice';

    /**
     * Requête globale
     * @var \Symfony\Component\HttpFoundation\Request::createFromGlobals
     */
    private static $Request = null;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Définition de la requête globale
        if (!self::$Request) :
            self::$Request = Request::createFromGlobals();
        endif;

        // Actions ajax
        $this->tFyAppAddAction(
            'wp_ajax_tiFyControlCookieNotice',
            'wp_ajax'
        );
        $this->tFyAppAddAction(
            'wp_ajax_nopriv_tiFyControlCookieNotice',
            'wp_ajax'
        );
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public static function init()
    {
        \wp_register_script(
            'tify_control-cookie_notice',
            self::tFyAppAssetsUrl('CookieNotice.js', get_class()),
            ['jquery'],
            170626,
            true
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    public static function enqueue_scripts()
    {
        \wp_enqueue_script('tify_control-cookie_notice');
    }

    /**
     * Génération du cookie de notification via Ajax
     *
     * @return void
     */
    public function wp_ajax()
    {
        check_ajax_referer('tiFyControlCookieNotice');

        // Récupération des arguments de création du cookie
        $cookie_name = $_POST['cookie_name'];
        $cookie_hash = $_POST['cookie_hash'];
        $cookie_expire = $_POST['cookie_expire'];

        // Traitement du hashage
        if (!$cookie_hash) :
            $cookie_hash = '';
        elseif ($cookie_hash == 'true') :
            $cookie_hash = '_'. COOKIEHASH;
        endif;

        $this->setCookie($cookie_name . $cookie_hash, $cookie_expire);

        wp_die(1);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $attrs {
     *      Liste des attributs de configuration
     *
     *      @param string $id Identifiant de qualification du contrôleur d'affichage
     *      @param string $container_id ID HTML du conteneur de notification
     *      @param string $container_class Classe HTML du conteneur de notification
     *      @param string $cookie_name Nom de qualification du cookie
     *      @param bool|string $cookie_hash Activation d'ajout d'un hashage dans le nom de qualification du cookie
     *      @param int $cookie_expire Nombre de seconde avant expiration du cookie
     *      @param string $text Texte de notification
     * }
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    protected static function display($attrs = [], $echo = true)
    {
        // Traitement des attributs de configuration
        $defaults = [
            'id'              => 'tiFyControlCookieNotice-' . self::$Instance,
            'container_id'    => 'tiFyControlCookieNotice--' . self::$Instance,
            'container_class' => '',
            'cookie_name'     => '',
            'cookie_hash'     => true,
            'cookie_expire'   => HOUR_IN_SECONDS,
            'text'            => '',
        ];
        $attrs = wp_parse_args($attrs, $defaults);

        /**
         * @var string $id Identifiant de qualification du contrôleur d'affichage
         * @var string $container_id ID HTML du conteneur de notification
         * @var string $container_class Classe HTML du conteneur de notification
         * @var string $cookie_name Nom de qualification du cookie
         * @var bool|string $cookie_hash Activation d'ajout d'un hashage dans le nom de qualification du cookie
         * @var int $cookie_expire Nombre de seconde avant expiration du cookie
         * @var string $text Texte de notification
         */
        extract($attrs);

        // Définition du nom de qualification du cookie
        if (!$cookie_name) :
            $cookie_name = $id;
        endif;

        // Traitement des arguments
        // Action de récupération via ajax
        $ajax_action = 'tiFyControlCookieNotice';

        /// Agent de sécurisation de la requête ajax
        $ajax_nonce = wp_create_nonce('tiFyControlCookieNotice');

        // Selecteur HTML
        $output = "";
        if (!static::getCookie($cookie_name, $cookie_hash)) :
            $output .= "<div id=\"{$container_id}\" class=\"tiFyControlCookieNotice" . ($container_class ? " {$container_class}" : '') . "\" data-tify_control=\"cookie_notice\" data-options=\"" . rawurlencode(json_encode(compact('ajax_action', 'ajax_nonce', 'cookie_name', 'cookie_hash', 'cookie_expire'))) . "\">\n";
            $output .= $text ? $text : call_user_func(__CLASS__ .'::html', $attrs);
            $output .= "</div>\n";
        endif;

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }

    /**
     * Contenu de la notification
     * Lien de validation : <a href="#" data-cookie_notice="#<?php echo $container_id; ?>" data-handle="valid">Valider</a>
     * Lien de fermeture : <a href="#" data-cookie_notice="#<?php echo $container_id; ?>" data-handle="close">Fermer</a>
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return string
     */
    public static function html($attrs = [])
    {
        return
            "<a " .
                "href=\"#{$attrs['container_id']}\" " .
                "data-cookie_notice=\"#{$attrs['container_id']}\" " .
                "data-handle=\"valid\" " .
                "class=\"tiFyControlCookieNotice\" " .
                "title=\"" . __('Masquer l\'avertissement','tify') . "\"" .
            ">" .
                __('Ignorer l\'avertissement', 'tify') .
            "</a>";
    }

    /**
     * Définition d'un cookie
     *
     * @var string $cookie_name Identification de qualification du cookie
     * @var int $cookie_expire Nombre de secondes avant expiration du cookie
     *
     * @return void
     */
    private function setCookie($cookie_name, $cookie_expire)
    {
        // Activation de la sécurité du cookie
        $secure = ('https' === parse_url(home_url(), PHP_URL_SCHEME));

        $response = new Response();
        $response->headers->setCookie(
            new Cookie(
                $cookie_name,
                true,
                time() + $cookie_expire,
                COOKIEPATH,
                COOKIE_DOMAIN,
                $secure
            )
        );

        if (COOKIEPATH != SITECOOKIEPATH) :
            $response->headers->setCookie(
                new Cookie(
                    $cookie_name,
                    true,
                    time() + $cookie_expire,
                    SITECOOKIEPATH,
                    COOKIE_DOMAIN,
                    $secure
                )
            );
        endif;

        $response->send();
    }

    /**
     * Récupération d'un cookie
     *
     * @var string $cookie_name Identification de qualification du cookie
     * @var bool|string $hash Ajout d'une chaine de hashage
     *
     * @return void
     */
    final public static function getCookie($cookie_name, $cookie_hash = true)
    {
        // Traitement du hashage
        if (!$cookie_hash) :
            $cookie_hash = '';
        elseif ($cookie_hash == 'true') :
            $cookie_hash = '_'. COOKIEHASH;
        endif;

        return (self::$Request)->cookies->get($cookie_name . $cookie_hash, false);
    }

    /**
     * Vérification d'existance du cookie
     * @deprecated
     *
     * @var string $cookie_name Identification de qualification du cookie
     *
     * @return \tiFy\Core\Control\CookieNotice\CookieNotice::getCookie
     */
    final public static function has($cookie_name)
    {
        return self::getCookie($cookie_name);
    }
}