<?php
namespace tiFy\Plugin;

class Plugin{
	protected	$Dir,
				$Url,
				$Filename,
				$Config;
	
	/* = CONSTRUCTEUR = */
	public function __construct(){
		// Définition de la configuration
		$this->Dir 		= $this->getDirname( $this );
		$this->Url 		= $this->getUrl( $this );
		$this->Config	= $this->getConfig( $this );
		$this->getSchema( $this );
		
		$loader = new \Psr4ClassLoader;
		$loader->addNamespace( 'tiFy\\Plugins\\'. ucfirst( self::getBasename( $this ) ), $this->getDirname( $this ) );
		$loader->register();		
	}
	
	/* = METHODE MAGIQUES = */
	/** == Getter == **/
	public function __get( $nom )
	{	
	    if ( isset( $this->$nom ) )
			return $this->$nom;
	}	
	
	/* = CONTRÔLEUR = */
	/** == Récupération des données du plugin == **/
	public static function getData( $class, $markup = true, $translate = true ){
		if( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
		return get_plugin_data( self::getFileName( $class ), $markup, $translate );
	}
	
	/** == Récupération == **/
	public static function getFilename( $class ){
		$reflection = new \ReflectionClass( $class );
		return $reflection->getFileName();
	}
	
	/** == Récupération du chemin vers le repertoire == **/
	public static function getDirname( $class ){		
		return dirname( self::getFileName( $class ) );
	}
	
	/** == Récupération du chemin vers le repertoire == **/
	public static function getBasename( $class ){		
		return basename( self::getDirname( $class ) );
	}
	
	/** == Récupération du nom du repertoire == **/
	public static function getUrl( $class ){		
		return untrailingslashit( plugin_dir_url( self::getFileName( $class ) ) );
	}
	
	/** == Récupération de la configuration == **/	
	public static function getConfig( $class ){
		global $tiFy;
		
		$filename = self::getDirname( $class ) .'/config/config.yml';
		$defaults = ( file_exists( $filename ) ) ? \tiFy_Config::parse_file( $filename ) : array();

		// Configuration personnalisée
		if( ! empty( $tiFy->params['plugins'][self::getBasename( $class )] ) ) 			
			return wp_parse_args( $tiFy->params['plugins'][self::getBasename( $class )], $defaults );
		else
			return $defaults;					
	}
	
	/** == Récupération de la configuration == **/	
	public static function getSchema( $class ){
		global $tiFy;
		
		$filename = self::getDirname( $class ) .'/config/schema.yml';
		$defaults = ( file_exists( $filename ) ) ? \tiFy_Config::parse_file( $filename ) : array();

		// Configuration personnalisée
		if( ! empty( $tiFy->params['schema'] ) ) 			
			return $tiFy->params['schema'] = wp_parse_args( $tiFy->params['schema'], $defaults );
		else
			return $tiFy->params['schema'] = $defaults;					
	}
}