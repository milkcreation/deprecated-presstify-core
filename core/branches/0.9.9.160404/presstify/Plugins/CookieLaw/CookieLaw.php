<?php
/*
Plugin Name: Cookie Law
Plugin URI: http://presstify.com/plugins/cookie-law
Description: Politique des cookies
Version: 1.1500925
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

namespace tiFy\Plugins\CookieLaw; 

use tiFy\Environment\Plugin;

class CookieLaw extends Plugin
{
	/* = ARGUMENTS = */
	/** == ACTIONS == **/
	// Liste des Actions à déclencher
	protected $CallActions				= array( 
		'init',
		'admin_init',
		'wp_enqueue_scripts',
		'wp_footer',
		'wp_ajax_tiFy_CookieLaw',
		'wp_ajax_nopriv_tiFy_CookieLaw'
	);	
	// Fonctions de rappel des actions
	protected $CallActionsFunctionsMap	= array(
		'wp_ajax_tiFy_CookieLaw' 			=> 'wp_ajax',
		'wp_ajax_nopriv_tiFy_CookieLaw'		=> 'wp_ajax'
	);
			
	/* = ACTIONS = */
	/** == Initialisation globale == **/
	public function init()
	{
		if( $post_id = get_option( 'page_for_cookie_law', 0 ) )
			self::setConfig( 'post_id', $post_id );
		
		// Déclaration des scripts
		wp_register_style( 'tiFy_CookieLaw', $this->Url .'/CookieLaw.css', array( 'dashicons' ), '141118' );
		wp_register_script( 'tiFy_CookieLaw', $this->Url .'/CookieLaw.js', array( 'jquery' ), '141118', true );		
	}
	
	/** == Initialisation de l'interface d'administration == **/
	public function admin_init()
	{
		register_setting( 'reading', 'page_for_cookie_law' );
		add_settings_section( 
			'tify_cookie_law_reading_section', 
			__( 'Politique des cookies', 'tify' ), 
			null,
			'reading' 
		);
		add_settings_field( 
			'page_for_cookie_law', 
			__( 'Page d\'affichage de la politique des cookies', 'tify' ), 
			array( $this, 'setting_field_render' ), 
			'reading', 
			'tify_cookie_law_reading_section' 
		);		
	}
	
	/** == Instanciation des scripts == **/
	public function wp_enqueue_scripts()
	{
		wp_enqueue_script( 'tiFy_CookieLaw' );
		wp_enqueue_style( 'tiFy_CookieLaw' );
	}
	
	/** == Affichage == **/
	public function wp_footer()
	{
		$output  = "";
		$output .= "\t<div id=\"tiFy_CookieLaw\" style=\"". ( ( isset( $_COOKIE[ 'tify_cookie_law' ] ) && ( $_COOKIE[ 'tify_cookie_law' ] == true ) ) ? 'display:none;': '' ) ."\">\n";
		$output .= "\t\t<p>" . self::getConfig( 'text' ) ."</p>\n";
		$output .= "\t\t<a href=\"#tiFy_CookieLaw-accept\" id=\"tiFy_CookieLaw-accept\">". __( 'Accepter', 'tify' ) ."</a>&nbsp;&nbsp;";
		if( self::getConfig( 'post_id' ) && ( $read_url = get_permalink( self::getConfig( 'post_id' ) ) ) )
			$output .= "\t\t<a href=\"{$read_url}\" id=\"tiFy_CookieLaw-read\" target=\"_blank\">". __( 'En savoir plus', 'tify' ) ."</a>\n";
		$output .= "\t\t<a href=\"#tiFy_CookieLaw-close\" id=\"tiFy_CookieLaw-close\" >" . self::getConfig( 'close_text' ) ."</a>\n";
		$output .= "\t</div>\n";
		
		echo $output;
	}
	
	/** == Définition du cookie == **/
	public function wp_ajax()
	{
		$secure_cookie = is_ssl() && 'https' === parse_url( get_option( 'home' ), PHP_URL_SCHEME );
		setcookie( 'tify_cookie_law', true, time() + self::getConfig( 'cookie_expire' ), COOKIEPATH, COOKIE_DOMAIN, $secure_cookie, true );
		if ( COOKIEPATH != SITECOOKIEPATH )
			setcookie( 'tify_cookie_law', true, time() + self::getConfig( 'cookie_expire' ), SITECOOKIEPATH, COOKIE_DOMAIN, $secure_cookie, true );
		wp_die(1);
	}
	
	/* = VUES = */	
	/** == Options/Lecture de l'interface d'administration == **/
	public function setting_field_render()
	{
		wp_dropdown_pages( 
			array( 
				'name' 				=> 'page_for_cookie_law', 
				'post_type' 		=> 'page', 
				'selected' 			=> get_option( 'page_for_cookie_law', 0 ), 
				'show_option_none' 	=> __( 'Aucune page choisie', 'tify' ), 
				'sort_column'  		=> 'menu_order' 
			) 
		);
	}
}