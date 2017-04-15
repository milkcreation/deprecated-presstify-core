<?php
namespace tiFy\Environment;

abstract class App
{
    use Traits\Actions, Traits\Config, Traits\Controllers, Traits\Filters, Traits\Getter, Traits\Helpers, Traits\HelpersNew, Traits\Path, Traits\Setter
    {
        Traits\Actions::__construct as private __ActionsConstruct;
        Traits\Actions::__call as private __ActionsCall;
        Traits\Config::__construct as private __ConfigConstruct;
        Traits\Filters::__construct as private __FiltersConstruct;
        Traits\Filters::__call as private __FiltersCall;
        Traits\Getter::__get as private __GetterGet;
        Traits\Getter::__isset as private __GetterIsset;
        Traits\Helpers::__construct as private __HelpersConstruct;
        Traits\HelpersNew::__construct as private __HelpersNewConstruct;
        Traits\Path::__get as private __PathGet;
        Traits\Path::__isset as private __PathIsset;        
        Traits\Setter::__set as private __SetterSet;
    }

    /* = CONSTRUCTEUR = */
    public function __construct()
    {
        $this->__ActionsConstruct();
        $this->__ConfigConstruct();
        $this->__FiltersConstruct();
        $this->__HelpersConstruct();
        $this->__HelpersNewConstruct();
    }

    /* = APPEL DE METHODE = */
    public function __call( $method_name, $arguments )
    {
        $this->__ActionsCall( $method_name, $arguments );    
        $this->__FiltersCall( $method_name, $arguments );
    }

    /* = RECUPÉRATION D'ATTRIBUTS = */
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

    /* = VÉRIFICATION D'ATTRIBUTS = */
    public function __isset( $name )
    {
        if( $__isset = $this->__GetterIsset( $name ) )
            return $__isset;
        elseif( $__isset = $this->__PathIsset( $name ) )
            return $__isset;
    
        return false;
    }

    /* = DÉFINITION D'ATTRIBUTS = */
    public function __set( $name, $value )
    {
        if( $__set = $this->__EnvSet( $name, $value ) )
            return $__set;
        elseif( $__set = $this->__SetterSet( $name, $value ) )
            return $__set;
        
        return null;
    }
} 