<?php
new tiFy;
class tiFy{
	/* = ARGUMENTS = */
	public	// Configuration
			$version	= '0.9.2.160216',
			$dir,
			$uri,
			$params;
			
	public	// Composant du Coeur
			$capabilities,
			$deprecated,
			$plugins,
			$script_loader,
			
			// Controllers
			$Kernel;
	
	/* = CONSTRUCTEUR = */
	public function __construct(){
		global $tiFy;		
		$tiFy = $this;
		
		// Définition des chemins
		$this->dir = plugin_dir_path( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );
		
		// Chargement de la librairies PressTiFy
		require __DIR__.'/vendor/tiFy/Libraries/ClassLoader/Psr4ClassLoader.php';
		$loader = new \Psr4ClassLoader;
		$loader->addNamespace( 'tiFy', __DIR__.'/vendor/tiFy' );
		$loader->register();
		$this->Kernel = new tiFy\tiFy( $this );		 	
	
		// Chargement des éléments du coeur
		require_once( $this->dir.'/core/admin.php' );
		$this->admin = new tiFy_CoreAdmin( $this );
		require_once( $this->dir.'/core/capabilities.php' );
		$this->capabilities = new tiFy_CoreCapabilities( $this );
		
		require_once( $this->dir.'/core/deprecated.php' );
		$this->deprecated = new tiFy_CoreDeprecated( $this );
		require_once( $this->dir.'/core/helpers.php' );
		
		require_once( $this->dir.'/core/plugins.php' );
		$this->plugins = new tiFy_CorePlugins( $this );			
		require_once( $this->dir.'/core/script-loader.php' );
		$this->script_loader = new tiFy_CoreScriptLoader( $this );
		
		// Classe Abstraite
		/// Configuration
		require_once( $this->dir.'/core/config.php' );
		/// Utilitaires
		require_once( $this->dir.'/core/utils.php' );		
				
		/// Actions Ajax Prédéfinies
		require_once( $this->dir.'/controllers/tify_ajax_actions/tify_ajax_actions.php' );
				
		/// Base de données
		require_once $this->dir .'/controllers/tify_db/tify_db.php';
				
		/// Templates
		/** @todo devient FrontView **/
		require_once $this->dir .'/controllers/tify_template/tify_template.php';
				
		/// Posts d'accroche
		require_once $this->dir .'/controllers/tify_hook_for_archive/tify_hook_for_archive.php';
		
		/// Formulaires
		require_once $this->dir .'/controllers/tify_forms/tify_forms.php';
		
		/// Modales
		/** == @todo doit devenir une lib == **/
		require_once $this->dir .'/controllers/tify_modal/tify_modal.php';
						
		/// Contrôleur des vidéos
		/** == @todo doit devenir une lib == **/
		require_once $this->dir .'/controllers/tify_video/tify_video.php';
		
		// Initialisation des paramètres
		$this->init_params();		
				
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	final public function wp_init(){
		global $locale, $wp_local_package;
		$_locale = preg_split( '/_/', $locale );
		
		// Translation
		load_textdomain( 'tify', $this->dir.'/languages/tify-'. $locale .'.mo' );
	}
	
	/* = PARAMETRAGE = */
	/** == Initialisation des paramètres == **/
	private function init_params(){		
		$this->params 	= array( 
			'config' 		=> array(),
			'plugins'		=> array(),
			'schema'		=> array()
		);
		
		// Paramétrage Natif
		/// Configuration
		if( file_exists( $this->dir .'/config/config.yml' ) )		 
			$this->params['config']		= tiFy_Config::parse_file( $this->dir .'/config/config.yml' );		
		/// Extensions
		if( file_exists( $this->dir .'/config/plugins.yml' ) )		 
			$this->params['plugins']	= tiFy_Config::parse_file( $this->dir .'/config/plugins.yml' );
		/// Modèles
		if( file_exists( $this->dir .'/config/schema.yml' ) )		 
			$this->params['schema']		= tiFy_Config::parse_file( $this->dir .'/config/schema.yml' );
				
		// Surchage du thème actif
		$theme_dir = get_template_directory();
		/// Configuration
		if( file_exists( $theme_dir .'/config/config.yml' ) ) :
			$config = tiFy_Config::parse_file( $theme_dir .'/config/config.yml' );
			$this->params['config'] = wp_parse_args( $config, $this->params['config'] );
		endif;
		/// Table de base de données
		if( file_exists( $theme_dir .'/config/schema.yml' ) ) :	 
			$models = tiFy_Config::parse_file( $theme_dir .'/config/schema.yml' );
			$this->params['schema'] = wp_parse_args( $models, $this->params['schema'] );
		endif;
		/// Formulaires de saisie
		if( file_exists( $theme_dir .'/config/taboox.yml' ) ) :	 
			$this->params['taboox'] = tiFy_Config::parse_file( $theme_dir .'/config/taboox.yml' );
		endif;
		/// Options
		if( file_exists( $theme_dir .'/config/options.yml' ) ) :
			$this->params['options'] = tiFy_Config::parse_file( $theme_dir .'/config/options.yml' );
		endif;
		
		/// Extensions
		if( file_exists( $theme_dir .'/config/plugins.yml' ) ) :
			$plugins = tiFy_Config::parse_file( $theme_dir .'/config/plugins.yml' );				
			if( ! empty( $plugins ) )
				foreach( (array) $plugins as $k => $v )
					if( is_numeric($k) && is_string( $v ) )
						$this->params['plugins'][$v] = array();
					else
						$this->params['plugins'][$k] = $v;
		endif;
	}	

	/* = CONTRÔLEUR = */
	/** == Récupération du répertoire d'installation de PressTiFy == **/
	final public function get_directory(){
		return $this->dir;
	}
	
	/** == Récupération de l'url du répertoire d'installation de PressTiFy == **/
	final public function get_directory_uri(){
		return $this->uri;
	}
	
	/** == Récupération du chemin relatif == 
	 * @todo A SUPPRIMER 
	 **/
	final public function get_relative_path( $filename ){
		return tify_get_relative_path( $filename, $this->dir );
	}
	
	/** == Récupération d'une option de configuration == **/
	final public function get_config( $option ){
		if( isset( $this->params['config'][$option] ) )
			return $this->params['config'][$option];
	}
}