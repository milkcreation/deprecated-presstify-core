<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\AccordionMenu;

class AccordionMenu extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'accordion_menu';
    
    /**
     * Instance
     */
    protected static $Instance;
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation de Wordpress
     */
    final public function init()
    {
        $min = SCRIPT_DEBUG ? '' : '.min';
     
        wp_register_style( 'tify_control-accordion_menu', self::getAssetsUrl( get_class() ) .'/AccordionMenu'. $min .'.css', array( ), 170704 );
        wp_register_script( 'tify_control-accordion_menu', self::getAssetsUrl( get_class() ) .'/AccordionMenu'. $min .'.js', array( 'jquery-ui-widget' ), 170704, true );
    }
    
    /**
     * Mise en file des scripts
     */
    final public function enqueue_scripts()
    {
        wp_enqueue_style( 'tify_control-accordion_menu' );
        wp_enqueue_script( 'tify_control-accordion_menu' );
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Affichage du contrôleur
     * @param array $attrs
     * @return string
     */
    public static function display( $attrs = array(), $echo = true )
    {
        self::$Instance++;
        
        $defaults = array(
            // Marqueur d'identification unique
            'id'                    => 'tiFyControlAccordionMenu--'. self::$Instance,
            // Id Html du conteneur
            'container_id'          => 'tiFyControlAccordionMenu--'. self::$Instance,            
            // Classe Html du conteneur
            'container_class'       => '',
            // Theme (light | dark | false)
            'theme'                 => 'dark',
            // Entrées de menu
            /**
             * - terms :
             * array(
             *      'terms'
             *      // get_terms query_args
             *      array(
             *          'taxonomy' => 'category'
             *          ...
             *      )
             * );
             */ 
            'nodes'                 => array()
        );
        $attrs = wp_parse_args( $attrs, $defaults );
        extract( $attrs );

        if( count($nodes) === 2 ) :
            $type = $nodes[0];
            $query_args = $nodes[1];
        else :
            $type = 'custom';
            $query_args = array();
        endif;
        
        $Nodes = self::loadOverride( '\tiFy\Core\Control\AccordionMenu\Nodes' );
        switch( $type ) :
            case 'terms' :
                $nodes = $Nodes->terms( $query_args );
            break;
            default:
            case 'custom' :
                break;
        endswitch; 
               
        $output  = "";
        $output  = "<div id=\"{$container_id}\" class=\"tiFyControlAccordionMenu tiFyControlAccordionMenu--{$theme}". ( $container_class ? ' '. $container_class : '' ) ."\" data-tify_control=\"accordion_menu\">\n";
        $output .= "\t<nav class=\"tiFyControlAccordionMenu-nav\">\n";	
		$Walker = self::loadOverride( '\tiFy\Core\Control\AccordionMenu\Walker' );
        $output .= $Walker->walk( $nodes );
        $output .= "\t</nav>\n";
        $output .= "</div>\n";
        
        if( $echo )
            echo $output;
        
        return $output;
    }
}