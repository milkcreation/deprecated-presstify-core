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
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    protected function init()
    {
        // Actions ajax
        $this->tFyAppAddAction(
            'wp_ajax_tiFyControl_CookieNotice',
            'wp_ajax'
        );
        $this->tFyAppAddAction(
            'wp_ajax_nopriv_tiFyControl_CookieNotice',
            'wp_ajax'
        );

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
    protected function enqueue_scripts()
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
        check_ajax_referer('tiFyControl-CookieNotice');

        // Récupération des arguments de création du cookie
        $cookie_name = self::tFyAppGetRequestVar('cookie_name', '', 'POST');
        $cookie_hash = self::tFyAppGetRequestVar('cookie_hash', '', 'POST');
        $cookie_expire = self::tFyAppGetRequestVar('cookie_expire', '', 'POST');

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

        return self::tFyAppGetRequestVar($cookie_name . $cookie_hash, false, 'COOKIE');
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
     * Contenu de la notification
     * Lien de validation : <a href="#" data-cookie_notice="#<?php echo $container_id; ?>" data-handle="valid">Valider</a>
     * Lien de fermeture : <a href="#" data-cookie_notice="#<?php echo $container_id; ?>" data-handle="close">Fermer</a>
     * @Overridable
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return string
     */
    public static function text($attrs = [])
    {
        return
            "<a " .
            "href=\"#{$attrs['container_id']}\" " .
            "data-cookie_notice=\"#{$attrs['container_id']}\" " .
            "data-handle=\"valid\" " .
            "class=\"tiFyControl-CookieNoticeValidLink\" " .
            "title=\"" . __('Masquer l\'avertissement','tify') . "\"" .
            ">" .
            __('Ignorer l\'avertissement', 'tify') .
            "</a>";
    }

    /**
     * Affichage
     *
     * @param array $attrs {
     *      Liste des attributs de configuration
     *
     *      @var string $id Identifiant de qualification du contrôleur d'affichage
     *      @var string $container_id ID HTML du conteneur de notification
     *      @var string $container_class Classe HTML du conteneur de notification
     *      @var string $cookie_name Nom de qualification du cookie
     *      @var bool|string $cookie_hash Activation d'ajout d'un hashage dans le nom de qualification du cookie
     *      @var int $cookie_expire Nombre de seconde avant expiration du cookie
     *      @var string $text Texte de notification
     * }
     *
     * @return string
     */
    protected function display($attrs = [])
    {
        // Traitement des attributs de configuration
        $defaults = [
            'id'              => 'tiFyControl-CookieNotice-' . $this->getId(),
            'container_id'    => 'tiFyControl-CookieNotice--' . $this->getId(),
            'container_class' => '',
            'cookie_name'     => '',
            'cookie_hash'     => true,
            'cookie_expire'   => HOUR_IN_SECONDS,
            'text'            => get_called_class() .'::text',
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
        $ajax_action = 'tiFyControl_CookieNotice';

        /// Agent de sécurisation de la requête ajax
        $ajax_nonce = wp_create_nonce('tiFyControl-CookieNotice');

        // Selecteur HTML
        $output = "";
        if (!self::getCookie($cookie_name, $cookie_hash)) :
            $output .= "<div id=\"{$container_id}\" class=\"tiFyControl-CookieNotice" . ($container_class ? " {$container_class}" : '') . "\" data-tify_control=\"cookie_notice\" data-options=\"" . rawurlencode(json_encode(compact('ajax_action', 'ajax_nonce', 'cookie_name', 'cookie_hash', 'cookie_expire'))) . "\">\n";
            $output .= is_callable($text) ? call_user_func($text, $attrs) : $text;
            $output .= "</div>\n";
        endif;

        echo $output;
    }
}