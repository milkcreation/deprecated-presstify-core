<?php
/**
 * @name DropdownColors
 * @desc Controleur d'affichage de selecteur de palette couleur
 * @package presstiFy
 * @namespace tiFy\Core\Control\DropdownColors
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\DropdownColors;

/**
 * @Overrideable \App\Core\Control\DropdownColors\DropdownColors
 *
 * <?php
 * namespace \App\Core\Control\DropdownColors
 *
 * class DropdownColors extends \tiFy\Core\Control\DropdownColors\DropdownColors
 * {
 *
 * }
 */

class DropdownColors extends \tiFy\Core\Control\Factory
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
        \wp_register_style(
            'tify_control-dropdown_colors',
            self::tFyAppAssetsUrl('DropdownColors.css', get_class()),
            [],
            150512
        );
        \wp_register_script(
            'tify_control-dropdown_colors',
            self::tFyAppAssetsUrl('DropdownColors.js', get_class()),
            ['jquery'],
            150512,
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
        \wp_enqueue_style('tify_control-dropdown_colors');
        \wp_enqueue_script('tify_control-dropdown_colors');
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
            // Conteneur
            'id'                => 'tify_control_dropdown_colors-' . $this->getId(),
            'class'             => 'tify_control_dropdown_colors',
            'name'              => 'tify_control_dropdown_colors-' . $this->getId(),
            'attrs'             => [],

            // Valeur
            'selected'          => 0,
            'choices'           => [
                '#FFF', '#000'
            ],
            'show_option_none'  => false,
            'option_none_value' => '',
            'labels'            => [],
            'disabled'          => false,

            // Liste de selection
            'picker'            => [
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

        // Traitement de la liste des choix
        if (is_string($choices)) :
            $choices = array_map('trim', explode(',', $choices));
        endif;

        // Ajout du choix "aucun" en tête de la liste des choix
        if ($show_option_none) :
            $choices = array_reverse($choices, true);
            $choices[] = $option_none_value;
            $choices = array_reverse($choices, true);
        endif;

        // Traitement de la valeur sélectionnée
        if ($show_option_none && !$selected) :
            $selected = $option_none_value;
        elseif (!$selected) :
            $selected = current($choices);
        endif;

        $selected_label = '';

        // Selecteur de traitement
        $output = "";
        $output .= "\t<select id=\"{$id}-handler\" name=\"{$name}\" data-tify_control=\"dropdown_colors-handler\" data-selector=\"#{$id}\" data-picker=\"#{$picker['id']}\"" . ($disabled ? " disabled=\"disabled\"" : "") . ">";
        foreach ((array)$choices as $value) :
            $output .= "<option value=\"{$value}\" " . selected($value == $selected, true,
                    false) . ">" . wp_strip_all_tags($value, true) . "</option>";
        endforeach;
        $output .= "\t</select>\n";

        // Selecteur HTML
        $output .= "<div id=\"{$id}\" class=\"{$class}\" data-tify_control=\"dropdown_colors\" data-handler=\"#{$id}-handler\" data-picker=\"" . htmlentities(json_encode($picker),
                ENT_QUOTES, 'UTF-8') . "\"";
        foreach ((array)$attrs as $k => $v) :
            $output .= " {$k}=\"{$v}\"";
        endforeach;
        $output .= ">\n";
        $output .= "\t<span class=\"selected\">\n";
        $output .= self::displayValue($selected, $selected_label);
        $output .= "\t</span>\n";
        $output .= "</div>\n";

        // Liste de selection HTML
        $output .= "<div id=\"{$picker['id']}\" data-tify_control=\"dropdown_colors-picker\" class=\"tify_control_dropdown_colors-picker" . ($picker['class'] ? ' ' . $picker['class'] : '') . "\" data-selector=\"#{$id}\" data-handler=\"#{$id}-handler\">\n";
        $output .= "\t<ul>\n";
        foreach ($choices as $value) :
            $output .= "\t\t<li" . ($selected == $value ? " class=\"checked\"" : "") . ">\n";
            $label = isset($labels[$value]) ? $labels[$value] : '';
            $output .= self::displayValue($value, $label);
            $output .= "\t\t</li>\n";
        endforeach;
        $output .= "\t</ul>\n";
        $output .= "</div>\n";

        echo $output;
    }

    /** == Affichage de la valeur == **/
    protected function displayValue($value = null, $label = '')
    {
        $output = "<span class=\"color-square" . ($value ? "" : " none") . "\" style=\"" . ($value ? "background-color:{$value}" : "") . "\"></span>\n";
        if ($label) :
            $output .= "<label>{$label}</label>";
        endif;

        return $output;
    }
}