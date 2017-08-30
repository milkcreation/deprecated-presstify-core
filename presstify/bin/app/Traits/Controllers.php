<?php
namespace tiFy\App\Traits;

use tiFy\tiFy;
use tiFy\Lib\StdClass;

trait Controllers
{
    /**
     * Liste des controleurs déclarés
     */
	protected static $Controllers           = array();
    
	/**
	 * CONTROLEURS
	 */
	/**
	 * Définition d'un controleur
	 */
    public static function setController( $id, $StdClass )
    {           
        if( is_object( $StdClass ) && get_class( $StdClass ) ) :
        elseif( class_exists( $StdClass ) ) :
            $StdClass = self::loadOverride( $StdClass );
        else :
            return;
        endif;
        
        return self::$Controllers[$id] = $StdClass;
    }
    
    /**
     * Récupération d'un controleur déclaré
     */
    public static function getController( $id )
    {
        if( isset( self::$Controllers[$id] ) )
            return self::$Controllers[$id];
    }
    
    /**
     * Formatage du nom d'un controleur
     */
    public static function sanitizeControllerName( $classname )
    {
        $lcfirst = preg_match( '/^tiFy/', $classname );
        $classname = StdClass::sanitizeName( $classname );
        
        if( $lcfirst  )
            $classname = lcfirst( $classname );
        
        return $classname;
    }
    
    /**
     * Récupération de la liste des espaces de nom de surcharge
     */
    public static function getOverrideNamespaceList()
    {
        return StdClass::getOverrideNamespaceList();
    }
    
    /**
     * Récupération de l'espace de nom de surcharge principal
     */
    public static function getOverrideNamespace()
    {
        return StdClass::getOverrideNamespace();
    }

    /**
     * Récupération des chemins de surcharge
     */
    public static function getOverridePath($classname)
    {
        return StdClass::getOverridePath($classname);
    }
    
    /**
     * Récupération d'une contrôleur de surcharge
     */
    public static function getOverride( $classname, $path = array() )
    {
        return StdClass::getOverride( $classname, $path );
    }
    
    /**
     * Chargement d'un contrôleur de surcharge
     */
    public static function loadOverride( $classname, $path = array() )
    {
        return StdClass::loadOverride( $classname, $path );
    }
    
    /**
     * Initialisation 
     */
    public static function initOverrideAutoloader( $namespace = null, $dirname = null, $autoload = 'Autoload' )
    {
        if( ! $namespace )
            $namespace = self::tFyAppAttr('Namespace');
        if( ! $dirname )
            $dirname = self::tFyAppDirname() .'/app';
        
        foreach(array( 'components', 'core', 'plugins', 'set' ) as $dir) :
            if(! file_exists( $dirname. '/' .$dir ))
                continue;
            tiFy::classLoad( $namespace ."\\App\\". ucfirst( $dir ), $dirname. '/' .$dir, 'Autoload' );
        endforeach;
    }
}