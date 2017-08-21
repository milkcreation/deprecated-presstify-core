<?php
namespace tiFy\Environment;

use \tiFy\Environment\Inherits\Attrs;
use \tiFy\Environment\Inherits\Config;

abstract class App
{
    use Traits\Actions,
        Traits\Config,
        Traits\Controllers,
        Traits\Filters,
        Traits\Getter,
        Traits\Helpers,
        Traits\HelpersNew,
        Traits\Path,
        Traits\Setter
    {
        Traits\Actions::__construct     as private __ActionsConstruct;
        Traits\Actions::__call          as private __ActionsCall;
        Traits\Filters::__construct     as private __FiltersConstruct;
        Traits\Filters::__call          as private __FiltersCall;
        Traits\Getter::__get            as private __GetterGet;
        Traits\Getter::__isset          as private __GetterIsset;
        Traits\Helpers::__construct     as private __HelpersConstruct;
        Traits\HelpersNew::__construct  as private __HelpersNewConstruct;
        Traits\Path::__get              as private __PathGet;
        Traits\Path::__isset            as private __PathIsset;
        Traits\Setter::__set            as private __SetterSet;
    }
    

    /**
     * Attributs de l'app
     * @var \tiFy\Environment\Inherits\Attrs[]
     */
    private static $__tFyAppAttrs = array();
    
    /**
     * Attributs de configuration de l'app
     * @var \tiFy\Environment\Inherits\Attrs[]
     */
    private static $__tFyAppConfig = array();

    /**
     * CONSTRUCTEUR
     * 
     * @return void
     */
    public function __construct()
    {
        $classname = get_called_class();
        self::$__tFyAppAttrs[$classname] = new Attrs($classname);
        self::$__tFyAppConfig[$classname] = new Config($classname);

        $this->__ActionsConstruct();
        $this->__tFyAppConfigInit();
        $this->__FiltersConstruct();
        $this->__HelpersConstruct();
        $this->__HelpersNewConstruct();
    }

    /**
     * CONTROLEURS
     */
    /**
     * Appel de méthode
     * 
     * @return void
     */
    public function __call( $method_name, $arguments )
    {
        $this->__ActionsCall( $method_name, $arguments );    
        $this->__FiltersCall( $method_name, $arguments );
    }

    /**
     * Récupération d'attributs
     * @param string $name
     * 
     * @return 
     */
    public function __get( $name )
    {
        if( $__get = $this->__EnvGet( $name ) )
            return $__get;
        elseif( $__get = $this->__PathGet( $name ) )
            return $__get;
        elseif( $__get = $this->__GetterGet( $name ) )
            return $__get;
        
        return false;
    }

    /**
     * Vérification d'existance d'attribut
     * @param string $name
     * 
     * @return bool
     */
    public function __isset( $name )
    {
        if( $__isset = $this->__GetterIsset( $name ) ) :
            return $__isset;
        elseif( $__isset = $this->__PathIsset( $name ) ) :
            return $__isset;
        endif;
    
        return false;
    }

    /**
     * Définition d'attribut
     * @param string $name
     * @param mixed $value
     * 
     * @return null|mixed
     */
    public function __set( $name, $value )
    {
        if( $__set = $this->__EnvSet( $name, $value ) )
            return $__set;
        elseif( $__set = $this->__SetterSet( $name, $value ) )
            return $__set;
        
        return null;
    }

    /**
     * Récupération des attributs de l'app
     * @param string classname
     * 
     * @return null|mixed[]
     */
    final public static function __tFyAppGetAttrs($classname = null)
    {
        if(!$classname)
            $classname = get_called_class();
        
        if( isset(self::$__tFyAppAttrs[$classname]))
            return self::$__tFyAppAttrs[$classname]->getList();
    }

    /**
     * Récupération d'un attribut de l'app
     * @param string $attr ReflectionClass | ClassName | ShortName | Namespace | Type | Filename | Dirname | Url | Rel
     * @param string classname
     * 
     * @return null|mixed
     */
    final public static function __tFyAppGetAttr($attr, $classname = null)
    {
        if(!$classname)
            $classname = get_called_class();
        
        if(isset(self::$__tFyAppAttrs[$classname]))
            return self::$__tFyAppAttrs[$classname]->get($attr);
    }

    /**
     * Récupération des attributs de configuration par défaut de l'app
     * @param null|string $name
     * @param string classname
     * 
     * @return null|mixed
     */
    final public static function __tFyAppGetDefaultConfig($name = null, $classname = null)
    {
        if(!$classname)
            $classname = get_called_class();
        
        if( isset( self::$__tFyAppConfig[$classname] ) )
            return self::$__tFyAppConfig[$classname]->getDefaults($name);
    }

    /**
     * Récupération des la liste des attributs de configuration de l'app
     * 
     * @return null|mixed
     */
    final public static function __tFyAppGetConfigList($classname = null)
    {
        if(!$classname)
            $classname = get_called_class();
        
        if( isset( self::$__tFyAppConfig[$classname] ) )
            return self::$__tFyAppConfig[$classname]->getList();
    }

    /**
     * Récupération d'un attribut de configuration de l'app
     * @param string $name
     * @param void|mixed $default
     * @param string classname
     * 
     * @return mixed
     */
    final public static function __tFyAppGetConfig($name, $default = '', $classname = null)
    {
        if(!$classname)
            $classname = get_called_class();
        
        if( isset( self::$__tFyAppConfig[$classname] ) )
            return self::$__tFyAppConfig[$classname]->get($name, $default);
    }

    /**
     * Définition d'un attribut de configuration de l'app
     * @param string $name
     * @param mixed $value
     * @param string classname
     * 
     * @return void
     */
    final public static function __tFyAppSetConfig($name, $value, $classname = null)
    {
        if(!$classname)
            $classname = get_called_class();
        
        if( isset( self::$__tFyAppConfig[$classname] ) )
            self::$__tFyAppConfig[$classname]->set($name, $value);
    }
} 