<?php
namespace tiFy\Components\NavMenu;

class NavMenu extends \tiFy\Environment\Component
{
    /**
     * Classe de rappel des menus déclarés
     */
    protected static $Factory   = array();
    
    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des menus
        foreach( (array) self::getConfig() as $id => $attrs ) :
            self::register( $id, $attrs );
        endforeach;
        
        do_action( 'tify_register_nav_menu' );
        
        //
        require_once self::getDirname() .'/Helpers.php';
    }
   
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration d'un menu
     */
    final public static function register( $id, $attrs )
    {
        // Bypass
        if( isset( self::$Factory[$id] ) )
            return;
        
        $path = array();
        if( isset( $attrs['cb'] ) ) :
            $path[] = $attrs['cb'];
        endif;           
        $path[] = "\\". self::getOverrideNamespace() ."\\Components\\NavMenu\\". self::sanitizeControllerName( $id );
        
        return self::$Factory[$id] = self::loadOverride( "\\tiFy\\Components\\NavMenu\\Factory", $path );  
    }
    
    /**
     * Ajout d'une entrée de menu
     */
    final public static function addNode( $id, $attrs )
    {
        // Bypass
        if( ! isset( self::$Factory[$id] ) )
            return;
        $Factory = self::$Factory[$id];
        
        return $Factory::add( $attrs );
    }
    
    /**
     * Affichage d'un menu
     */
    final public static function display( $args, $echo = true )
    {
        $defaults = array(
            'id'                => current( array_keys( self::$Factory ) ),
            'container'         => 'nav', 
            'container_id'      => '',
            'container_class'   => '',
            'menu_id'           => '',
            'menu_class'        => 'menu',
            'depth'             => 0            
        );        
        $args = wp_parse_args( $args, $defaults );
        
        if( ! $args['id'] )
            return;
        if( ! isset( self::$Factory[$args['id']] ) )
            return;
        
        $Factory = self::$Factory[$args['id']];
        
        return $Factory::display( $args, $echo );
    }
}