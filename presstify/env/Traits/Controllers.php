<?php
namespace tiFy\Environment\Traits;

use tiFy\Lib\StdClass;

trait Controllers
{
    /* = ARGUMENTS = */
    // Controleurs
	protected static $Controllers           = array();
    
	/* = CONTROLEURS = */
    /** == Déclaration d'un contrôleur == **/
    public static function setController( $id, $StdClass )
    {           
        if( is_object( $StdClass ) && get_class( $StdClass ) ) :
        elseif( class_exists( $StdClass ) ) :
            $Class = self::loadOverride( $StdClass );
        else :
            return;
        endif;
        
        return self::$Controllers[$id] = $StdClass;
    }
    
    /** == Récupération d'un contrôleur == **/
    public static function getController( $id )
    {
        if( isset( self::$Controllers[$id] ) )            
            return self::$Controllers[$id];
    }
    
    /** == Formatage d'un nom de contrôleur == **/
    public static function sanitizeControllerName( $classname )
    {
        return StdClass::sanitizeName( $classname );
    }
		
    /** == Récupération d'une contrôleur de surcharge == **/
    public static function getOverride( $classname, $path = array() )
    {
        return StdClass::getOverride( $classname, $path );
    }
    
    /** == Chargement d'un contrôleur de surcharge == **/
    public static function loadOverride( $classname, $path = array() )
    {
        return StdClass::loadOverride( $classname, $path );
    }    
}