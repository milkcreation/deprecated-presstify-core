<?php
namespace tiFy\Plugins\SocialShare\Network\Twitter;

use \tiFy\Environment\App;

class Twitter extends App
{
	/* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions				= array(
		'init',
		'wp_enqueue_scripts',
		'tify_options_register_node'
	);
	
	/* = ACTIONS A DECLENCHER = */
	/** == Initialisation globale == **/
	final public function init()
	{		
		require_once $this->Dirname ."/Helpers.php";
		
		// Initialisation des scripts
		wp_register_script( 'tify_social_share_twitter_widgets', '//platform.twitter.com/widgets.js', array( ), '20150109', true );
		wp_register_script( 'tify_social_share_twitter', $this->Url .'/Twitter.js', array( 'jquery', 'tify_social_share_twitter_widgets' ), '20150109', true );
	}
	
	/** == Mise en file des scripts == **/
	final public function wp_enqueue_scripts()
	{
		wp_enqueue_script( 'tify_social_share_twitter' );
	}
	
	/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == Déclaration d'une section de boîte à onglets == **/
	final public function tify_options_register_node()
	{
		tify_options_register_node(	
			array(
				'id' 		=> 'tify_social_share-tweet',
				'parent' 	=> 'tify_social_share',
				'title' 	=> "<i class=\"fa fa-twitter\"></i> ". __( 'Twitter', 'tify' ),
				'cb' 		=> "tiFy\\Plugins\\SocialShare\\Network\\Twitter\\Taboox\\Admin\\Option\\Twitter"	
			)
		);
	}
}