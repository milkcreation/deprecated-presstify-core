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
        $classname = self::parseOverrideClassname($classname);

        $classname = implode('', array_map('ucfirst', explode('-', $classname)));
        $classname = implode('_', array_map('ucfirst', explode('_', $classname)));
                
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

        foreach ((array) Apps::querySet() as $classname => $attrs) :
            $namespaces[] = "{$attrs['Namespace']}\\App";
        endforeach;

        foreach ((array) Apps::queryPlugins() as $classname => $attrs) :
            $namespaces[] = "tiFy\\Plugins\\{$attrs['Id']}\\App";
        endforeach;

        return $namespaces;
    }
    
    /** 
     * Récupération de l'espace de nom de surcharge principal
     *
     * @return string
     */
    public static function getOverrideNamespace()
    {
        if($namespaces = self::getOverrideNamespaceList()) :
            return current($namespaces);
        endif;
    }

    /**
     * Récupération de la liste des repertoires de stockage de surchage d'une classe
     *
     * @return string[]
     */
    public static function getOverrideDirs($classname = null)
    {
        $classname = self::parseOverrideClassname($classname);

        $namespaces = array();

        if (($app = tiFy::getConfig('app')) && ! empty($app['namespace'])) :
            $namespaces[] = $app['namespace'];
        endif;

        foreach ((array) Apps::querySet() as $classname => $attrs) :
            $namespaces[] = "{$attrs['Namespace']}\\App";
        endforeach;

        foreach ((array) Apps::queryPlugins() as $classname => $attrs) :
            $namespaces[] = "tiFy\\Plugins\\{$attrs['Id']}\\App";
        endforeach;

        return $namespaces;
    }
    
    /**
     * Récupération de la liste des chemins de surcharge d'une classe
     *
     * @return string[]
     */
    public static function getOverridePath($classname = null)
    {
        $classname = self::parseOverrideClassname($classname);

        $path = array();
        foreach((array) self::getOverrideNamespaceList() as $namespace) :
            $namespace = ltrim( $namespace, '\\' );
            $path[] = $namespace ."\\". preg_replace( "/^tiFy\\\/", "", ltrim( $classname, '\\' ));
        endforeach;
        
        return $path;
    }
    
    /** 
     * Récupération d'une classe de surcharge
     *
     * @return null|string
     */
    public static function getOverride($classname = null, $path = array() )
    {
        $classname = self::parseOverrideClassname($classname);

        if(empty($path)) :
            $path = self::getOverridePath($classname);
        endif;

        foreach((array)$path as $override) :
            if(class_exists( $override ) && is_subclass_of($override, $classname)) :
                $classname = $override;
                break;
            endif;
        endforeach;
        
        if(class_exists($classname)) :
            return $classname;
        endif;
    }
    
    /** 
     * Chargement d'une classe de surcharge 
     */
    public static function loadOverride($classname = null, $path = array() )
    {
        $classname = self::parseOverrideClassname($classname);

        if($classname = self::getOverride($classname, $path)) :
            return new $classname;
        endif;
    }

    /**
     * Récupération du nom de la classe de surcharge
     *
     * @param object|string classname Instance (objet) ou Nom de la classe de l'applicatif
     *
     * @return string
     */
    public static function parseOverrideClassname($classname = null)
    {
        if (!$classname) :
            $classname = get_called_class();
        endif;

        if (is_object($classname)) :
            $classname = get_class($classname);
        endif;

        return $classname;
    }
}