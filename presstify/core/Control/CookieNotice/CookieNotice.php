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
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

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

        $cookie_name = $_POST['cookie_name'];
        $cookie_expire = $_POST['cookie_expire'];
        $secure = ('https' === parse_url(home_url(), PHP_URL_SCHEME));

        setcookie(
            $cookie_name . COOKIEHASH,
            true,
            time() + $cookie_expire,
            COOKIEPATH,
            COOKIE_DOMAIN,
            $secure,
            true
        );
        if (COOKIEPATH != SITECOOKIEPATH) :
            setcookie(
                $cookie_name . COOKIEHASH,
                true,
                time() + $cookie_expire,
                SITECOOKIEPATH,
                COOKIE_DOMAIN,
                $secure,
                true
            );
        endif;
        wp_die(1);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $attrs Liste des attributs de configuration
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    protected static function display($attrs = [], $echo = true)
    {
        // Traitement des attributs de configuration
        $defaults = [
            // Identifier
            'id'              => 'tiFyControlCookieNotice-' . self::$Instance,
            // ID HTML du conteneur
            'container_id'    => 'tiFyControlCookieNotice--' . self::$Instance,
            // Classe HTML du conteneur
            'container_class' => '',
            // 
            'cookie_name'     => '',
            // Expiration du cookie - exprimée en sec. 1 heure (3600sec) par défaut
            'cookie_expire'   => HOUR_IN_SECONDS,
            // Contenu de la notification
            'html'            => '',
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

        // Traitement des arguments
        /// Action de récupération via ajax
        $ajax_action = 'tiFyControlCookieNotice';

        /// Agent de sécurisation de la requête ajax
        $ajax_nonce = wp_create_nonce('tiFyControlCookieNotice');

        // Nom du cookie
        if (!$cookie_name) :
            $cookie_name = $id . '_';
        endif;

        // Liste des arguments pour le traitement de la requête Ajax
        $ajax_attrs = compact('ajax_action', 'ajax_nonce', 'cookie_name', 'cookie_expire');

        // Selecteur HTML
        $output = "";
        $output .= "<div id=\"{$container_id}\" class=\"tiFyControlCookieNotice" . ($container_class ? ' ' . $container_class : '') . "\" data-tify_control=\"cookie_notice\" data-attrs=\"" . htmlentities(json_encode($ajax_attrs)) . "\">\n";
        if (!static::has($cookie_name)) :
            $output .= $html ? $html : static::html($attrs);
        endif;
        $output .= "</div>\n";

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }

    /**
     * Contenu de la notification
     * Pour fonctionner un lien contenant l'attribut data-toggle est requis
     */
    public static function html($attrs = [])
    {
        return "<a class=\"tiFyControlCookieNotice\" href=\"#{$attrs['container_id']}\" data-toggle=\"fade\" title=\"" . __('Masquer l\'avertissement',
                'tify') . "\">" . __('Ignorer l\'avertissement', 'tify') . "</a>";
    }

    /**
     * Vérification d'existance du cookie
     */
    public static function has($cookie_name)
    {
        return !empty($_COOKIE[$cookie_name . COOKIEHASH]);
    }
}