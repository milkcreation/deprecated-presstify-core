<?php
/**
 * @name Tabs
 * @desc Controleur d'affichage de boite à onglet
 * @package presstiFy
 * @namespace tiFy\Core\Control\Tabs
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\Tabs;

/**
 * @Overrideable \App\Core\Control\Tabs\Tabs
 *
 * <?php
 * namespace \App\Core\Control\Tabs
 *
 * class Tabs extends \tiFy\Core\Control\Tabs\Tabs
 * {
 *
 * }
 */

class Tabs extends \tiFy\Core\Control\Factory
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();


    }

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
        // Déclaration des Actions Ajax
        $this->tFyAppAddAction(
            'wp_ajax_tiFyControlTabs',
            'wp_ajax'
        );
        $this->tFyAppAddAction(
            'wp_ajax_nopriv_tiFyControlTabs',
            'wp_ajax'
        );

        // Déclaration des scripts
        \wp_register_style(
            'tify_control-tabs',
            self::tFyAppAssetsUrl('Tabs.css', get_class()),
            [],
            170704
        );
        \wp_register_script(
            'tify_control-tabs',
            self::tFyAppAssetsUrl('Tabs.js', get_class()),
            ['jquery-ui-widget'],
            170704,
            true
        );
        \wp_localize_script(
            'tify_control-tabs',
            'tiFyControlTabs',
            [
                '_ajax_nonce' => wp_create_nonce('tiFyControlTabs')
            ]
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    protected function enqueue_scripts()
    {
        \wp_enqueue_style('tify_control-tabs');
        \wp_enqueue_script('tify_control-tabs');
    }

    /**
     * Mise à jour de l'onglet courant via Ajax
     *
     * @return \wp_send_json
     */
    public static function wp_ajax()
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
     * Affichage
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return string
     */
    protected function display($attrs = [])
    {
        // Traitement des attributs de configuration
        $defaults = [
            // Marqueur d'identification unique
            'id'              => 'tiFyControlTabs--' . $this->getId(),
            // Id Html du conteneur
            'container_id'    => 'tiFyControlTabs--' . $this->getId(),
            // Classe Html du conteneur
            'container_class' => '',
            // Entrées de menu
            'nodes'           => []
        ];
        $attrs = wp_parse_args($attrs, $defaults);

        /**
         * @var string $id Identifiant de qualification du controleur
         * @var string $container_id ID HTML du conteneur
         * @var string $container_class Classes HTML du conteneur
         * @var array[] $nodes Liste des greffons et leurs attributs
         */
        extract($attrs);

        /**
         * @var \tiFy\Core\Control\Tabs\Nodes $Nodes
         */
        $Nodes = self::tFyAppLoadOverride('tiFy\Core\Control\Tabs\Nodes');
        $nodes = $Nodes->customs($nodes);

        $output = "";
        $output = "<div id=\"{$container_id}\" class=\"tiFyControlTabs" . ($container_class ? ' ' . $container_class : '') . "\" data-tify_control=\"tabs\">\n";

        /**
         * @var \tiFy\Core\Control\Tabs\Walker $Walker
         */
        $Walker = self::tFyAppLoadOverride('tiFy\Core\Control\Tabs\Walker');
        $output .= $Walker::output($nodes);
        $output .= "</div>\n";

        echo $output;
    }
}