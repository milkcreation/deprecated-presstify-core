<?php
namespace tiFy\Environment;

abstract class Plugin extends \tiFy\Environment\App
{
	// Namespace
	protected $Namespace 	= 'tiFy\\Plugins\\';
	
	//
	protected $SubDir		= 'plugins';
	
	//
	protected $Schema		= true;
	
	// Données de plugin
	protected static $PluginData;
	
	/* = CONTROLEURS = */	
	/** == Récupération des données du plugin == **/
	public static function getData( $data = null )
	{
		$CalledClass = get_called_class();
		
		if( ! static::$PluginData[$CalledClass] ) :
			$reflection = new \ReflectionClass( $CalledClass );
			static::$PluginData[$CalledClass] = \get_plugin_data( $reflection->getFileName() );
		endif;
		
		if( ! $data ) :
			return isset( static::$PluginData[$CalledClass] ) ? static::$PluginData[$CalledClass] : array();
		elseif( isset( static::$PluginData[$CalledClass][$data] ) ) :
			return static::$PluginData[$CalledClass][$data];
		endif;
	}
	
	/** == Récupère le numéro de version d'un plugin == **/
	public static function getVersion()
	{
		return static::getData( 'Version' );
	}
}