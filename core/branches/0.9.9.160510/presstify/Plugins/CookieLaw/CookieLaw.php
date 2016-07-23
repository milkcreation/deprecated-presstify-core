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
		$output .= "\t<div id=\"tiFy_CookieLaw\" style=\"". ( ( isset( $_COOKIE[ 'tify_cookie_law_'. COOKIEHASH ] ) && ( $_COOKIE[ 'tify_cookie_law_'.COOKIEHASH ] == true ) ) ? 'display:none;': '' ) ."\">\n";
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
		$expire = (int) self::getConfig( 'cookie_expire' );
		$secure = ( 'https' === parse_url( home_url(), PHP_URL_SCHEME ) );
		setcookie( 'tify_cookie_law_'. COOKIEHASH, true, time() + $expire, COOKIEPATH, COOKIE_DOMAIN, $secure, true );
		if ( COOKIEPATH != SITECOOKIEPATH )
			setcookie( 'tify_cookie_law_'. COOKIEHASH, true, time() + $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $secure, true );
		wp_die(1);
	}
}