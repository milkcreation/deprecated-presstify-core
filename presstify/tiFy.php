<?php
namespace tiFy;

class tiFy
{
	/* = ARGUMENTS = */
	// Version de PresstiFy
	public static $Version	= '0.9.9.160619';

	// Chemins absolue vers la racine de l'environnement
	public static $AbsPath;

	// Chemins absolue vers la racine de presstiFy
	public static $AbsDir;

	// Url absolue vers la racine la racine de presstiFy
	public static $AbsUrl;

	/* = CONSTRUCTEUR = */
	public function __construct( $AbsPath = null )
	{
		if( defined( 'WP_INSTALLING' ) && ( WP_INSTALLING === true ) )
			return;

		global $tiFy;
		$tiFy = $this;

		// Déclaration de l'espace de nom dédié
		require_once __DIR__ .'/Libraries/ClassLoader/Psr4ClassLoader.php';
		$loader = new \Psr4ClassLoader;
		$loader->addNamespace( 'tiFy', __DIR__ );
		$loader->register();

		// Instanciation des librairies
		new Libraries\Autoload;

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

		// Instanciation des plugins
		if( ! defined( 'TIFY_PLUGINS_DIR' ) )
			define( 'TIFY_PLUGINS_DIR', dirname( __DIR__ ) .'/presstify-plugins' );

		if( file_exists( TIFY_PLUGINS_DIR .'/Plugins.php' ) ) :
			$loader = new \Psr4ClassLoader;
			$loader->addNamespace( 'tiFy\Plugins', TIFY_PLUGINS_DIR );
			$loader->register();

			new Plugins\Plugins;
		endif;

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	/* = ACTIONS = */
	/** == Initialisation globale == **/
	final public function load_textdomain()
	{
		return load_textdomain( 'tify', self::$AbsDir .'/Languages/tify-' . get_locale() . '.mo' );
	}
}
