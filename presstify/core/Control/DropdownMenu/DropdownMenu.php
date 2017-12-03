<?php
/**
 * @name DropdownMenu
 * @desc Controleur d'affichage de menu dropdown
 * @package presstiFy
 * @namespace tiFy\Core\Control\DropdownMenu
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\DropdownMenu;

/**
 * @Overrideable \App\Core\Control\DropdownMenu\DropdownMenu
 *
 * <?php
 * namespace \App\Core\Control\DropdownMenu
 *
 * class DropdownMenu extends \tiFy\Core\Control\DropdownMenu\DropdownMenu
 * {
 *
 * }
 */

class DropdownMenu extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     * @var string
     */
    protected $ID = 'dropdown_menu';

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
            'tify_control-dropdown_menu',
            self::tFyAppAssetsUrl('DropdownMenu.css', get_class()),
            [],
            160913
        );
        \wp_register_script(
            'tify_control-dropdown_menu',
            self::tFyAppAssetsUrl('DropdownMenu.js', get_class()),
            ['jquery'],
            160913,
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
        \wp_enqueue_style('tify_control-dropdown_menu');
        \wp_enqueue_script('tify_control-dropdown_menu');
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
    public static function display($attrs = [], $echo = true)
    {
        // Incrémentation du nombre d'instance
        self::$Instance++;

        // Traitement des attributs de configuration
        $defaults = [
            'id'               => 'tify_control_dropdown_menu-' . self::$Instance,
            'class'            => 'tify_control_dropdown_menu',
            'selected'         => 0,
            'links'            => [],
            'show_option_none' => __('Aucun', 'tify'),

            // Liste de selection
            'picker'           => [
                'class'    => '',
                'append'   => 'body',
                'position' => 'default', // default: vers le bas | top |  clever: positionnement intelligent
                'width'    => 'auto'
            ]
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

        // Traitement des arguments de la liste de selection
        $picker = wp_parse_args(
            $picker,
            [
                'id'       => $id . '-picker',
                'append'   => 'body',
                'position' => 'default', // default: vers le bas | top | clever: positionnement intelligent
                'width'    => 'auto'
            ]
        );

        $output = "";
        $output .= "<div id=\"{$id}\" class=\"tify_control_dropdown_menu {$class}\" data-tify_control=\"dropdown_menu\" data-picker=\"" . htmlentities(json_encode($picker),
                ENT_QUOTES, 'UTF-8') . "\">\n";
        $output .= "\t<span class=\"selected\">";
        $output .= isset($links[$selected]) ? strip_tags($links[$selected]) : $show_option_none;
        $output .= "</span>\n";
        $output .= "</div>\n";

        // Picker HTML
        $output .= "<div id=\"{$picker['id']}\" data-tify_control=\"dropdown_menu-picker\" class=\"tify_control_dropdown_menu-picker" . ($picker['class'] ? ' ' . $picker['class'] : '') . "\" data-selector=\"#{$id}\" data-handler=\"#{$id}-handler\">\n";
        $output .= "\t<ul>\n";
        foreach ((array)$links as $value => $link) : if ($value === $selected) {
            continue;
        }
            $output .= "\t\t<li>{$link}</li>\n";
        endforeach;
        $output .= "\t</ul>\n";
        $output .= "</div>\n";

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}