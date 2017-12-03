<?php
/**
 * @name CurtainMenu
 * @desc Controleur d'affichage de menu rideau
 * @package presstiFy
 * @namespace tiFy\Core\Control\CurtainMenu
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\CurtainMenu;

/**
 * @Overrideable \App\Core\Control\Calendar\Calendar
 *
 * <?php
 * namespace \App\Core\Control\Calendar
 *
 * class Calendar extends \tiFy\Core\Control\Calendar\Calendar
 * {
 *
 * }
 */

class CurtainMenu extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'curtain_menu';

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
        \wp_register_style(
            'tify_control-curtain_menu',
            self::tFyAppAssetsUrl('CurtainMenu.css', get_class()),
            [],
            170704
        );
        \wp_register_script(
            'tify_control-curtain_menu',
            self::tFyAppAssetsUrl('CurtainMenu.js', get_class()),
            ['jquery-ui-widget'],
            170704,
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
        \wp_enqueue_style('tify_control-curtain_menu');
        \wp_enqueue_script('tify_control-curtain_menu');
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
            // Marqueur d'identification unique
            'id'              => 'tiFyControlCurtainMenu--' . self::$Instance,
            // Id Html du conteneur
            'container_id'    => 'tiFyControlCurtainMenu--' . self::$Instance,
            // Classe Html du conteneur
            'container_class' => '',
            // Theme (light | dark | false)
            'theme'           => 'dark',
            // Entrées de menu
            'nodes'           => [],
            // Selection active
            'selected'        => 0
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

        if (count($nodes) === 2) :
            $type = $nodes[0];
            $query_args = $nodes[1];
        else :
            $type = 'custom';
            $query_args = [];
        endif;

        $Nodes = self::tFyAppLoadOverrideClass('\tiFy\Core\Control\CurtainMenu\Nodes');

        switch ($type) :
            case 'terms' :
                $nodes = $Nodes->terms($query_args, ['selected' => $selected]);
                break;
            default:
            case 'custom' :
                $nodes = $Nodes->customs($nodes, ['selected' => $selected]);
                break;
        endswitch;

        $output = "";
        $output .= "<div id=\"{$container_id}\" class=\"tiFyControlCurtainMenu tiFyControlCurtainMenu--{$theme}" . ($container_class ? ' ' . $container_class : '') . "\" data-tify_control=\"curtain_menu\">\n";
        $output .= "\t<nav class=\"tiFyControlCurtainMenu-nav\">\n";
        $output .= "\t\t<div class=\"tiFyControlCurtainMenu-panel tiFyControlCurtainMenu-panel--open\">\n";
        $Walker = self::tFyAppLoadOverrideClass('\tiFy\Core\Control\CurtainMenu\Walker');
        $output .= $Walker->output($nodes, ['selected' => $selected]);
        $output .= "\t\t</div>\n";
        $output .= "\t</nav>\n";
        $output .= "</div>\n";

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}