<?php
/**
 * @todo
 */
namespace tiFy\Abstracts;

abstract class Hooker
{
    /**
     * Listes des accroches d'éléments de template
     */
    protected static $Hooks     = array();
    
    /**
     * CONSTRUCTEUR
     */
    public function __construct( $hooks = array() )
    {
        parent::__construct();
        
        foreach( $hooks as $tag => $functions  ) :
            if( empty( $functions ) )
                continue;
            if( ! isset( self::$Hooks[$tag] ) )
                continue;

            foreach( $functions as $function => $priority ) :
                if( ! isset( self::$Hooks[$tag][$function] ) ) :
                    $this->add( $tag, $function, $priority );
                elseif( ! $priority ) :
                    $this->remove( $tag, $function, self::$Hooks[$tag][$function] );
                elseif( self::$Hooks[$tag][$function] !== (int) $priority ) :
                    $this->change( $tag, $function, $priority );
                endif;
            endforeach;    
        endforeach;
        
        // Contextualisation
        add_action( 'wp', array( $this, 'wp' ), 99 );
    }
    
    /**
     * DECLENCHEURS
     */
    /**
     * Au traitement de la requête principale de wordpress
     */
    public function wp()
    {
       // Contextualisation
        if( $matches = preg_grep( '/^woocommerce_/', get_class_methods( $this ) ) ) :
            foreach( $matches as $tag ) :
                if( ! isset( self::$Hooks[$tag] ) )
                    continue;
                
                call_user_func( array( $this, $tag ) );
            endforeach;
        endif; 
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Accrochage d'un élément de template 
     */
    final public static function add( $tag, $function, $priority = 10 )
    {
        // Bypass
        if( ! isset( self::$Hooks[$tag] ) )
            return;
        
        $function_id = self::getFunctionIdentifier( $function );  
            
        self::$Hooks[$tag][$function_id] = $priority;
        
        return add_action( $tag, $function, $priority );
    }
    
    /**
     * Décrochage d'un élément de template 
     */
    final public static function remove( $tag, $function, $priority = 10 )
    {
        // Bypass
        if( ! isset( self::$Hooks[$tag] ) )
            return;
        
        if( $rm = remove_action( $tag, $function, $priority ) ) :
            unset( self::$Hooks[$tag][$function] );
        endif;       
        
        return $rm;
    }
    
    /**
     * Ré-accrochage d'un élément de template 
     */
    final public static function change( $tag, $function, $priority = 10 )
    {
        // Bypass
        if( ! isset( self::$Hooks[$tag] ) )
            return;
        if( ! isset( self::$Hooks[$tag][$function] ) )
            return;

        if( self::remove( $tag, $function, self::$Hooks[$tag][$function] ) )
            self::add( $tag, $function, $priority );
    }
    
    /**
     * Récupère l'identifiant d'une fonction, une fonction anonyme sera sérialisée 
     */
    protected static function getFunctionIdentifier( $func )
    {
        if( is_string( $func ) )
            return $func;
        
        $rf = new \ReflectionFunction( $func );
        
        return $rf->__toString();
    }
    
    /**
     * SURCHAGE
     */    
    /**
     * Exemple de Contextualisation
     */
    public function woocommerce_before_main_content()
    {
        self::add( __FUNCTION__, '__return_false', 1 );
    }
}