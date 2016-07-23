<?php
/*
Plugin Name: Cookie Policy
Plugin URI: http://presstify.com/plugins/cookie_policy
Description: Politique des cookies
Version: 1.1500925
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

new tiFy_CookiePolicy;
class tiFy_CookiePolicy{
	/* = ARGUMENTS = */
	private	// Paramètres
			$uri,
			$config;
	
	/* = CONSTRUCTEUR = */
	public function __construct(){
		$this->uri = tiFY_Plugin::get_url( $this );
		$this->config = tiFY_Plugin::get_config( $this );

		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'admin_init', array( $this, 'wp_admin_init' ) );		
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'wp_footer', array( $this, 'wp_footer' ) );		
		add_action( 'wp_ajax_tify_cookie_policy_set_cookie', array( $this, 'wp_ajax' ) );
		add_action( 'wp_ajax_nopriv_tify_cookie_policy_set_cookie', array( $this, 'wp_ajax' ) );
	}	
		
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	public function wp_init(){
		// Déclaration des scripts
		wp_register_script( 'tify-cookie_policy', $this->uri.'/cookie_policy.js', array( 'jquery' ), '141118', true );
		wp_register_style( 'tify-cookie_policy', $this->uri.'/cookie_policy.css', array( 'dashicons' ), '141118' );
	}
	
	/** == Initialisation de l'interface d'administration == **/
	public function wp_admin_init(){
		register_setting( 'reading', 'page_for_cookie_policy' );
		add_settings_section( 
			'tify_cookie_policy_reading_section', 
			__( 'Politique des cookies', 'tify' ), 
			null,
			'reading' 
		);
		add_settings_field( 'page_for_cookie_policy', __( 'Page d\'affichage de la politique des cookies', 'tify' ), array( $this, 'setting_field_render' ), 'reading', 'tify_cookie_policy_reading_section' );		
	}
	
	/** == Instanciation des scripts == **/
	public function wp_enqueue_scripts(){
		wp_enqueue_script( 'tify-cookie_policy' );
		wp_enqueue_style( 'tify-cookie_policy' );
	}
	
	/** == Affichage == **/
	public function wp_footer(){
		$output  = "";
		$output .= "\t<div id=\"tify_cookie_policy\" style=\"". ( ( isset( $_COOKIE[ 'tify-cookie_policy' ] ) && ( $_COOKIE[ 'tify-cookie_policy' ] == true ) ) ? 'display:none;': '' ) ."\">\n";
		$output .= "\t\t<p>{$this->config['text']}</p>\n";
		$output .= "\t\t<a href=\"#tify_cookie_policy-accept\" id=\"tify_cookie_policy-accept\">". __( 'Accepter', 'tify' ) ."</a>&nbsp;&nbsp;";
		if( $this->config['post_id'] && ( $read_url = get_permalink( $this->config['post_id'] ) ) )
			$output .= "\t\t<a href=\"{$read_url}\" id=\"tify_cookie_policy-read\" target=\"_blank\">". __( 'En savoir plus', 'tify' ) ."</a>\n";
		$output .= "\t\t<a href=\"#tify_cookie_policy-close\" id=\"tify_cookie_policy-close\" >{$this->config['close_text']}</a>\n";
		$output .= "\t</div>\n";
		
		echo apply_filters( 'tify_cookie_policy_display', $output, $this->config );
	}
	
	/** == Définition du cookie == **/
	public function wp_ajax(){
		$secure_cookie = is_ssl() && 'https' === parse_url( get_option( 'home' ), PHP_URL_SCHEME );
		setcookie( 'tify-cookie_policy', true, time() + $this->config['cookie_expire'], COOKIEPATH, COOKIE_DOMAIN, $secure_cookie, true );
		if ( COOKIEPATH != SITECOOKIEPATH )
			setcookie( 'tify-cookie_policy', true, time() + $this->config['cookie_expire'], SITECOOKIEPATH, COOKIE_DOMAIN, $secure_cookie, true );
		wp_die(1);
	}
	
	/* = VUES = */	
	/** == Options/Lecture de l'interface d'administration == **/
	public function setting_field_render(){
		wp_dropdown_pages( 
			array( 
				'name' 				=> 'page_for_cookie_policy', 
				'post_type' 		=> 'page', 
				'selected' 			=> get_option( 'page_for_cookie_policy', false ), 
				'show_option_none' 	=> __( 'Aucune page choisie', 'tify' ), 
				'sort_column'  		=> 'menu_order' 
			) 
		);
	}
}