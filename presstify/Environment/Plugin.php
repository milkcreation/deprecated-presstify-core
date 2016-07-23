<?php
namespace tiFy\Environment;

use \tiFy\Environment\App;

abstract class Plugin extends App
{
	/* = ARGUMENTS = */
	// Nom d'appel
	protected $Name;
	// Nom de la classe
	protected $ShortName;
	
	// Configuration par défaut
	protected static $DefaultConfig;
	// Configuration
	protected static $Config;
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();

		// Définition des Arguments
	 	$this->initNames();
		$this->initConfig();
	}
	
	/** == Initialisation du nom d'appel du plugin == **/
	private function initNames()
	{		
		$this->ShortName = $this->ReflectionClass->getShortName();
		$this->Name = $this->ReflectionClass->getName();
	}
		
	/** == Initialisation de la configuration du plugin == **/
	private function initConfig()
	{
		$filename = $this->Dirname .'/config/config.yml';
		$class = get_called_class();
		
		$defaults = ( file_exists( $filename ) ) ? \tiFy\Core\Params::parseFile( $filename ) : array();
		self::$DefaultConfig[$class] = \tiFy\Core\Params::evalPHP( $defaults );

		// Configuration personnalisée
		if( ! empty( $this->Params['plugins'][$this->Name] ) ) :
			$config = \tiFy\Core\Params::evalPHP( $this->Params['plugins'][$this->Name] );			
			return self::$Config[$class] = wp_parse_args( $config, self::$DefaultConfig[$class] );	
			
		elseif( preg_match( '/'. preg_quote( 'tiFy\\Plugins\\', '\\' ) .'/', $this->Name )	&& ! empty( $this->Params['plugins'][$this->ShortName] ) ) :
			$config = \tiFy\Core\Params::evalPHP( $this->Params['plugins'][$this->ShortName] );	
			return self::$Config[$class] = wp_parse_args( $config, self::$DefaultConfig[$class] );	
		else :
			return self::$Config[$class] = self::$DefaultConfig[$class];
		endif;
	}
	
	/** == Récupération de la configuration == **/
	public static function getConfig( $index = null )
	{
		$class = get_called_class();
		
		if( ! $index ) :
			return isset( static::$Config[$class] ) ? static::$Config[$class] : array();
		elseif( isset( static::$Config[$class][$index] ) ) :
			return static::$Config[$class][$index];
		endif;
	}
	
	/** == Récupération de la configuration par défaut du composant == **/
	public static function getDefaultConfig( $index = null )
	{
		$class = get_called_class();
	
		if( ! $index ) :
			return static::$DefaultConfig[$class];
		elseif( isset( static::$DefaultConfig[$class][$index] ) ) :
			return static::$DefaultConfig[$class][$index];
		endif;
	}
	
	/** == Définition d'une propriété de la configuration == **/
	public static function setConfig( $index, $value )
	{
		$class = get_called_class();
		
		return static::$Config[$class][$index] = $value;
	}
}