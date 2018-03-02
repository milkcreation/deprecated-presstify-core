<?php
/**
 * @name Colorpicker
 * @desc Controleur d'affichage de selecteur de couleur
 * @package presstiFy
 * @namespace tiFy\Core\Control\Colorpicker
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\Colorpicker;

/**
 * @Overrideable \App\Core\Control\Colorpicker\Colorpicker
 *
 * <?php
 * namespace \App\Core\Control\Colorpicker
 *
 * class Colorpicker extends \tiFy\Core\Control\Colorpicker\Colorpicker
 * {
 *
 * }
 */

class Colorpicker extends \tiFy\Core\Control\Factory
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
            'tify_control-colorpicker',
            self::tFyAppAssetsUrl('Colorpicker.css', get_class()),
            ['spectrum'],
            141216
        );

        $deps = ['jquery', 'spectrum'];
        if (wp_script_is('spectrum-i10n', 'registered')) :
            $deps[] = 'spectrum-i10n';
        endif;
        \wp_register_script(
            'tify_control-colorpicker',
            self::tFyAppAssetsUrl('Colorpicker.js', get_class()),
            $deps,
            141216,
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
        \wp_enqueue_style('tify_control-colorpicker');
        \wp_enqueue_script('tify_control-colorpicker');
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
            'name'    => '',
            'value'   => '',
            'attrs'   => [],
            // @see https://bgrins.github.io/spectrum/#options
            'options' => [],
        ];
        $attrs = \wp_parse_args($attrs, $defaults);
        extract($attrs);

        // Traitement des options
        $options = wp_parse_args(
            $options,
            [
                'preferredFormat' => "hex"
            ]
        );

        $output = "";
        $output .= "<div class=\"tify_colorpicker\">\n";
        $output .= "<input type=\"hidden\"";
        if ($name) :
            $output .= " name=\"$name\"";
        endif;
        if ($attrs) :
            foreach ($attrs as $iattr => $vattr) :
                $output .= " $iattr=\"$vattr\"";
            endforeach;
        endif;
        if ($options) :
            $output .= " data-options=\"" . esc_attr(json_encode($options)) . "\"";
        endif;
        $output .= " value=\"$value\" />";
        $output .= "</div>";

        echo  $output;
    }
}