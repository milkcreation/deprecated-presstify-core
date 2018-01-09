<?php
/**
 * @name Checkbox
 * @desc Controleur d'affichage de case à cocher
 * @package presstiFy
 * @namespace tiFy\Core\Control\Checkbox
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Julien Picard dit pitcho <julien@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\Checkbox;

/**
 * @Overrideable \App\Core\Control\Checkbox\Checkbox
 *
 * <?php
 * namespace \App\Core\Control\Checkbox
 *
 * class Checkbox extends \tiFy\Core\Control\Checkbox\Checkbox
 * {
 *
 * }
 */

class Checkbox extends \tiFy\Core\Control\Factory
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
        wp_register_style(
            'tify_control-checkbox',
            self::tFyAppAssetsUrl('Checkbox.css', get_class()),
            ['dashicons'],
            150420
        );
        wp_register_script(
            'tify_control-checkbox',
            self::tFyAppAssetsUrl('Checkbox.js', get_class()),
            ['jquery'],
            150420,
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
        \wp_enqueue_style('tify_control-checkbox');
        \wp_enqueue_script('tify_control-checkbox');
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
            'id'             => 'tify_control_checkbox-' . $this->getId(),
            'class'          => 'tify_control_checkbox',
            'name'           => 'tify_control_checkbox-' . $this->getId(),
            'value'          => 0,
            'label'          => __('Aucun', 'tify'),
            'label_class'    => 'tify_control_checkbox-label',
            'label_position' => 'R',
            'checked'        => 0
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

        $output = "";
        $output .= "<noscript>\n";
        $output .= "\t<style type=\"text/css\">";
        $output .= "\t\t.tify_checkbox{ display:none; }\n";
        $output .= "\t</style>";
        $output .= "\t<div class=\"checkbox\">\n";
        $output .= "\t\t<input type=\"checkbox\" value=\"{$value}\" name=\"{$name}\">";
        $output .= "\t\t<label>{$label}</label>";
        $output .= "\t</div>\n";
        $output .= "</noscript>\n";

        $class .= ((bool)$checked === true) ? ' checked' : '';

        $output .= "<div id=\"{$id}\" class=\"{$class}\" data-tify_control=\"checkbox\" data-label_position=\"" . ($label_position === 'R' ? 'right' : 'left') . "\">\n";
        $output .= "\t<label class=\"{$label_class}\">";
        if ($label_position != 'R') {
            $output .= $label;
        }

        $output .= "<input type=\"checkbox\" value=\"{$value}\" name=\"{$name}[]\" autocomplete=\"off\" " . checked((bool)$checked,
                true, false) . ">";
        if ($label_position == 'R') :
            $output .= "$label";
        endif;
        $output .= "\t</label>";
        $output .= "</div>\n";

        echo $output;
    }
}