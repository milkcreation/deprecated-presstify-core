<?php
/*
 Plugin Name: Restricted Access
 Plugin URI: http://presstify.com/policy/addons/restricted_access
 Description: Restriction d'accès au site
 Version: 1.150130
 Author: Milkcreation
 Author URI: http://milkcreation.fr
 */

class tify_policy_restricted_access{
	/**
	 * Initialisation
	 */
	function __construct(){
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
	}
	
	/**
	 * Affichage de la restriction d'accès aux personnes non habilités
	 */
	function template_redirect(){
		// Vérification des habilitations d'accès au site
		if( apply_filters( 'tify_policy_restricted_access_capabilities', $this->capabilities() ) )
			return;
		// Affichage du message pour les utilisateurs non autorisé
		wp_die( apply_filters( 'tify_policy_restricted_access_message', "<h1>". __( "Ce site est actuellement en cours de développement", "tify" ). "</h1><h3>". __( "Son accès est strictement réservé au personnel habilité, ", "tify" ). "</h3><p>". __( "Veuillez vous identifier pour accéder à la consultation", "tify" ). "</p>" ), 
				apply_filters( 'tify_policy_restricted_access_title',__( 'Site inaccessible', 'tify') ),
				403 
		);	
	}
	
	/**
	 * Vérification des habilitations d'accès au site
	 */
	function capabilities(){
		if( is_user_logged_in() )
			return true;
		
		return false;
	}
}
new tify_policy_restricted_access;	