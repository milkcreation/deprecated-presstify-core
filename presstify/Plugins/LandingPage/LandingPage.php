<?php
/*
Plugin Name: Landing Page
Plugin URI: http://presstify.com/theme_manager/addons/landing_page
Description: Page d'attente de site
Version: 1.150724
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

namespace tiFy\Plugins\LandingPage; 

use tiFy\Environment\Plugin;

class LandingPage extends Plugin
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'query_vars',
		'pre_get_posts',
		'template_redirect'
	);
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'query_vars' => 1
	);
			
	/** == == **/
	function wp_query_vars( $aVars )
	{
		$aVars[] = 'tify_landing_page';
		return $aVars;
	}
	
	function pre_get_posts( $query )
	{
		// Bypass
		if( is_admin() )
			return;
		if( $this->isAllowed() )
			return;
		if( $this->isExpired() )
			return;
		
		$query->set( 'tify_landing_page', true );
	}
	
	/** == Redirection de template == **/
	function template_redirect()
	{
		if( ! get_query_var( 'tify_landing_page' ) )
			return;
		
		$exists = false;	
			
		if( ! self::getConfig( 'template' ) ) :			
		elseif( ! file_exists( STYLESHEETPATH . '/' . self::getConfig( 'template' ) .'.php' ) ) :
		elseif( ! file_exists( TEMPLATEPATH . '/' . self::getConfig( 'template' ) .'.php' ) ) :
		else :
			$exists = true;
		endif;
		
		if( ! $exists ) :
			$message = 	"<h3>". __( 'Le gabarit de la page d\'attente est introuvable', 'tify' ) ."</h3>";
			if( ! empty( self::getConfig( 'template' ) ) ) :
				$message .= "<p>". sprintf( __( 'Impossible de trouver le fichier %s dans le dossier du thème courant.', 'tify' ), "<b>". self::getConfig( 'template' ). ".php</b>" ) ."</p>";
			else :
				$message .= "<p>". sprintf( __( 'la propriété "template" de votre fichier de configuration devrait être renseignée.', 'tify' ), self::getConfig( 'template' ). ".php" ) ."</p>";
			endif;
			wp_die( $message, __( 'Page d\'attente introuvable', 'tify' ), 500 );
		else :
			get_template_part( self::getConfig( 'template' ) );
		endif;
		exit;
	}
	
	/* = CONTROLEUR = */
	/** ==   == **/
	private function isAllowed()
	{
		if( ! $user = wp_get_current_user() )
			return false;
		
		if( is_bool( self::getConfig( 'allow_logged_in' ) ) ) :
			if( self::getConfig( 'allow_logged_in' ) === false ) :
				return false;
			else :
				return is_user_logged_in();
			endif;
		elseif( $allowed_users = ( is_string( self::getConfig( 'allow_logged_in' ) ) ) ? array_map( 'trim', explode( ',', self::getConfig( 'allow_logged_in' ) ) ) : self::getConfig( 'allow_logged_in' ) ) :
			return in_array( $user->user_login, $allowed_users );
		endif;
	}
	
	/** == == **/
	private function isExpired()
	{
		if( self::getConfig( 'expiration' ) )
			return ( ( mysql2date( 'U', self::getConfig( 'expiration' ) ) ) < current_time( 'timestamp' ) );		
	}
}