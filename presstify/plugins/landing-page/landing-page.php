<?php
/*
Plugin Name: Landing Page
Plugin URI: http://presstify.com/theme_manager/addons/landing_page
Description: Page d'attente de site
Version: 1.150724
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

class tiFy_LandingPage{
	/* = ARGUMENTS = */
	public	// Configuration
			$template 			= 'landing-page',	// Nom du fichier de template d'affichage de la landing page (sans extension)
			$allowed_users 		= array(),			// Liste des logins utilisateur autorisés à afficher le site et pas la landing page
			$allowed_logged_in	= true,				// Autorise l'affichage du site aux utilisateurs connectés ( allowed_users doit être vide )
			
			// Paramètres
			/// Chemins
			$dir,
			$uri;
			
	/* = CONSTRUCTEUR = */
	public function __construct(){
		// Définition des chemins
		$this->dir = dirname( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );
		
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_filter( 'query_vars', array( $this, 'wp_query_vars' ), 1 );
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		add_action( 'template_redirect', array( $this, 'wp_template_redirect' ) );
	}
	
	/* = CONFIGURATION = */
	function set_config(){
		$this->template 			= apply_filters( 'tify_landing_page_template', $this->template );
		$this->allowed_users 		= apply_filters( 'tify_landing_page_allowed_users', $this->allowed_users );
		$this->allowed_logged_in	= apply_filters( 'tify_landing_page_allowed_logged_in', $this->allowed_logged_in );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	function wp_init(){
		$this->set_config();
	}
	
	/** == == **/
	function wp_query_vars( $aVars ){
		$aVars[] = 'tify_landing_page';
		return $aVars;
	}
	
	function pre_get_posts( $query ){
		// Bypass
		if( is_admin() )
			return;
		if( ! $this->user_must_have() )
			return;
		if( $this->is_expired() )
			return;
		$query->set( 'tify_landing_page', true );
	}
	
	/** == Redirection de template == **/
	function wp_template_redirect(){
		if( ! get_query_var( 'tify_landing_page' ) )
			return;
		
		get_template_part( $this->template );
		exit;
	}
	
	/* = CONTROLEUR = */
	/** ==   == **/
	function user_must_have(){
		if( ! $user = wp_get_current_user() )
			return;
		
		if( $this->allowed_users )	
			return ( $this->allowed_users && ! in_array(  $user->user_login, $this->allowed_users ) );
		
		if( $this->allowed_logged_in )
			return ( ! is_user_logged_in() );
	}
	
	/** == == **/
	function is_expired( ){
		$expired = apply_filters( 'tify_landing_page_expired', '' );
		if( $expired )
			return ( ( mysql2date( 'U', $expired ) ) < current_time( 'timestamp' ) );
		
	}
}
new tiFy_LandingPage;
