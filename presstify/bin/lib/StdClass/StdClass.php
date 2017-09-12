<?php
namespace tiFy\Lib;

use tiFy\tiFy;
use tiFy\Apps;

class StdClass
{
    /** 
     * Formatage d'un nom de classe
     * ex: my-class_name => MyClass_Name
     * 
     * @param string $classname
     * 
     * @return string
     **/
    public static function sanitizeName($classname = null)
    {
        if (!$classname)
            $classname = get_called_class();

        if (is_object($classname))
            $classname = get_class($classname);

        $classname = implode( '', array_map( 'ucfirst', explode( '-', $classname ) ) );
        $classname = implode( '_', array_map( 'ucfirst', explode( '_', $classname ) ) );
                
        return $classname;
    }    
    
    /** 
     * Récupération de la liste des espaces de nom de surcharge
     * 
     * @return string[]
     */
    public static function getOverrideNamespaceList()
    {
        $namespaces = array();
        
        if (($app = tiFy::getConfig('app')) && ! empty($app['namespace'])) :
            $namespaces[] = $app['namespace'];
        endif;

        foreach ((array) Apps::queryPlugins() as $classname => $attrs) :
            $namespaces[] = "tiFy\\Plugins\\{$attrs['Id']}\\App";
        endforeach;

        foreach ((array) Apps::querySet() as $classname => $attrs) :
            $namespaces[] = "{$attrs['Namespace']}\\App";
        endforeach;

        return $namespaces;
    }
    
    /** 
     * Récupération de l'espace de nom de surcharge principal
     */
    public static function getOverrideNamespace()
    {
        if($namespaces = self::getOverrideNamespaceList())
            return current($namespaces);
    }
    
    /**
     * Récupération des chemins de surcharge
     */
    public static function getOverridePath($classname = null)
    {
        if (!$classname)
            $classname = get_called_class();

        if (is_object($classname))
            $classname = get_class($classname);

        $path = array();
        foreach((array) self::getOverrideNamespaceList() as $namespace) :
            $namespace = ltrim( $namespace, '\\' );
            $path[] = $namespace ."\\". preg_replace( "/^tiFy\\\/", "", ltrim( $classname, '\\' ));
        endforeach;
        
        return $path;
    }
    
    /** 
     * Récupération d'une classe de surcharge
     */
    public static function getOverride($classname = null, $path = array() )
    {
        if (!$classname)
            $classname = get_called_class();

        if (is_object($classname))
            $classname = get_class($classname);

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
    public static function loadOverride($classname = null, $path = array() )
    {
        if (!$classname)
            $classname = get_called_class();

        if (is_object($classname))
            $classname = get_class($classname);

        if($classname = self::getOverride( $classname, $path ) )
            return new $classname;
    }    
}