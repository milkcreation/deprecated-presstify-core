<?php
namespace tiFy\App\Traits;

use tiFy\tiFy;
use tiFy\Lib\StdClass;
use \tiFy\Apps;

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
     * @deprecated
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|object
	 */
    public static function setController($id, $classname)
    {
        if(is_object($classname) && get_class($classname)) :
        elseif(class_exists($classname)) :
            $classname = self::loadOverride($classname);
        else :
            return;
        endif;
        
        return self::$Controllers[$id] = $classname;
    }
    
    /**
     * Récupération d'un controleur déclaré
     * @deprecated
     *
     */
    public static function getController($id)
    {
        if( isset( self::$Controllers[$id] ) )
            return self::$Controllers[$id];
    }
    
    /**
     * Formatage du nom d'un controleur
     *
     * @param null|object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return string
     */
    public static function sanitizeControllerName($classname = null)
    {
        $classname = $classname ? $classname : self::tFyAppClassname();

        $lcfirst = preg_match('/^tiFy/', $classname);
        $classname = StdClass::sanitizeName($classname);
        
        if($lcfirst) :
            $classname = lcfirst($classname);
        endif;
        
        return $classname;
    }
    
    /**
     * Récupération de la liste des espaces de nom de surcharge de l'application
     *
     * @return string[]
     */
    public static function getOverrideNamespaceList()
    {
        return StdClass::getOverrideNamespaceList();
    }
    
    /**
     * Récupération de l'espace de nom de surcharge principal
     *
     * @return string
     */
    public static function getOverrideNamespace()
    {
        return StdClass::getOverrideNamespace();
    }

    /**
     * Récupération de la liste des repertoires de stockage de surchage d'un fichier de contrôle
     *
     * @param null|object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return string[]
     */
    public static function getOverrideAppDirList($classname = null)
    {
        $classname = $classname ? $classname : self::tFyAppClassname();

        $dirs = array();
        if (! $override_path = self::tFyAppOverridePath($classname)) :
            return $dirs;
        endif;

        if (! empty($override_path['theme_app']) && ! is_wp_error($override_path['theme_app']['error'])) :
            $dirs[] = $override_path['theme_app']['path'];
        endif;

        /**
         * @todo

        foreach ((array) Apps::querySet() as $classname => $attrs) :

            $namespaces[] = "{$attrs['Namespace']}\\App";
        endforeach;

        foreach ((array) Apps::queryPlugins() as $classname => $attrs) :
            var_dump($attrs);
            $namespaces[] = "tiFy\\Plugins\\{$attrs['Id']}\\App";
        endforeach;
        */

        return $dirs;
    }

    /**
     * Récupération de la liste des repertoires de stockage de surchage d'une fichier de gabarit
     *
     * @param null|object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return string[]
     */
    public static function getOverrideTemplateDirList($classname = null)
    {
        $classname = $classname ? $classname : self::tFyAppClassname();

        $dirs = array();
        if (! $override_path = self::tFyAppOverridePath($classname)) :
            return $dirs;
        endif;

        if (! empty($override_path['theme_templates']) && ! is_wp_error($override_path['theme_templates']['error'])) :
            $dirs[] = $override_path['theme_templates']['path'];
        endif;

        /**
         * @todo

        foreach ((array) Apps::querySet() as $classname => $attrs) :

        $namespaces[] = "{$attrs['Namespace']}\\App";
        endforeach;

        foreach ((array) Apps::queryPlugins() as $classname => $attrs) :
        var_dump($attrs);
        $namespaces[] = "tiFy\\Plugins\\{$attrs['Id']}\\App";
        endforeach;
         */

        return $dirs;
    }

    /**
     * @param string $file Nom du fichier
     * @param null|object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return mixed
     */
    public static function getOverrideAppFile($file, $classname = null)
    {
        foreach ((array)self::getOverrideAppDirList($classname) as $path) :
            if (file_exists($path . '/' . $file)) :
                include $path . '/' . $file;
                break;
            endif;
        endforeach;
    }

    /**
     * @param string $file Nom du fichier
     * @param null|object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return mixed
     */
    public static function getOverrideTemplateFile($file, $classname = null)
    {
        foreach ((array)self::getOverrideAppDirList($classname) as $path) :
            if (file_exists($path . '/' . $file)) :
                include $path . '/' . $file;
                break;
            endif;
        endforeach;
    }

    /**
     * Récupération de la liste des chemins de surcharge d'une application
     *
     * @param null|object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return string[]
     */
    public static function getOverridePath($classname = null)
    {
        $classname = $classname ? $classname : self::tFyAppClassname();

        return StdClass::getOverridePath($classname);
    }
    
    /**
     * Récupération du contrôleur de surcharge courant d'une application
     *
     * @param null|object|string $classname Instance (objet) ou Nom de la classe de l'application
     * @param null|string[] $classname Liste des chemins de surcharge de recherche
     *
     * @return string
     */
    public static function getOverride($classname = null, $path = array())
    {
        $classname = $classname ? $classname : self::tFyAppClassname();

        return StdClass::getOverride($classname, $path);
    }
    
    /**
     * Chargement d'un contrôleur de surcharge courant d'une application
     *
     * @param null|object|string $classname Instance (objet) ou Nom de la classe de l'application
     * @param null|string[] $classname Liste des chemins de surcharge de recherche
     *
     * @return object
     */
    public static function loadOverride($classname = null, $path = array())
    {
        $classname = $classname ? $classname : self::tFyAppClassname();

        return StdClass::loadOverride($classname, $path );
    }

    /**
     * Initialisation 
     */
    public static function initOverrideAutoloader($namespace = null, $dirname = null, $autoload = 'Autoload')
    {
        if (! $namespace) :
            $namespace = self::tFyAppAttr('Namespace');
        endif;
        if (! $dirname) :
            $dirname = self::tFyAppDirname() . '/app';
        endif;
        
        foreach (array('components', 'core', 'plugins', 'set') as $dir) :
            if (! file_exists($dirname. '/' .$dir)) :
                continue;
            endif;
            tiFy::classLoad($namespace . "\\App\\" . ucfirst($dir), $dirname . '/' . $dir, 'Autoload');
        endforeach;
    }
}