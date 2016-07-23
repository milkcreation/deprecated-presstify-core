<?php
namespace tiFy;

class tiFy
{
	/* = ARGUMENTS = */
	// Version de PresstiFy
	public static $Version	= '0.9.9.160421';
	
	// Chemins absolue vers la racine
	public static $AbsDir;
	
	// Url absolue vers la racine
	public static $AbsUrl;	
	
	/* = CONSTRUCTEUR = */
	public function __construct()
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
		self::$AbsDir = dirname( __FILE__ );
		self::$AbsUrl = \tiFy\Lib\Utils::get_filename_url( self::$AbsDir );
				
		// Instanciation des librairies
		new Core\Autoload;
				
		// Instanciation des composants
		new Components\Autoload;
									
		// Instanciation des fonctions d'aides au développement
		new Helpers\Autoload;
		
		// Instanciation des plugins
		new Plugins\Autoload;
		
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}
	
	/* = ACTIONS = */
	/** == Initialisation globale == **/
	final public function load_textdomain()
	{
		load_muplugin_textdomain( 'tify', "/presstify/Languages/" );
	}
}
new tiFy;