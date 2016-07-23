<?php
/*
Plugin Name: Preloader
Plugin URI: http://presstify.com/theme-manager/addons/preloader
Description: Affichage d'un Préchargement à l'ouverture des pages du site
Version: 1.150825
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

class tiFy_Preloader{
	/* = ARGUMENTS = */
	public	// Configuration
			/// Liste des thèmes disponibles
			$themes = array(
				'barber-shop', 'big-counter', 'bounce', 'center-atom', 'center-circle', 'center-radar', 'center-simple', 
				'corner-indicator', 'fill-left', 'flash', 'flat-top', 'loading-bar', 'mac-osx', 'minimal'				
			),
			/// Liste des déclinaisons couleur de thème disponibles
			$colors = array( 'black', 'blue', 'green', 'orange', 'pink', 'purple', 'red', 'silver', 'white', 'yellow' ),
			
			// Paramètres			
			$uri = '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2', // Url de CDN Alternative : '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2' | '//raw.githubusercontent.com/HubSpot/pace/v1.0.2 '
			$theme,
			$color = 'black';
	
	/* = CONSTRUCTEUR = */
	public function __construct(){		
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array ( $this, 'wp_enqueue_scripts' ), 1 );
		add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_action( 'wp_footer', array( $this, 'wp_footer' ), 1 );		
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	public function init(){
		// Définition des paramètres
		$defaults = array( 'uri' => $this->uri, 'theme' => $this->theme, 'color' => $this->color );
		$options = apply_filters( 'tify_preloader_options', $defaults );	
		
		foreach( $options as $option_name => $option_value )
			if( array_key_exists( $option_name, $defaults ) )
				$this->{$option_name} = $option_value;
		
		if( ! $this->uri ) :
			global $tiFy;
			$this->uri = $tiFy->uri .'/assets/pace';
		endif;
			
		// Déclaration des scripts
		wp_register_script( 'pace', $this->uri .'/pace.min.js', array(), '1.0.2', false );
		
		$color = in_array( $this->color, $this->colors ) ? $this->color : false;
		$this->theme = in_array( $this->theme, $this->themes ) ? $this->theme : 'minimal';
		$uri_theme = $this->uri . ( $color ? '/themes/'. $color .'/' : '/templates/' ) . 'pace-theme-'. $this->theme . ( $color ? '' : '.tmpl' ) .'.css';		
				
		wp_register_style( 'pace-theme-'. $this->theme, $uri_theme, array(), '1.0.2' );
	}
	
	/** == Mise en file des scripts == **/
	public function wp_enqueue_scripts(){
		wp_enqueue_script( 'pace' );
		wp_enqueue_style( 'pace-theme-'. $this->theme );
	}
	
	/** == == **/
	public function wp_head(){
	?><style type="text/css">.pace,.pace .pace-progress,.pace .pace-activity{z-index:100001;}#tify_preloader{position:fixed;top:0; right:0; bottom:0; left:0;z-index:100000;}</style><?php
	}
	
	/** == == **/
	public function wp_footer(){
	?><div id="tify_preloader"><?php echo apply_filters( 'tify_preloader', '' );?></div><?php
	}
}
new tiFy_Preloader;