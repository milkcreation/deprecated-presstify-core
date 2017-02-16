<?php
namespace tiFy\Environment\Traits;

use tiFy\tiFy;

trait Controllers
{
    /* = ARGUMENTS = */
    // Controleurs
	protected static $Controllers           = array();
    
    /** == Récupération d'une classe de surcharge == **/
    public static function getOverride( $ClassName, $path = array() )
    {
        return tiFy::getOverride( $ClassName, $path );
    }
    
    /** == Chargement d'une classe de surcharge == **/
    public static function loadOverride( $ClassName, $path = array() )
    {
        if( $ClassName =  tiFy::getOverride( $ClassName, $path ) )
            return new $ClassName;
    }
    
    /** == == **/
    public static function setController( $id, $Class )
    {           
        if( is_object( $Class) && get_class( $Class ) ) :
        elseif( class_exists( $Class ) ) :
            $Class = self::loadOverride( $Class );
        else :
            return;
        endif;
        
        return self::$Controllers[$id] = $Class;
    }
    
    /** ==  == **/
    public static function getController( $id )
    {
        if( isset( self::$Controllers[$id] ) )            
            return self::$Controllers[$id];
    }
}