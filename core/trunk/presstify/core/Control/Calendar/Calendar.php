<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\Calendar;

class Calendar extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'calendar';
    
    /**
     * Instance Courante
     */ 
    protected static $Instance = 0;
        
    /**
     * Classe de rappel de l'affichage du calendrier
     */
    protected static $DisplayCb = '\tiFy\Core\Control\Calendar\Display';
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale de Wordpress
     */
    final public function init()
    {
        wp_register_style( 'tify_control-calendar', self::tFyAppUrl( get_class() ) .'/Calendar.css', array( 'spinkit-pulse' ), 170519 );
        wp_register_script( 'tify_control-calendar', self::tFyAppUrl( get_class() ) .'/Calendar.js', array( 'jquery' ), 170519, true );
   
        // Actions ajax
        add_action( 'wp_ajax_tiFyControlCalendar', array( $this, 'wp_ajax' ) );
        add_action( 'wp_ajax_nopriv_tiFyControlCalendar', array( $this, 'wp_ajax' ) );
    }
    
    /**
     * Mise en file des scripts
     */
    final public function enqueue_scripts()
    {
        wp_enqueue_style( 'tify_control-calendar' );
        wp_enqueue_script( 'tify_control-calendar' );
    }
    
    /**
     * Récupération ajax du calendrier
     */
    final public function wp_ajax()
    {
        $this->display( array( 'id' => $_POST['id'], 'selected' => $_POST['selected'] ) );
    }   
    
    /**
     * CONTROLEURS
     */
    /**
     * Affichage du calendrier
     */
    public static function display( $args = array(), $echo = true ) 
    {   
        self::$Instance++; 
        
        $defaults = array(
            'id'                => 'tiFyCalendar--'. self::$Instance,
            'selected'          => 'today'
        );
        $args = wp_parse_args( $args, $defaults );
        
        $path = array( self::getOverrideNamespace(). "\\Core\\Control\\Calendar\\". $args['id'] );
        $className = self::getOverride( self::$DisplayCb, $path );

        $display = new $className( $args );

        $output = $display->output();
        
        if( $echo )
            echo $output;
    
        return $output; 
    }
}