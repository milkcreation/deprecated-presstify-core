<?php
class tiFy_CorePlugins{	
	public	$plugins = array(),
			$active_plugins = array();
	
	private	$master;
		
	/* = CONSTRUCTEUR = */
	public function __construct( tiFY $master ){		
		$this->master = $master;
				
		// Actions et Filtres Wordpress
		add_action( 'muplugins_loaded', array( $this, 'wp_muplugins_loaded' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Après le chargement des Must-Use Plugins == **/
	public function wp_muplugins_loaded(){
		$this->load_active_plugins();
	}
	
	/* = CONTRÔLEURS = */			
	/** == Récupération des plugins == **/
	public function get_plugins(){
		if( $this->plugins )
			return $this->plugins;
		
	  	if( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	  	
		$plugin_folder = '/'. basename( $this->master->dir ). '/plugins';

		if ( ! empty( $this->plugins ) )
			return $this->plugins;
	
		$wp_plugins = array ();
		$plugin_root = WPMU_PLUGIN_DIR;
		if ( ! empty( $plugin_folder ) )
			$plugin_root .= $plugin_folder;

		// Files in wp-content/plugins directory
		$plugins_dir = @ opendir( $plugin_root );
		$plugin_files = array();

		if ( $plugins_dir ) {
			while (($file = readdir( $plugins_dir ) ) !== false ) {
				if ( substr($file, 0, 1) == '.' )
					continue;
				if ( is_dir( $plugin_root.'/'.$file ) ) {
					$plugins_subdir = @ opendir( $plugin_root.'/'.$file );
					if ( $plugins_subdir ) {
						while (($subfile = readdir( $plugins_subdir ) ) !== false ) {
							if ( substr($subfile, 0, 1) == '.' )
								continue;
							if ( substr($subfile, -4) == '.php' )
								$plugin_files[] = "$file/$subfile";
						}
						closedir( $plugins_subdir );
					}
				} else {
					if ( substr($file, -4) == '.php' )
						$plugin_files[] = $file;
				}
			}
			closedir( $plugins_dir );
		}

		if ( empty($plugin_files) )
			return $wp_plugins;
	
		foreach ( $plugin_files as $plugin_file ) {
			if ( ! is_readable( "$plugin_root/$plugin_file" ) )
				continue;
	
			$plugin_data = get_plugin_data( "$plugin_root/$plugin_file", true, true );
	
			if ( empty ( $plugin_data['Name'] ) )
				continue;
	
			$wp_plugins[plugin_basename( $plugin_file )] = $plugin_data;
		}

		uasort( $wp_plugins, '_sort_uname_callback' );
	
		$this->plugins = $wp_plugins;
	
		return $wp_plugins;
	}

	/** == Récupération des plugins actifs == **/
	public function get_active_plugins(){
		if( ! $this->active_plugins )
			$this->active_plugins = ! empty( $this->master->params['plugins'] ) ? array_keys( $this->master->params['plugins'] ) : array();

		return $this->active_plugins;
	}
	
	/** == Vérification de l'état d'activation d'un plugin == **/
	private function is_active_plugin( $plugin_path ){
		return in_array( $plugin_path, $this->get_active_plugins() );
	}
	
	/** == Initialisation des plugins actifs == **/
	private function load_active_plugins(){		
		call_user_func( array( $this, 'plugin_autoloader' ) );
	}
	
	/** == Chargement automatique des classes == **/
	private function plugin_autoloader() {
		foreach( $this->get_active_plugins() as $name )
			if( file_exists( $this->master->dir .'/plugins/'. $name .'/'. $name .'.php' ) )
			   	include $this->master->dir .'/plugins/'. $name .'/'. $name .'.php';
	}
}

abstract class tiFY_Plugin{
	/* = CONTRÔLEUR = */
	/** == Récupération des données du plugin == **/
	public static function get_data( $plugin_class, $markup = true, $translate = true ){
		if( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
		return get_plugin_data( self::get_filename( $plugin_class ), $markup, $translate );
	}
	
	/** == Récupération == **/
	public static function get_filename( $plugin_class ){
		$reflection = new ReflectionClass( $plugin_class );
		return $reflection->getFileName();
	}
	
	/** == Récupération du chemin vers le repertoire == **/
	public static function get_dirname( $plugin_class ){		
		return dirname( self::get_filename( $plugin_class ) );
	}
	
	/** == Récupération du chemin vers le repertoire == **/
	public static function get_basename( $plugin_class ){		
		return basename( self::get_dirname( $plugin_class ) );
	}
	
	/** == Récupération du nom du repertoire == **/
	public static function get_url( $plugin_class ){		
		return untrailingslashit( plugin_dir_url( self::get_filename( $plugin_class ) ) );
	}
	
	/** == Récupération de la configuration == **/	
	public static function get_config( $plugin_class ){
		global $tiFy;
		
		$filename = self::get_dirname( $plugin_class ) .'/config/config.yml';
		$defaults = ( file_exists( $filename ) ) ? tiFy_Config::parse_file( $filename ) : array();

		// Configuration personnalisée
		if( ! empty( $tiFy->params['plugins'][self::get_basename( $plugin_class )] ) ) 			
			return wp_parse_args( $tiFy->params['plugins'][self::get_basename( $plugin_class )], $defaults );
		else
			return $defaults;					
	}
	
	/** == Récupération de la configuration == **/	
	public static function get_schema( $plugin_class ){
		global $tiFy;
		
		$filename = self::get_dirname( $plugin_class ) .'/config/schema.yml';
		$defaults = ( file_exists( $filename ) ) ? tiFy_Config::parse_file( $filename ) : array();

		// Configuration personnalisée
		if( ! empty( $tiFy->params['schema'] ) ) 			
			return $tiFy->params['schema'] = wp_parse_args( $tiFy->params['schema'], $defaults );
		else
			return $tiFy->params['schema'] = $defaults;					
	}
}