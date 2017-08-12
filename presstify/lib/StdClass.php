<?php
namespace tiFy\Lib;

use tiFy\tiFy;

class StdClass
{
    /** 
     * Formatage d'un nom de classe
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
     * Récupération de la liste des espaces de nom de surcharge
     */
    public static function getOverrideNamespaceList()
    {
        $namespaces = array();
        
        if( ( $theme = tiFy::getConfig( 'theme' ) ) && ! empty( $theme['namespace'] ) ) :
            $namespaces[] = $theme['namespace'];
        endif;
        
        foreach( (array) tiFy::getPlugins() as $plugin ) :
            $namespaces[] = "tiFy\\Plugins\\{$plugin}\\App";
        endforeach;

        return $namespaces;
    }
    
    /** 
     * Récupération de l'espace de nom de surcharge principal
     */
    public static function getOverrideNamespace()
    {
        if( $namespaces = self::getOverrideNamespaceList() )
            return current( $namespaces );
    }
    
    /**
     * Récupération des chemins de surcharge
     */
    public static function getOverridePath($classname)
    {
        $path = array();
        foreach((array) self::getOverrideNamespaceList() as $namespace) :
            $namespace = ltrim( $namespace, '\\' );
            $path[] = "\\". $namespace ."\\". preg_replace( "/^tiFy\\\/", "", ltrim( $classname, '\\' ));
        endforeach;
        
        return $path;
    }
    
    /** 
     * Récupération d'une classe de surcharge
     */
    public static function getOverride( $classname, $path = array() )
    {
        if( empty( $path ) ) :
            $path = self::getOverridePath($classname);
        endif;
        
        foreach( (array) $path as $override ) :
            if( class_exists( $override ) && is_subclass_of( $override, $classname ) ) :
                $classname = $override;
                break;
            endif;
        endforeach;
        
        if( class_exists( $classname ) )
            return $classname;
    }
    
    /** 
     * Chargement d'une classe de surcharge 
     */
    public static function loadOverride( $classname, $path = array() )
    {
        if( $classname = self::getOverride( $classname, $path ) )
            return new $classname;
    }    
}