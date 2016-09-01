<?php
namespace tiFy\Environment;

use \tiFy\Environment\App;

abstract class Config extends App
{
	/* = ARGUMENTS = */
	// Nom d'appel
	protected $ClassName;
	
	// Nom de la classe
	protected $ClassShortName;
	
	// Namespace
	protected $Namespace;
	
	// 
	protected $SubDir;
	
	// Schema
	protected $Schema		= false;
	
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
		
		if( $this->Schema )
			$this->initSchema();
	}
	
	/** == Définition des attributs de noms d'appel == **/
	private function initNames()
	{
		$this->ClassShortName = $this->ReflectionClass->getShortName();
		$this->ClassName = $this->ReflectionClass->getName();		
	}
	
	/** == Définition de la configuration == **/
	protected function initConfig()
	{
		$filename = $this->Dirname .'/config/config.yml';
		$class = get_called_class();
		
		self::$DefaultConfig[$class] = ( file_exists( $filename ) ) ? \tiFy\Core\Params::parseAndEval( $filename ) : array();

		// Configuration personnalisée
		if( ! empty( $this->Params[$this->SubDir][$this->ClassName] ) ) :		
			return self::$Config[$class] = wp_parse_args( $this->Params[$this->SubDir][$this->ClassName], self::$DefaultConfig[$class] );			
		elseif( preg_match( '/'. preg_quote( $this->Namespace, '\\' ) .'/', $this->ClassName )	&& ! empty( $this->Params[$this->SubDir][$this->ClassShortName] ) ) :
			return self::$Config[$class] = wp_parse_args( $this->Params[$this->SubDir][$this->ClassShortName], self::$DefaultConfig[$class] );				
		elseif( get_called_class() === 'tiFy_Forms' && ! empty( $this->Params[$this->SubDir]['Forms'] ) ) :
			return self::$Config[$class] = wp_parse_args( $this->Params[$this->SubDir]['Forms'], self::$DefaultConfig[$class] );			
		else :
			return self::$Config[$class] = self::$DefaultConfig[$class];
		endif;
		exit;
	}
	
	/** == Définition du schema == **/
	protected function initSchema()
	{
		$dirname 	= $this->Dirname .'/config/';
		$schema		= array();
		// Récupération du paramétrage natif
		$_dir = @ opendir( $dirname );
		if( $_dir ) :
			while ( ( $file = readdir( $_dir ) ) !== false ) :
				if ( substr( $file, 0, 1 ) == '.' )
						continue;
				$basename = basename( $file, ".yml" );
				if( $basename !== 'schema' )
				 	continue;			
				
				$schema += \tiFy\Core\Params::_parseFilename( "{$dirname}/{$file}", array(), 'yml', array( 'eval' => true ) );
			endwhile;
			closedir( $_dir );
		endif;
		
		foreach( (array) $schema as $id => $entity ) :
			if( isset( $entity['Db'] ) ) :
				\tiFy\Core\Db\Db::Register( $id, $entity['Db'] );
			endif;
			if( isset( $entity['View'] ) ) :
				\tiFy\Core\View\View::Register( $id, $entity['View'] );
			endif;
		endforeach;
	}
	
	
	/** == Récupération de la configuration du composant == **/
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
	
	/** == Définition d'une propriété de la configuration du composant == **/
	public static function setConfig( $index, $value )
	{
		$class = get_called_class();
		
		return static::$Config[$class][$index] = $value;
	}
}