<?php
namespace tiFy\Core\Control\Progress;

class Progress extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'progress';
    
    /**
     * Instance
     * @var int
     */
    private static $Instance;
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale de wordpress
     */
    final public function init()
    {
        wp_register_style( 'tify_control-progress', self::tFyAppUrl( get_class() ) .'/Progress.css', array(), '160605' );
        wp_register_script( 'tify_control-progress', self::tFyAppUrl( get_class() ) .'/Progress.js', array( 'jquery-ui-widget' ), '160605', true );
    }
    
    /**
     * Mise en file des scripts
     */
    final public function enqueue_scripts()
    {
        wp_enqueue_style( 'tify_control-progress' );
        wp_enqueue_script( 'tify_control-progress' );
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Mise en file des scripts
     * @param array $args
     */
    public static function display( $args = array(), $echo = true )
    {
        self::$Instance++;

        $defaults = array(
            'id'            => 'tiFyControlProgress--'. self::$Instance,
            'class'         => '',
            'title'         => '',
            'value'         => 0,
            'max'           => 100
        );    
        $args = wp_parse_args( $args, $defaults );

        $footer = function() use ( $args ){
            extract( $args );
            
            $percent = ceil( ( $value / $max ) * 100 );
            
            $output  = "";
            $output .= "<div id=\"{$id}\" class=\"tiFyControlProgress". ( $class ? ' '. $class : '' ) ."\" data-tify_control=\"progress\">\n";
            $output .= "\t<div class=\"tiFyControlProgress-content\">";
            $output .= "\t\t<div class=\"tiFyControlProgress-contentHeader\">\n";
            $output .= "\t\t\t<h3 class=\"tiFyControlProgress-headerTitle\" data-role=\"header-title\">{$title}</h3>\n";
            $output .= "\t\t</div>\n";
            $output .= "\t\t<div class=\"tiFyControlProgress-contentBody\">\n";
            $output .= "\t\t\t<div class=\"tiFyControlProgress-bar\" style=\"background-position:-{$percent}% 0;\" data-role=\"bar\" data-max=\"". intval( $max ) ."\">\n";
            $output .= "\t\t\t\t<div class=\"tiFyControlProgress-indicator\" data-role=\"indicator\"></div>\n";
            $output .= "\t\t\t</div>\n";
            $output .= "\t\t\t<div class=\"tiFyControlProgress-infos\" data-role=\"info\"></div>\n";
            $output .= "\t\t</div>\n";
            $output .= "\t\t<div class=\"tiFyControlProgress-contentFooter\">\n";
            $output .= "\t\t\t<button class=\"tiFyButton--primary tiFyControlProgress-close\" data-role=\"close\">". __( 'Annuler', 'tify' ) ."</button>\n";
            $output .= "\t\t</div>\n";
            $output .= "\t</div>\n";
            $output .= "\t<div id=\"{$id}-backdrop\" class=\"tiFyControlProgress-backdrop\"></div>\n";
            $output .= "</div>\n";
            
            echo $output;
        };

        add_action( 'wp_footer', $footer ); 
        add_action( 'admin_footer', $footer ); 
    }
}