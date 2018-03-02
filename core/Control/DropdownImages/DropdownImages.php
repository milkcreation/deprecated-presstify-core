<?php
/**
 * @name DropdownImages
 * @desc Controleur d'affichage de selecteur d'images
 * @package presstiFy
 * @namespace tiFy\Core\Control\DropdownImages
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\DropdownImages;

use Emojione\Emojione;
use tiFy\Statics\Media;

/**
 * @Overrideable \App\Core\Control\DropdownImages\DropdownImages
 *
 * <?php
 * namespace \App\Core\Control\DropdownImages
 *
 * class DropdownImages extends \tiFy\Core\Control\DropdownImages\DropdownImages
 * {
 *
 * }
 */

class DropdownImages extends \tiFy\Core\Control\Factory
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
            'tify_control-dropdown_images',
            self::tFyAppAssetsUrl('DropdownImages.css', get_class()),
            [],
            150122
        );
        \wp_register_script(
            'tify_control-dropdown_images',
            self::tFyAppAssetsUrl('DropdownImages.js', get_class()),
            ['jquery'],
            150122,
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
        \wp_enqueue_style('tify_control-dropdown_images');
        \wp_enqueue_script('tify_control-dropdown_images');
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
            'id'                => 'tify_control_dropdown_images-' . $this->getId(),
            'class'             => 'tify_control_dropdown_images',
            'name'              => 'tify_control_dropdown_images-' . $this->getId(),

            // Liste de selection
            'picker'            => [
                'class'    => '',
                'append'   => 'body',
                // default: vers le bas | top |  clever: positionnement intelligent
                'position' => 'default',
                'width'    => 'auto'
            ],
            'choices'           => [],
            'selected'          => 0,
            'show_option_none'  => self::tFyAppAssetsUrl('none.jpg', get_class()),
            'option_none_value' => -1,
            // Nombre de colonnes d'icônes à afficher par ligne  
            'cols'              => 6
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

        // Traitement des arguments de la liste de selection
        $picker = wp_parse_args(
            $picker,
            [
                'id'       => $id . '-picker',
                'class'    => '',
                'append'   => 'body',
                'position' => 'default',
                'width'    => 'auto'
            ]
        );

        if (!$choices) :
            $client = Emojione::getClient();
            $n = 0;
            foreach ((array)$client->getRuleset()->getShortcodeReplace() as $shotcode => $filename) :
                $src = 'https:' . $client->imagePathSVG . $filename . '.svg' . $client->cacheBustParam;
                $choices[esc_url($src)] = $src;
                if (++$n > 10) {
                    break;
                }
            endforeach;
        endif;

        // Ajout du choix aucun au début de la liste des choix
        if ($show_option_none) :
            $choices = array_reverse($choices, true);
            $choices[$option_none_value] = $show_option_none;
            $choices = array_reverse($choices, true);
        endif;

        if ($show_option_none && !$selected) {
            $selected = $option_none_value;
        }

        $seletedSrc = (!$selected) ? current($choices) : (isset($choices[$selected]) ? $choices[$selected] : $option_none_value);

        $output = "";

        // Selecteur HTML
        $output .= "<div id=\"{$id}\" class=\"{$class}\" data-tify_control=\"dropdown_images\" data-picker=\"{$picker['id']}\">\n";
        $output .= "\t<span class=\"selected\">\n";
        $output .= "\t\t<b class=\"selection\">";
        $output .= "\t\t\t<input type=\"radio\" name=\"{$name}\" value=\"{$selected}\" autocomplete=\"off\" checked=\"checked\">\n";
        $output .= "\t\t\t<img class=\"selection\" src=\"" . Media::base64Src($seletedSrc) . "\" style=\"width:100%;height:auto;\" />";
        $output .= "\t\t</b>\n";
        $output .= "\t\t<i class=\"caret\"></i>\n";
        $output .= "\t</span>\n";
        $output .= "</div>\n";

        // Picker HTML
        $output .= "<div id=\"{$picker['id']}\" class=\"dropdown_images-picker" . ($picker['class'] ? ' ' . $picker['class'] : '') . "\" data-selector=\"#{$id}\">\n";
        $output .= "\t<ul>\n";
        $col = 0;
        foreach ($choices as $value => $path) :
            /// Ouverture de ligne
            if (!$col) {
                $output .= "\t\t<li>\n\t\t\t<ul>\n";
            }
            $output .= "\t\t\t\t<li";
            if ($selected == $value) {
                $output .= " class=\"checked\"";
            }

            $output .= ">\n";
            $output .= "\t\t\t\t\t<label>\n";
            $output .= "\t\t\t\t\t\t<b class=\"selection\">";
            $output .= "\t\t\t\t\t\t\t<input type=\"radio\" name=\"{$name}\" value=\"{$value}\" autocomplete=\"off\" " . checked(($selected == $value),
                    true, false) . ">\n";
            $output .= "\t\t\t\t\t\t\t<img src=\"" . Media::base64Src($path) . "\" style=\"width:100%;height:auto;\" />";
            $output .= "\t\t\t\t\t\t</b>";
            $output .= "\t\t\t\t\t</label>\n";
            $output .= "\t\t\t\t</li>\n";

            /// Fermeture de ligne
            if (++$col >= $cols) :
                $output .= "\t\t\t</ul>\n\t\t</li>\n";
                $col = 0;
            endif;
        endforeach;
        /// Fermeture de ligne si requise
        if ($col) {
            $output .= "\t\t\t</ul>\n\t\t</li>\n";
        }
        $output .= "\t</ul>\n";
        $output .= "</div>\n";

        echo $output;
    }
}