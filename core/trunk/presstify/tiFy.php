<?php
namespace tiFy;

final class tiFy
{
	/* = ARGUMENTS = */
	// Version de PresstiFy
	public static $Version			= '1.0.253';

	// Chemins absolue vers la racine de l'environnement
	public static $AbsPath;

	// Chemins absolue vers la racine de presstiFy
	public static $AbsDir;

	// Url absolue vers la racine la racine de presstiFy
	public static $AbsUrl;
	
	// Paramètres de configuration des éléments de presstiFy
	public static $Params;
	
	// Classe de chargement automatique
	private static $ClassLoader		= null;

	/* = CONSTRUCTEUR = */
	public function __construct( $AbsPath = null )
	{
		if( defined( 'WP_INSTALLING' ) && ( WP_INSTALLING === true ) )
			return;

		global $tiFy;
		$tiFy = $this;
		
		// Définition des constantes d'environnement
		/// Attributs de configuration
		if( ! defined( 'TIFY_CONFIG_DIR' ) )
			define( 'TIFY_CONFIG_DIR', get_template_directory() .'/config' );
		if( ! defined( 'TIFY_CONFIG_EXT' ) )
			define( 'TIFY_CONFIG_EXT', 'yml' );		
		/// Répertoire des plugins
		if( ! defined( 'TIFY_PLUGINS_DIR' ) )
			define( 'TIFY_PLUGINS_DIR', dirname( __DIR__ ) .'/presstify-plugins' );
		
		// Instanciation de l'environnement
		self::classLoad( 'tiFy\Environment', __DIR__ .'/env' );		
			
		// Instanciation des librairies tierce
		self::classLoad( 'tiFy\Vendor', __DIR__ .'/vendor', 'Autoload' );
		
		// Définition des chemins
		self::$AbsPath = ( $AbsPath ) ? $AbsPath : ABSPATH;
		self::$AbsDir = dirname( __FILE__ );
		self::$AbsUrl = \tiFy\Lib\File::getFilenameUrl( self::$AbsDir, self::$AbsPath );

		// Instanciation du coeur
		self::classLoad( 'tiFy\Core', __DIR__ .'/core', 'Autoload' );
		
		// Instanciation des composants
		self::classLoad( 'tiFy\Components', __DIR__ .'/components', 'Autoload' );

		// Instanciation des fonctions d'aides au développement
		self::classLoad( 'tiFy\Helpers', __DIR__ .'/helpers', 'Autoload' );
				
		// Chargement des traductions
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	/* = DECLENCHEURS = */	
	/** == Initialisation globale == **/
	public function load_textdomain()
	{
		return load_textdomain( 'tify', self::$AbsDir .'/languages/tify-' . get_locale() . '.mo' );
	}
	
	/* = CONTROLEURS = */
	/** == Chargement automatique des classes == **/
	public static function classLoad( $namespace, $base_dir, $bootstrap = null )
	{
		if( is_null( self::$ClassLoader ) ) :
			require_once __DIR__ .'/vendor/ClassLoader/Psr4ClassLoader.php';
			self::$ClassLoader = new \Psr4ClassLoader;
		endif;
		
		if( ! $base_dir )
			$base_dir = dirname( __FILE__ );
		
		self::$ClassLoader->addNamespace( $namespace, $base_dir, false );
		self::$ClassLoader->register();
			
		if( $bootstrap ) :
			$class_name = "\\". ltrim( $namespace, '\\' ) ."\\". $bootstrap;
			if( class_exists( $class_name ) ) :
				new $class_name;
			endif;
		endif;
	}
	
	/* = CONTROLEUR = */
	/** == == **/
	public static function getConfig( $index, $default = '' )
	{
		if( isset( self::$Params['config'][$index] ) )
			return self::$Params['config'][$index];
		return $default;
	}
}
