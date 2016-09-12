<?php
namespace tiFy;

final class tiFy
{
	/* = ARGUMENTS = */
	// Version de PresstiFy
	public static $Version	= '0.9.9.160823';

	// Chemins absolue vers la racine de l'environnement
	public static $AbsPath;

	// Chemins absolue vers la racine de presstiFy
	public static $AbsDir;

	// Url absolue vers la racine la racine de presstiFy
	public static $AbsUrl;
	
	// Paramètres de configuration des éléments de presstiFy
	public static $Params;

	/* = CONSTRUCTEUR = */
	public function __construct( $AbsPath = null )
	{
		if( defined( 'WP_INSTALLING' ) && ( WP_INSTALLING === true ) )
			return;

		global $tiFy;
		$tiFy = $this;
		
		// Définition des constantes
		/// Attribut de configuration
		if( ! defined( 'TIFY_CONFIG_DIR' ) )
			define( 'TIFY_CONFIG_DIR', get_template_directory() .'/config' );
		if( ! defined( 'TIFY_CONFIG_EXT' ) )
			define( 'TIFY_CONFIG_EXT', 'yml' );		
		
		/// Répertoire des plugins
		if( ! defined( 'TIFY_PLUGINS_DIR' ) )
			define( 'TIFY_PLUGINS_DIR', dirname( __DIR__ ) .'/presstify-plugins' );
		
		// Déclaration de l'espace de nom dédié
		require_once __DIR__ .'/vendor/ClassLoader/Psr4ClassLoader.php';
		$loader = new \Psr4ClassLoader;

		$loader->addNamespace( 'tiFy\Components', __DIR__ .'/components' );
		$loader->addNamespace( 'tiFy\Core', __DIR__ .'/core' );
		$loader->addNamespace( 'tiFy\Environment', __DIR__ .'/env' );
		$loader->addNamespace( 'tiFy\Helpers', __DIR__ .'/helpers' );
		$loader->addNamespace( 'tiFy\Vendor', __DIR__ .'/vendor' );
		$loader->addNamespace( 'tiFy\Plugins', TIFY_PLUGINS_DIR );
		$loader->register();
				
		// Instanciation des librairies
		new Vendor\Autoload;
	
		// Définition des chemins vers la racine de PresstiFy
		self::$AbsPath = ( $AbsPath ) ? $AbsPath : ABSPATH;
		self::$AbsDir = dirname( __FILE__ );
		self::$AbsUrl = \tiFy\Lib\Utils::get_filename_url( self::$AbsDir, self::$AbsPath );

		// Instanciation des librairies
		new Core\Autoload;

		// Instanciation des composants
		new Components\Autoload;

		// Instanciation des fonctions d'aides au développement
		new Helpers\Autoload;
				
		add_action( 'after_setup_tify', array( $this, 'load_plugins' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	/* = DECLENCHEURS = */
	/** == Chargement des plugins == **/
	final public function load_plugins()
	{
		if( empty( tiFy::$Params['plugins'] ) )
			return;

		foreach( (array) array_keys( tiFy::$Params['plugins'] ) as $plugin ) :
			
			if( class_exists( $plugin ) ) :
				$ClassName	= $plugin;
			elseif( class_exists( "tiFy\\Plugins\\{$plugin}\\{$plugin}" ) ) :
				$ClassName	= "tiFy\\Plugins\\{$plugin}\\{$plugin}";
			else :
				continue;
			endif;

			new $ClassName;			
		endforeach;
	}
	
	/** == Initialisation globale == **/
	final public function load_textdomain()
	{
		return load_textdomain( 'tify', self::$AbsDir .'/Languages/tify-' . get_locale() . '.mo' );
	}
}
