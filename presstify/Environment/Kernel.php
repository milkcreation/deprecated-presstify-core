<?php
namespace tiFy\Environment;

final class Kernel
{
	// Chemin absolue de la racine de PresstiFy
	static protected $AbsDir;
	// Url absolute  de la racine de PresstiFy
	static protected $AbsUrl;
	
	static protected $Capabilities;
	static protected $Params;
	static protected $Plugins;	
	 
	static private $SetAttrs		= array(
		'AbsDir', 'AbsUrl', 'Params'
	);
	
	static private $GetAttrs		= array(
		'AbsDir', 'AbsUrl', 'Params'
	);
	
	
	/* = = */
	static function set( $name, $value )
	{
		if( in_array( $name, self::$SetAttrs ) )
			return self::${$name} = $value;
		
		return false;
	}
	
	/* = = */
	static function get( $name )
	{
		if( in_array( $name, self::$GetAttrs ) )
			return self::${$name};
		
		return false;
	}
}