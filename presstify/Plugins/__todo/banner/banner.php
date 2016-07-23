<?php
/*
Plugin Name: Banner
Plugin URI: http://presstify.com/plugins/banner
Description: Affichage d'une bannière publicitaire
Version: 1.150619
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

class tiFy_Banner{
	/* = ARGUMENTS = */
	public	// Chemins
			$dir,
			$uri,
			// Configuration
			$instance,
			$config = array();
			
	/* = CONSTRUCTEUR = */
	function __construct(){
		// Définition des chemins
		$this->dir = dirname( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );
			
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'wp_ajax_tify_banner_set_cookie', array( $this, 'wp_ajax' ) );
		add_action( 'wp_ajax_nopriv_tify_banner_set_cookie', array( $this, 'wp_ajax' ) );	
		add_shortcode(  'tify_banner', array( $this, 'wp_add_shortcode' ) );
		
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	function wp_init(){
		wp_register_script( 'tify-banner', $this->uri .'banner.js', array( 'jquery' ), 150619, true );
	}
	
	/** == Shortcode == **/
	function wp_add_shortcode( $atts ){
		extract(
	    	shortcode_atts(
	    		array( ),
	    		$atts
			)
		);		
	    return $this->display( $atts, false );
	}
	
	/** == Pied de page == **/
	function wp_footer(){
		wp_enqueue_script( 'tify-banner' );
	}
	
	/** == Action Ajax == **/
	function wp_ajax(){
		setcookie( $_POST['name'], true, time() + $_POST['expire'], SITECOOKIEPATH );
		wp_die(1);
	}
	
	/* = GENERAL TEMPLATE = */
	function display( $args = array() ){
		$this->instance++;
		
		$defaults = array(
			'id' 			=> 'tify_banner-'. $this->instance,
			'class'			=> '',
			'cookie_name'	=> 'tify_banner-'. $this->instance,
			'cookie_expire'	=> DAY_IN_SECONDS
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		if( ! empty( $_COOKIE[ $cookie_name ] ) && ( $_COOKIE[ $cookie_name ] == true )  )
			return;
		
		add_action( 'wp_footer', array( $this, 'wp_footer' ) );
		
		$before	= "<div id=\"{$id}\" class=\"tify_banner {$class}\" data-cookie_name=\"{$cookie_name}\" data-cookie_expire=\"{$cookie_expire}\">";
		$after  = "</div>";
		$output = apply_filters( 'tify_banner', '', $args );
				
		return $before . $output . $after;
	}	
}
new tiFy_Banner;

/* = HELPER = */
/** == Affichage de la bannière == **/
function tify_banner_display( $args = array(), $echo = true ){
	if( $echo )
		echo do_shortcode( '[tify_banner]' );
	else
		return do_shortcode( '[tify_banner]' );
}