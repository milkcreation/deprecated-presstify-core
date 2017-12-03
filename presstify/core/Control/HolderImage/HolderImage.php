<?php
/**
 * @name HolderImage
 * @desc Controleur d'affichage d'image de remplacement
 * @package presstiFy
 * @namespace tiFy\Core\Control\HolderImage
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\HolderImage;

/**
 * @Overrideable \App\Core\Control\HolderImage\HolderImage
 *
 * <?php
 * namespace \App\Core\Control\HolderImage
 *
 * class HolderImage extends \tiFy\Core\Control\HolderImage\HolderImage
 * {
 *
 * }
 */

class HolderImage extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     * @var string
     */
    protected $ID = 'holder_image';

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
            'tify_control-holder_image',
            self::tFyAppAssetsUrl('HolderImage.css', get_class()),
            [],
            160714
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    public static function enqueue_scripts()
    {
        \wp_enqueue_style('tify_control-holder_image');
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
            'text'             => "<span class=\"tiFyControlHolderImage-content--default\">" . __('Aucun visuel disponible', 'tify') . "</span>",
            'ratio'            => '1:1',
            'background-color' => '#E4E4E4',
            'foreground-color' => '#AAA',
            'font-size'        => '1em'
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        list($w, $h) = preg_split('/:/', $attrs['ratio'], 2);
        $sizer = ($w && $h) ? "<span class=\"tiFyControlHolderImage-sizer\" style=\"padding-top:" . (ceil((100 / $w) * $h)) . "%\" ></span>" : "";

        $output = "";
        $output .= "<div class=\"tiFyControlHolderImage\" data-tify_control=\"holder_image\" style=\"background-color:{$attrs['background-color']};color:{$attrs['foreground-color']};\">\n";
        $output .= $sizer;
        $output .= "\t<div class=\"tiFyControlHolderImage-content\" style=\"font-size:{$attrs['font-size']}\">{$attrs['text']}</div>\n";
        $output .= "</div>\n";

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}