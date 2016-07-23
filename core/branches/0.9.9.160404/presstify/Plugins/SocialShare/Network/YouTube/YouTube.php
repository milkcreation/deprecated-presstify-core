<?php
namespace tiFy\Plugins\SocialShare\Network\YouTube;

use \tiFy\Environment\App;

class YouTube extends App
{
	/* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions				= array(
		'init',
		'tify_options_register_node'
	);
	
	/* = ACTIONS A DECLENCHER = */
	/** == Initialisation globale == **/
	final public function init()
	{
		require_once $this->Dirname ."/Helpers.php";
	}
	
	/** == Déclaration d'un section de boîte à onglets == **/
	final public function tify_options_register_node()
	{
		tify_options_register_node(	
			array(
				'id' 		=> 'tify_social_share-youtube',
				'parent' 	=> 'tify_social_share',
				'title' 	=> "<i class=\"fa fa-youtube\"></i> ".__( 'YouTube', 'tify' ),
				'cb' 		=> "tiFy\\Plugins\\SocialShare\\Network\\YouTube\\Taboox\\Admin\\Option\\YouTube"	
			)
		);
	}
}