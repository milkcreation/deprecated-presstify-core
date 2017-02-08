<?php
namespace tiFy\Environment;

use \tiFy\tiFy;

abstract class Config extends \tiFy\Environment\App
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
		$filename = self::getDirname() .'/config/config.yml';
		$class = get_called_class();
		
		self::$DefaultConfig[$class] = ( file_exists( $filename ) ) ? \tiFy\Core\Params::parseAndEval( $filename ) : array();
		
		// Configuration personnalisée
		if( ! empty( tiFy::$Params[$this->SubDir][$this->ClassName] ) ) :			
			return self::$Config[$class] = wp_parse_args( tiFy::$Params[$this->SubDir][$this->ClassName], self::$DefaultConfig[$class] );			
		elseif( preg_match( '/'. preg_quote( $this->Namespace, '\\' ) .'/', $this->ClassName )	&& ! empty( tiFy::$Params[$this->SubDir][$this->ClassShortName] ) ) :
			return self::$Config[$class] = wp_parse_args( tiFy::$Params[$this->SubDir][$this->ClassShortName], self::$DefaultConfig[$class] );				
		elseif( get_called_class() === 'tiFy_Forms' && ! empty( tiFy::$Params[$this->SubDir]['Forms'] ) ) :
			return self::$Config[$class] = wp_parse_args( tiFy::$Params[$this->SubDir]['Forms'], self::$DefaultConfig[$class] );			
		else :
			return self::$Config[$class] = self::$DefaultConfig[$class];
		endif;
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
		
		// Récupération du paramétrage personnalisé
		if( ! empty( tiFy::$Params[$this->SubDir][$this->ClassName]['schema'] ) ) :
			$schema = wp_parse_args( tiFy::$Params[$this->SubDir][$this->ClassName]['schema'], $schema ); 
		elseif( preg_match( '/'. preg_quote( $this->Namespace, '\\' ) .'/', $this->ClassName )	&& ! empty( tiFy::$Params[$this->SubDir][$this->ClassShortName]['schema'] ) ) :
			$schema = wp_parse_args( tiFy::$Params[$this->SubDir][$this->ClassShortName]['schema'], $schema ); 
		endif;
	
		// Traitement du parametrage
		foreach( (array) $schema as $id => $entity ) :
			/// Classe de rappel des données en base
			if( isset( $entity['Db'] ) ) :
				\tiFy\Core\Db\Db::Register( $id, $entity['Db'] );
			endif;
			
			/// Classe de rappel des intitulés
			\tiFy\Core\Labels\Labels::Register( $id, ( isset( $entity['Labels'] ) ? $entity['Labels'] : array() ) );
			
			/// Gabarits de l'interface d'administration
			if( isset( $entity['Admin'] ) ) :
				foreach( (array) $entity['Admin'] as $i => $tpl ) :
					if( ! isset( $tpl['db'] ) )
						$tpl['db'] = $id;
					if( ! isset( $tpl['labels'] ) )
						$tpl['labels'] = $id;
						
					\tiFy\Core\Templates\Templates::Register( $i, $tpl, 'admin' );
				endforeach;
			endif;
			
			/// Gabarits de l'interface utilisateur
			if( isset( $entity['Front'] ) ) :
				foreach( (array) $entity['Front'] as $i => $tpl ) :
					if( ! isset( $tpl['db'] ) )
						$tpl['db'] = $id;
					if( ! isset( $tpl['labels'] ) )
						$tpl['labels'] = $id;
					
					\tiFy\Core\Templates\Templates::Register( $i, $tpl, 'front' );
				endforeach;
			endif;			
		endforeach;
	}	
	
	/** == Récupération de la configuration du composant == **/
	public static function getConfig( $index = null, $class = null )
	{
		if( ! $class )
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