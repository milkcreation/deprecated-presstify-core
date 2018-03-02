<?php
/**
 * @name Dropdown
 * @desc Controleur d'affichage de champ de selection
 * @package presstiFy
 * @namespace tiFy\Core\Control\Dropdown
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\Dropdown;

/**
 * @Overrideable \App\Core\Control\Dropdown\Dropdown
 *
 * <?php
 * namespace \App\Core\Control\Dropdown
 *
 * class Dropdown extends \tiFy\Core\Control\Dropdown\Dropdown
 * {
 *
 * }
 */

class Dropdown extends \tiFy\Core\Control\Factory
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
            'tify_control-dropdown',
            self::tFyAppUrl(get_class()) . '/Dropdown.min.css',
            [],
            141212
        );
        \wp_register_script(
            'tify_control-dropdown',
            self::tFyAppUrl(get_class()) . '/Dropdown.min.js',
            ['jquery'],
            141212,
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
        \wp_enqueue_style('tify_control-dropdown');
        \wp_enqueue_script('tify_control-dropdown');
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
    protected function display($attrs = [])
    {
        // Traitement des attributs de configuration
        $defaults = [
            // Conteneur
            'id'                => 'tify_control_dropdown-' . $this->getId(),
            'class'             => 'tify_control_dropdown',
            'name'              => 'tify_control_dropdown-' . $this->getId(),
            'attrs'             => [],

            // Valeur            
            'selected'          => 0,
            'choices'           => [],
            'show_option_none'  => __('Aucun', 'tify'),
            'option_none_value' => -1,
            'disabled'          => false,

            // Options
            'type'              => 'single',    // @TODO single | multi            

            // Controleur HTML (masqué)
            'handler'           => [
                'class' => ''
            ],

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
        $handler = wp_parse_args(
            $handler,
            [
                'id'    => $id . '-handler',
                'class' => ''
            ]
        );

        // Traitement des arguments de la liste de selection
        $picker = wp_parse_args(
            $picker,
            [
                'id'       => $id . '-picker',
                'class'    => '',
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
        if ($show_option_none && !isset($choices[$option_none_value])) :
            $choices = array_reverse($choices, true);
            $choices[$option_none_value] = $show_option_none;
            $choices = array_reverse($choices, true);
        endif;

        // Traitement de la valeur sélectionnée
        if ($show_option_none && !$selected) :
            $selected = $option_none_value;
        elseif (!$selected) :
            $selected = current($choices);
        endif;

        // Selecteur de traitement
        $output = "";
        $output .= "\t<select id=\"{$handler['id']}\" name=\"{$name}\" class=\"tify_control_dropdown-handler" . ($handler['class'] ? ' ' . $handler['class'] : '') . "\" data-tify_control=\"dropdown-handler\" data-selector=\"#{$id}\" data-picker=\"#{$picker['id']}\"" . ($disabled ? " disabled=\"disabled\"" : "") . ">";
        foreach ((array)$choices as $value => $label) :
            $output .= "<option value=\"{$value}\" " . selected(((!empty($selected) && !empty($value) && ($selected == $value)) || ($selected === $value)),
                    true, false) . ">" . wp_strip_all_tags($label, true) . "</option>";
        endforeach;
        $output .= "\t</select>\n";

        // Selecteur HTML
        if ($disabled) :
            $class .= " disabled";
        endif;

        $output .= "<div id=\"{$id}\" class=\"tify_control_dropdown {$class}\" data-tify_control=\"dropdown\" data-handler=\"#{$id}-handler\" data-picker=\"" . htmlentities(json_encode($picker),
                ENT_QUOTES, 'UTF-8') . "\"";
        foreach ((array)$attrs as $k => $v) :
            $output .= " {$k}=\"{$v}\"";
        endforeach;
        $output .= ">\n";
        $output .= "\t<span class=\"selected\">\n";
        $output .= isset($choices[$selected]) ? $choices[$selected] : ($show_option_none ? $show_option_none : current($choices));
        $output .= "\t</span>\n";
        $output .= "</div>\n";

        // Liste de selection HTML
        $output .= "<div id=\"{$picker['id']}\" data-tify_control=\"dropdown-picker\" class=\"tify_control_dropdown-picker" . ($picker['class'] ? ' ' . $picker['class'] : '') . "\" data-selector=\"#{$id}\" data-handler=\"#{$id}-handler\">\n";
        $output .= "\t<ul>\n";
        foreach ((array)$choices as $value => $label) :
            $output .= "\t\t<li" . ($selected == $value ? " class=\"checked\"" : "") . ">\n";
            $output .= $label;
            $output .= "\t\t</li>\n";
        endforeach;
        $output .= "\t</ul>\n";
        $output .= "</div>\n";

        echo $output;
    }
}