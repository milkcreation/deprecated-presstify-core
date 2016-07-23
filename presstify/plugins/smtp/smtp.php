<?php
/*
Plugin Name: SMTP
Plugin URI: http://presstify.com/core/addons/smtp
Description: Utilisation d'un serveur d'envoi SMTP
Version: 1.150410
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

/**
	USAGE :

 	add_filter( 'tify_smtp_settings', '{function_hook_name}' );
	function function_hook_name( ){
		return array(
			'host'			=> '',
			'port'			=> 25,
			'username'		=> '',
			'password'		=> '',
			'smtp_auth'		=> false,
			'smtp_secure'	=> ''	
		); 
	}
 */

/**
 * @see https://gist.github.com/franz-josef-kaiser/5840282
 */
new tiFy_SMTP; 
class tiFy_SMTP{
	/* = ARGUMENTS = */
	private $settings;
	
	/* = CONSTRUCTEUR = */
	public function __construct(){
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'phpmailer_init', array( $this, 'phpmailer_init' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale de Wordpress == **/
	public function wp_init(){
		// Traitement des réglages du compte
		$defaults = array(
			'host'			=> 'localhost',
			'port'			=> 25,
			'username'		=> '',
			'password'		=> '',
			'smtp_auth'		=> false,
			'smtp_secure'	=> ''
		);
		$this->settings = wp_parse_args( apply_filters( 'tify_smtp_settings', array() ), $defaults );	
	}
	
	/** == Modification des paramètres SMTP de PHPMailer == **/
	public function phpmailer_init( PHPMailer $phpmailer ) {
		$phpmailer->IsSMTP();
		
	    $phpmailer->Host 		= $this->settings['host'];
	    $phpmailer->Port 		= $this->settings['port'];
	    $phpmailer->Username 	= $this->settings['username'];
	    $phpmailer->Password 	= $this->settings['password'];
	    $phpmailer->SMTPAuth 	= $this->settings['smtp_auth'];
		if( $this->settings['smtp_secure'] ) 
	    	$phpmailer->SMTPSecure = $this->settings['smtp_secure']; // ssl | tls	    
	}
}