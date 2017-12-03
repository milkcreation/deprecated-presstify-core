<?php
/**
 * @name SlickCarousel
 * @desc Controleur d'affichage de diaporama basé sur Slick
 * @see http://kenwheeler.github.io/slick/
 * @package presstiFy
 * @namespace tiFy\Core\Control\SlickCarousel
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\SlickCarousel;

/**
 * @Overrideable \App\Core\Control\SlickCarousel\SlickCarousel
 *
 * <?php
 * namespace \App\Core\Control\SlickCarousel
 *
 * class SlickCarousel extends \tiFy\Core\Control\SlickCarousel\SlickCarousel
 * {
 *
 * }
 */

class SlickCarousel extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     * @var string
     */
    protected $ID = 'slick_carousel';

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
            'tify_control-slick_carousel',
            self::tFyAppAssetsUrl('SlickCarousel.css', get_class()),
            ['slick', 'slick-theme'],
            170722
        );
        \wp_register_script(
            'tify_control-slick_carousel',
            self::tFyAppAssetsUrl('SlickCarousel.js', get_class()),
            ['slick'],
            170722,
            true
        );
    }

    /**
     * Identifiant de la classe
     * @var string
     */
    public static function enqueue_scripts()
    {
        \wp_enqueue_style('tify_control-slick_carousel');
        \wp_enqueue_script('tify_control-slick_carousel');
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
            // Marqueur d'identification unique
            'id'              => 'tiFyControl-slick_carousel-' . self::$Instance,
            // Id Html du conteneur
            'container_id'    => 'tiFyControlSlickCarousel--' . self::$Instance,
            // Classe Html du conteneur
            'container_class' => '',
            // Options
            // @see http://kenwheeler.github.io/slick/#settings
            'options'         => [],
            // Liste des slides
            'nodes'           => [],
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

        $Nodes = self::tFyAppLoadOverrideClass('\tiFy\Core\Control\SlickCarousel\Nodes');
        $nodes = $Nodes->customs($nodes);

        $output = "";
        $output = "<div id=\"{$container_id}\" class=\"tiFyControlSlickCarousel" . ($container_class ? ' ' . $container_class : '') . "\" data-tify_control=\"slick_carousel\" data-slick=\"" . htmlentities(json_encode($options)) . "\">\n";
        $Walker = self::tFyAppLoadOverrideClass('\tiFy\Core\Control\SlickCarousel\Walker');
        $output .= $Walker->output($nodes);
        $output .= "</div>\n";

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}