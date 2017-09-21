<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\CurtainMenu;

class CurtainMenu extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'curtain_menu';
    
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
        wp_register_style( 'tify_control-curtain_menu', self::tFyAppAssetsUrl('CurtainMenu.css', get_class()), array( ), 170704 );
        wp_register_script( 'tify_control-curtain_menu', self::tFyAppAssetsUrl('CurtainMenu.js', get_class()), array( 'jquery-ui-widget' ), 170704, true );
    }
    
    /**
     * Mise en file des scripts
     */
    final public function enqueue_scripts()
    {
        wp_enqueue_style( 'tify_control-curtain_menu' );
        wp_enqueue_script( 'tify_control-curtain_menu' );
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Affichage du contrôleur
     * @param array $attrs
     * @return string
     */
    public static function display($attrs = [], $echo = true)
    {
        self::$Instance++;
        
        $defaults = array(
            // Marqueur d'identification unique
            'id'                    => 'tiFyControlCurtainMenu--'. self::$Instance,
            // Id Html du conteneur
            'container_id'          => 'tiFyControlCurtainMenu--'. self::$Instance,
            // Classe Html du conteneur
            'container_class'       => '',
            // Theme (light | dark | false)
            'theme'                 => 'dark',
            // Entrées de menu
            'nodes'                 => [],
            // Selection active
            'selected'              => 0
        );
        $attrs = wp_parse_args($attrs, $defaults);
        extract( $attrs );
        
        if( count($nodes) === 2 ) :
            $type = $nodes[0];
            $query_args = $nodes[1];
        else :
            $type = 'custom';
            $query_args = array();
        endif;
        
        $Nodes = self::loadOverride( '\tiFy\Core\Control\CurtainMenu\Nodes' );
        switch( $type ) :
            case 'terms' :
                $nodes = $Nodes->terms($query_args,['selected' => $selected]);
            break;
            default:
            case 'custom' :
                break;
        endswitch;

        $output  = "";
        $output .= "<div id=\"{$container_id}\" class=\"tiFyControlCurtainMenu tiFyControlCurtainMenu--{$theme}". ($container_class ? ' '. $container_class : '') ."\" data-tify_control=\"curtain_menu\">\n";
        $output .= "\t<nav class=\"tiFyControlCurtainMenu-nav\">\n";
        $output .= "\t\t<div class=\"tiFyControlCurtainMenu-panel tiFyControlCurtainMenu-panel--open\">\n";
        $Walker = self::loadOverride('\tiFy\Core\Control\CurtainMenu\Walker');
        $output .= $Walker->output($nodes,['selected' => $selected]);
        $output .= "\t\t</div>\n";
        $output .= "\t</nav>\n";
        $output .= "</div>\n";
        
        if($echo) :
            echo $output;
        endif;

        return $output;
    }
}