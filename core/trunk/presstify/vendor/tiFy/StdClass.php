<?php
namespace tiFy\Lib;

use tiFy\tiFy;

class StdClass
{
    /** 
     * = Formatage d'un nom de classe =
     * 
     * @param string $classname
     * @return string (ex:my-class_name => MyClass_Name)
     **/
    public static function sanitizeName( $classname )
    {
        $classname = implode( '', array_map( 'ucfirst', explode( '-', $classname ) ) );
        $classname = implode( '_', array_map( 'ucfirst', explode( '_', $classname ) ) );
        
        return $classname;
    }
    
    
    /** 
     * = Récupération de l'espace de nom de surcharge = 
     **/
    public static function getOverrideNamespace()
    {
        if( ( $theme = tiFy::getConfig( 'theme' ) ) && ! empty( $theme['namespace'] ) ) 
            return $theme['namespace'];
    }
    
    /** 
     * = Récupération d'une classe de surcharge = 
     **/
    public static function getOverride( $classname, $path = array() )
    {
        if( empty( $path ) ) :
            $path[] = "\\". self::getOverrideNamespace() . '\\'. preg_replace( "#^\\\#", "", $classname );
        endif;

        foreach( (array) $path as $override ) :
            if( class_exists( $override ) && is_subclass_of( $override, $classname ) ) :
                $classname = $override;
                break;
            endif;
        endforeach;
        
        return $classname;
    }
    
    /** 
     * = Chargement d'une classe de surcharge = 
     **/
    public static function loadOverride( $classname, $path = array() )
    {
        if( $ClassName =  self::getOverride( $classname, $path ) )
            return new $classname;
    }    
}