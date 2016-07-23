<?php
namespace tiFy\Environment;

use tiFy\Environment\Actions;

abstract class App
{
	use Traits\Actions, Traits\Filters, Traits\Getter, Traits\Helpers, Traits\Path, Traits\Setter
	{
		Traits\Actions::__construct as private __ActionsConstruct;
		Traits\Actions::__call as private __ActionsCall;	
		Traits\Filters::__construct as private __FiltersConstruct;
		Traits\Filters::__call as private __FiltersCall;
		Traits\Getter::__get as private __GetterGet;
		Traits\Getter::__isset as private __GetterIsset;
		Traits\Helpers::__construct as private __HelpersConstruct;
		Traits\Path::__get as private __PathGet;
		Traits\Path::__isset as private __PathIsset;		
		Traits\Setter::__set as private __SetterSet;
	}
		
	// Liste des arguments pouvant être récupérés
	protected $GetAttrs	= array();
	
	// Liste des arguments pouvant être défini
	protected $SetAttrs	= array();
		
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		$this->__ActionsConstruct();	
		$this->__FiltersConstruct();
		$this->__HelpersConstruct();
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
		if( $__get = $this->__GetterGet( $name ) )
			return $__get;
		elseif( $__get = $this->__PathGet( $name ) )
			return $__get;
		else
			return false;
	}
	
	/* = VÉRIFICATION D'ATTRIBUTS = */
	public function __isset( $name )
	{
		if( $__isset = $this->__GetterIsset( $name ) )
			return $__isset;
		elseif( $__isset = $this->__PathIsset( $name ) )
			return $__isset;
		else
			return false;
	}
	
	/* = DÉFINITION D'ATTRIBUTS = */
	public function __set( $name, $value )
	{
		return $this->__SetterSet( $name, $value );
	}
} 