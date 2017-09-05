<?php
namespace tiFy\Components\NavMenu;

class NavMenu extends \tiFy\Environment\Component
{
    /**
     * Classe de rappel d'affichage des menus déclarés
     */
    protected static $Walkers   = array();
    
    /**
     * Liste des greffons
     */
    protected static $Nodes     = array();
    
    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des menus
        foreach( (array) self::tFyAppConfig() as $id => $attrs ) :
            self::register( $id, $attrs );
        endforeach;
        
        do_action( 'tify_register_nav_menu' );

        require_once self::tFyAppDirname() .'/Helpers.php';
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
        if( isset( self::$Walkers[$id] ) )
            return;
        
        $path = array();
        if( isset( $attrs['walker'] ) ) :
            $path[] = $attrs['walker'];
        endif;           
        $path[] = "\\". self::getOverrideNamespace() ."\\Components\\NavMenu\\". self::sanitizeControllerName( $id ) ."\\Walker";
        foreach( self::getOverrideNamespaceList() as $namespace ) :
            $path[] = $namespace ."\\Components\\NavMenu\\Walker";
        endforeach;
        self::$Walkers[$id] = self::loadOverride( "\\tiFy\\Components\\NavMenu\\Walker", $path );
        
        if( isset( $attrs['nodes'] ) ) :
            foreach($attrs['nodes'] as $node_id => $node_attrs) :
                self::addNode($id, $node_attrs);
            endforeach;
        endif;
        
        return self::$Walkers[$id];
    }

    /**
     * Ajout d'une entrée de menu
     * @param string $id
     * @param mixed $attrs
     * 
     * @return void
     */
    final public static function addNode( $id, $attrs )
    {
        // Bypass
        if( ! isset( self::$Walkers[$id] ) )
            return;
        
        if( ! isset( self::$Nodes[$id] ) )
           self::$Nodes[$id] = array(); 
            
        array_push( self::$Nodes[$id], $attrs );
    }

    /**
     * Affichage d'un menu
     * @param array $args
     * @param string $echo
     * @return void|mixed
     */
    final public static function display( $args = array(), $echo = true )
    {
        $defaults = array(
            // Identifiant du menu
            'id'                => current( array_keys( self::$Walkers ) ),
            /**
             * @todo
             */
            'container'         => 'nav', 
            'container_id'      => '',
            'container_class'   => '',
            'menu_id'           => '',
            'menu_class'        => 'menu',
            'depth'             => 0
        );        
        $args = wp_parse_args( $args, $defaults );
        extract( $args );
        
        if( ! $id )
            return;
        if( ! isset( self::$Walkers[$id] ) )
            return;
        if( ! isset( self::$Nodes[$id] ) )
            return;  
        
        $nodes_path[] = "\\". self::getOverrideNamespace() ."\\Components\\NavMenu\\". self::sanitizeControllerName( $id ) ."\\Nodes";
        $nodes_path[] = "\\". self::getOverrideNamespace() ."\\Components\\NavMenu\\Nodes";
        $Nodes = self::loadOverride( '\tiFy\Components\NavMenu\Nodes' );
        $nodes = $Nodes->customs( self::$Nodes[$id] );        
        
        $Walker = self::$Walkers[$id];
        $output = $Walker::output($nodes);

        if( $echo )
            echo $output;
        
        return $output;
    }
}