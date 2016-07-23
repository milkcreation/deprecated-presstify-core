<?php
class tiFy_theme_manager_appearance{
	public	$tiFy,
			// Chemins
			$dir,
			$uri,
			$path,
			
			$options;
	/**
	 * Initialisation
	 */
	function __construct(){
		global $tiFy;
	
		$this->tiFy 	= $tiFy;
		// Définition des chemins
		$this->dir 		= dirname( __FILE__ );
		$this->path  	= $this->tiFy->get_relative_path( $this->dir );
		$this->uri		= $this->tiFy->uri . $this->path;
				
		// Action et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'admin_init', array( $this, 'wp_admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_admin_enqueue_scripts' ) );
	}
	
	/**
	 * Définition des options
	 */
	function set_options(){
		$this->options = apply_filters( 'tify_theme_appearance',
			array( 
				'setting_group' 		=> 'tify_theme_options',
				'setting_page_hookname'	=> 'settings_page_tify_theme_options'
			)
		);
	}
	
	/**
	 * Initialisation de Wordpress
	 */
	function wp_init(){
		/// Récupération des options par défaut
		$this->set_options();
		$this->options = wp_parse_args( get_option( 'tify_theme_appearance' ), $this->options );
	}
	
	/**
	 * Initialisation de l'interface d'administration
	 */
	function wp_admin_init(){
		if( $this->options['setting_group'] ) :
			register_setting( $this->options['setting_group'], 'tify_appareance_theme_colors' );
		endif;
		
		if( $this->options['setting_page_hookname'] ) :
			taboox_option_add_node(
				$this->options['setting_page_hookname'],
				array(
					'id' 		=> 'theme-options-appearance',
					'title' 	=> __( 'Apparence', 'tify' )
				)
			);
				
			taboox_option_add_node(
				$this->options['setting_page_hookname'],
				array(
					'id' 		=> 'theme-options-appearance-colors',
					'parent' 	=> 'theme-options-appearance',
					'title' 	=> __( 'Palette de couleur', 'tify' ),
					'cb' 		=> 'tify_taboox_color_palette',
					'args' 		=> array( 'default' => apply_filters( 'tify_appareance_theme_colors', array() ), 'name' => 'tify_appareance_theme_colors' )
				)
			);
		endif;
		
	}
	
	/**
	 * Mise en file des scripts de l'édition des options
	 */
	function wp_admin_enqueue_scripts( $hookname ){
		// Bypass
		if( $this->options['setting_page_hookname'] != $hookname )
			return;
	}

}
global $tify_theme_appearance;
$tify_theme_appearance = new tiFy_theme_manager_appearance;