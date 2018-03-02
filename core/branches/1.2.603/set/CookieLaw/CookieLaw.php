<?php
/**
 * @name CookieLaw
 * @desc Affichage d'une notification d'utilisation de cookie sur le site
 * @package presstiFy
 * @namespace tiFy\Core\Control\Checkbox
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Set\CookieLaw;

use tiFy\Core\Control\Control;
use tiFy\Core\Router\Router;

class CookieLaw extends \tiFy\App\Set
{
    /**
     * Classe de rappel du Router
     * @var \tiFy\Core\Router\Factory
     */
    private static $Router = null;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Définition des événements de déclenchement
        $this->tFyAppAddAction('init');
        $this->tFyAppAddAction('tify_router_register');
        $this->tFyAppAddAction('wp_enqueue_scripts');
        $this->tFyAppAddAction('wp_footer');
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public function init()
    {
        // Déclaration des scripts
        \wp_register_style(
            'tiFySet_CookieLaw',
            self::tFyAppAssetsUrl('CookieLaw.css', get_class()),
            ['dashicons'],
            141118
        );
    }

    /**
     * Déclaration des attributs de routage de la page d'affichage des règles de cookie
     *
     * @return void
     */
    public function tify_router_register()
    {
        if (!$attrs = self::tFyAppConfig('router')) :
            return;
        endif;

        $defaults = [
            'title' => __('Page d\'affichage des règles de cookie', 'tify')
        ];
        if (!is_array($attrs)) :
            $attrs = [];
        endif;

        $attrs = \wp_parse_args($attrs, $defaults);

        self::$Router = Router::register('_tiFySet_CookieLaw', $attrs);
    }

    /**
     * Initialisation des scripts de l'interface utilisateur
     *
     * @return void
     */
    public function wp_enqueue_scripts()
    {
        if(self::tFyAppConfig('enqueue_scripts', true)) :
            self::enqueue_scripts();
        endif;
    }

    /**
     * Pied de page de l'interface utilisateur du site
     *
     * @return string
     */
    public function wp_footer()
    {
        if(self::tFyAppConfig('display', true)) :
            self::display();
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    final public function display($echo = true)
    {
        // Définition des arguments du gabarit
        $container_id = 'tiFySet-CookieLaw';
        $text = self::tFyAppConfig('text');
        if($rules_page_id = self::getRulesPageId()) :
            $rules_url = \get_permalink($rules_page_id);
        else :
            $rules_url = '';
        endif;
        $valid_text = self::tFyAppConfig('valid_text');
        $rules_text = self::tFyAppConfig('rules_text');
        $close_text = self::tFyAppConfig('close_text');

        // Récupération du gabarit
        ob_start();
        self::tFyAppGetTemplatePart(
            'notice',
            null,
            compact(
                'container_id',
                'text',
                'rules_url',
                'valid_text',
                'rules_text',
                'close_text'
            )
        );
        $text = ob_get_clean();

        // Affichage de la notification
        Control::CookieNotice(
            [
                'id'              => 'tiFySet-CookieLaw',
                'container_id'    => $container_id,
                'cookie_name'     => 'tiFySet_CookieLaw',
                'cookie_hash'     => true,
                'cookie_expire'   => YEAR_IN_SECONDS,
                'text'            => $text,
            ],
            $echo
        );
    }

    /**
     * Mise en file des scripts
     */
    final public static function enqueue_scripts()
    {
        \wp_enqueue_style('tiFySet_CookieLaw');
        Control::enqueue_scripts('cookie_notice');
    }

    /**
     * Récupération de l'identifiant de qualification de la page d'affichage des règles de cookie
     *
     * @return int
     */
    final public static function getRulesPageId()
    {
        if (!$router = self::$Router) :
            return 0;
        endif;

        return $router->getSelected();
    }
}
