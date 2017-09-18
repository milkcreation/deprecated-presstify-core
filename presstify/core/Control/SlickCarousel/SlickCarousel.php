<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\SlickCarousel;

class SlickCarousel extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'slick_carousel';
    
    /**
     * Instance courante
     */
    protected static $Instance;

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    final public function init()
    {
        wp_register_style('tify_control-slick_carousel', self::tFyAppAssetsUrl('SlickCarousel.css', get_class()), array('slick', 'slick-theme'), 170722 );
        wp_register_script('tify_control-slick_carousel', self::tFyAppAssetsUrl('SlickCarousel.js', get_class()), array('slick'), 170722, true);
    }
    
    /**
     * Mise en file des scripts
     */
    final public function enqueue_scripts()
    {
        wp_enqueue_style('tify_control-slick_carousel');
        wp_enqueue_script('tify_control-slick_carousel');
    }
       
    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     */
    final public static function display( $args = array(), $echo = true )
    {
        self::$Instance++;
        
        $defaults = array(
            // Marqueur d'identification unique
            'id'                => 'tiFyControl-slick_carousel-'. self::$Instance,
            // Id Html du conteneur
            'container_id'      => 'tiFyControlSlickCarousel--'. self::$Instance,
            // Classe Html du conteneur
            'container_class'   => '',
            // Options
            // @see http://kenwheeler.github.io/slick/#settings
            'options'   => array(),
            // Liste des slides
            'nodes'     => array(),
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args );
        

        $Nodes = self::loadOverride( '\tiFy\Core\Control\SlickCarousel\Nodes' );
        $nodes = $Nodes->customs($nodes);

        $output  = "";
        $output  = "<div id=\"{$container_id}\" class=\"tiFyControlSlickCarousel". ( $container_class ? ' '. $container_class : '' ) ."\" data-tify_control=\"slick_carousel\" data-slick=\"". htmlentities( json_encode( $options ) ) ."\">\n";
        $Walker = self::loadOverride( '\tiFy\Core\Control\SlickCarousel\Walker' );
        $output .= $Walker->output($nodes);
        $output .= "</div>\n";
        
        if( $echo )
            echo $output;

        return $output;
    }     
}