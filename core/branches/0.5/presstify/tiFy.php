<?php
new tiFy;
class tiFy{
	/* = ARGUMENTS = */
	public	// Configuration
			$version	= '0.2.151214',
			$dir,
			$uri,
			$params,
			$custom;
			
	public	// Composant du Coeur
			$capabilities,
			$deprecated,
			$plugins,
			$script_loader,
			
			// Controllers
			$control,		/** @todo $Control */
			$Query;
	
	/* = CONSTRUCTEUR = */
	public function __construct(){
		global $tiFy;		
		$tiFy = $this;
		
	 	// Définition des chemins
		$this->dir = plugin_dir_path( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );
		
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
		require_once( $this->dir .'assets/spyc-master/Spyc.php' );
		require_once( $this->dir.'/core/config.php' );
		/// Utilitaires
		require_once( $this->dir.'/core/utils.php' );
		
		// Chargement des contrôleurs 
		/// Actions Ajax Prédéfinies
		require_once( $this->dir.'/controllers/tify_ajax_actions/tify_ajax_actions.php' );
		
		/// Base de données
		require_once $this->dir .'/controllers/tify_db/tify_db.php';
		
		/// Templates
		require_once $this->dir .'/controllers/tify_template/tify_template.php';
		
		/// Contrôleur de champs
		require_once( $this->dir.'/controllers/tify_control/tify_control.php' );
		$this->control = new tiFy_Control_Main( $this );
		
		/// Boîtes à onglets
		require_once $this->dir .'/controllers/tify_taboox/tify_taboox.php';
		
		/// Posts d'accroche
		require_once $this->dir .'/controllers/tify_hook_for_archive/tify_hook_for_archive.php';
		
		/// Formulaires
		require_once $this->dir .'/controllers/tify_forms/tify_forms.php';
		
		/// Modales
		/** == @todo doit devenir une lib == **/
		require_once $this->dir .'/controllers/tify_modal/tify_modal.php';
		
		/// Options
		/** == @todo doit devenir une lib == **/
		require_once $this->dir .'/controllers/tify_options/tify_options.php';
			
		/// Requêtes
		require_once $this->dir .'/controllers/tify_query/tify_query.php';
		$this->Query = new tiFy_QueryMain( $this );	
		
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
			'models'		=> array()
		);
		
		// Paramétrage Natif
		/// Configuration
		if( file_exists( $this->dir .'/config/config.yml' ) )		 
			$this->params['config']	= tiFy_Config::parse_file( $this->dir .'/config/config.yml' );
		/// Modèles
		if( file_exists( $this->dir .'/config/models.yml' ) )		 
			$this->params['config']	= tiFy_Config::parse_file( $this->dir .'/config/models.yml' );
		/// Extensions
		if( file_exists( $this->dir .'/config/plugins.yml' ) )		 
			$this->params['config']	= tiFy_Config::parse_file( $this->dir .'/config/plugins.yml' );
		
		// Surchage du thème actif
		$theme_dir = get_template_directory();
		/// Configuration
		if( file_exists( $theme_dir .'/config/config.yml' ) ) :
			$config = tiFy_Config::parse_file( $theme_dir .'/config/config.yml' );
			$this->params['config'] = wp_parse_args( $config, $this->params['config'] );
		endif;
		/// Modèles
		if( file_exists( $theme_dir .'/config/models.yml' ) ) :	 
			$models = tiFy_Config::parse_file( $theme_dir .'/config/config.yml' );
			$this->params['models'] = wp_parse_args( $models, $this->params['config'] );
		endif;
		/// Modèles
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