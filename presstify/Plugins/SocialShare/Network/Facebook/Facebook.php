<?php
namespace tiFy\Plugins\SocialShare\Network\Facebook;

use \tiFy\Environment\App;

class Facebook extends App
{
	/* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions				= array(
		'init',
		'wp_enqueue_scripts',
		'wp_ajax_tify_fb_post2feed_callback',
		'wp_ajax_nopriv_tify_fb_post2feed_callback',
		'tify_options_register_node'
	);
	// Fonctions de rappel des actions
	protected $CallActionsFunctionsMap	= array(
		'wp_ajax_tify_fb_post2feed_callback' 		=> 'wp_ajax',
		'wp_ajax_nopriv_tify_fb_post2feed_callback'	=> 'wp_ajax',
	);
	
	/* = ACTIONS A DECLENCHER = */
	/** == Initialisation globale == **/
	final public function init()
	{		
		require_once $this->Dirname ."/Helpers.php";
		
		// Initialisation des scripts
		wp_register_script( 'tiFy_SocialShare_Facebook_Share', $this->Url .'/Share.js', array( 'jquery' ), '150108', true );	
	}
	
	/** == Mise en file des scripts == **/
	final public function wp_enqueue_scripts()
	{
		wp_enqueue_script( 'tiFy_SocialShare_Facebook' );
	}

	/** == == **/
	final public function wp_ajax()
	{
		$output = apply_filters( 'tify_fb_post2feed_callback_handle', '', $_POST['response'], $_POST['attrs'] );		
		
		wp_die( $output );
	}
	
	/** == Déclaration d'un section de boîte à onglets == **/
	final public function tify_options_register_node()
	{
		tify_options_register_node(	
			array(
				'parent' 	=> 'tify_social_share',
				'id' 		=> 'tify_social_share-facebook',				
				'title' 	=> "<i class=\"fa fa-facebook-official\"></i> ". __( 'Facebook', 'tify' ),
				'cb' 		=> "\\tiFy\\Plugins\\SocialShare\\Network\\Facebook\\Taboox\\Option\\PageLink\\Admin\\PageLink"					
			)
		);
	}
}