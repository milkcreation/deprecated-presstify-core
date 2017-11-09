<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\Tabs;

class Tabs extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'tabs';

    /**
     * Instance
     */
    protected static $Instance;

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation de Wordpress
     *
     * @return void
     */
    final public function init()
    {
        wp_register_style('tify_control-tabs', self::tFyAppAssetsUrl('Tabs.css', get_class()), [], 170704);
        wp_register_script('tify_control-tabs', self::tFyAppAssetsUrl('Tabs.js', get_class()), ['jquery-ui-widget'], 170704, true);
        wp_localize_script(
            'tify_control-tabs',
            'tiFyControlTabs',
            [
                '_ajax_nonce'   => wp_create_nonce('tiFyControlTabs')
            ]
        );

        // Actions ajax
        self::tFyAppActionAdd('wp_ajax_tiFyControlTabs', 'wp_ajax');
        self::tFyAppActionAdd('wp_ajax_nopriv_tiFyControlTabs', 'wp_ajax');
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    final public function enqueue_scripts()
    {
        wp_enqueue_style('tify_control-tabs');
        wp_enqueue_script('tify_control-tabs');
    }

    /**
     * Action Ajax
     *
     * @return string
     */
    final public function wp_ajax()
    {
        check_ajax_referer('tiFyControlTabs');

        // Bypass
        if (empty($_POST['key'])) :
            wp_die(0);
        endif;

        $key = $_POST['key'];
        $raw_key = base64_decode($key);

        if (!$raw_key = maybe_unserialize($raw_key)) :
            wp_die(0);
        else :
            $raw_key = maybe_unserialize($raw_key);
        endif;

        $success = update_user_meta(get_current_user_id(), 'tiFyControlTabs' . $raw_key['_screen_id'], $raw_key['id']);

        \wp_send_json(['success' => $success]);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage du contrôleur
     *
     * @param array $attrs
     *
     * @return string
     */
    public static function display($attrs = [], $echo = true)
    {
        self::$Instance++;

        /**
         * @var string $id Identifiant de qualification du controleur
         * @var string $container_id ID HTML du conteneur
         * @var string $container_class Classes HTML du conteneur
         * @var array $nodes {
         *      Liste des greffons
         *
         *
         * }
         */
        $defaults = [
            // Marqueur d'identification unique
            'id'              => 'tiFyControlTabs--' . self::$Instance,
            // Id Html du conteneur
            'container_id'    => 'tiFyControlTabs--' . self::$Instance,
            // Classe Html du conteneur
            'container_class' => '',
            // Entrées de menu
            'nodes'           => []
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

        /**
         * @var \tiFy\Core\Control\Tabs\Nodes $Nodes
         */
        $Nodes = self::loadOverride('\tiFy\Core\Control\Tabs\Nodes');
        $nodes = $Nodes->customs($nodes);

        $output = "";
        $output = "<div id=\"{$container_id}\" class=\"tiFyControlTabs" . ($container_class ? ' ' . $container_class : '') . "\" data-tify_control=\"tabs\">\n";

        /**
         * @var \tiFy\Core\Control\Tabs\Walker $Walker
         */
        $Walker = self::loadOverride('\tiFy\Core\Control\Tabs\Walker');
        $output .= $Walker::output($nodes);
        $output .= "</div>\n";

        if ($echo) {
            echo $output;
        }

        return $output;
    }
}