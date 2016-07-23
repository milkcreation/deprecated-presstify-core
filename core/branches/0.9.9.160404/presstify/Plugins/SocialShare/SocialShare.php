<?php
/*
 Plugin Name: Social Share
 Plugin URI: http://presstify.com/plugins/social-share
 Description: Partage sur les réseaux sociaux
 Version: 1.150701
 Author: Milkcreation
 Author URI: http://milkcreation.fr
 Text Domain: tify
*/

namespace tiFy\Plugins\SocialShare;

use tiFy\Environment\Plugin;

class SocialShare extends Plugin
{
	/* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions				= array(
		'admin_enqueue_scripts',
		'tify_options_register_node'
	);
	
	public static $Options;
	
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		foreach( (array) self::getConfig( 'networks' ) as $network ) :
			$Class = "tiFy\\Plugins\\SocialShare\\Network\\$network\\$network";
			if( class_exists( $Class ) )
				new $Class;
		endforeach;
		
		self::$Options = get_option( 'tify_social_share', array() );
	}
	
	/* = ACTIONS A DECLENCHER = */
	/** == Mise en file des scripts de l'interface d'administration == **/
	public function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'font-awesome' );
	}
	
	/** == Déclaration de la boîte à onglets == **/
	public function tify_options_register_node()
	{
		if( ! empty( self::getConfig( 'tify_options' ) ) )
			tify_options_register_node(
				array(
					'id' 		=> 'tify_social_share',
					'title' 	=> __( 'Réseaux sociaux', 'tify' ),
				)
			);
	}
}