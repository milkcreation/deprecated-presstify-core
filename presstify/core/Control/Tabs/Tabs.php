<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\Tabs;

class Tabs extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'tabs';
    
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
        wp_register_style( 'tify_control-tabs', self::tFyAppAssetsUrl('Tabs.css', get_class()), array( ), 170704 );
        wp_register_script( 'tify_control-tabs', self::tFyAppAssetsUrl('Tabs.js', get_class()), array( 'jquery-ui-widget' ), 170704, true );
    }
    
    /**
     * Mise en file des scripts
     */
    final public function enqueue_scripts()
    {
        wp_enqueue_style( 'tify_control-tabs' );
        wp_enqueue_script( 'tify_control-tabs' );
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
            'id'                    => 'tiFyControlTabs--'. self::$Instance,
            // Id Html du conteneur
            'container_id'          => 'tiFyControlTabs--'. self::$Instance,            
            // Classe Html du conteneur
            'container_class'       => '',
            // Entrées de menu
            'nodes'                 => array()
        );
        $attrs = wp_parse_args( $attrs, $defaults );
        extract( $attrs );
     
        $Nodes = self::loadOverride( '\tiFy\Core\Control\Tabs\Nodes' );
        $nodes = $Nodes->customs( $nodes );
        
        $output  = "";
        $output  = "<div id=\"{$container_id}\" class=\"tiFyControlTabs". ( $container_class ? ' '. $container_class : '' ) ."\" data-tify_control=\"tabs\">\n";	
		$Walker = self::loadOverride( '\tiFy\Core\Control\Tabs\Walker' );
        $output .= $Walker->output($nodes);
        $output .= "</div>\n";
        
        if( $echo )
            echo $output;
        
        return $output;
    }
}