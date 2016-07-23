<?php
/*
 Plugin Name : Thematizr
 Plugin URI: http://presstify.com/plugins/thematizr
 Description: Gestion des interfaces du thème
 Version: 1.141127
 Author: Milkcreation
 Author URI: http://milkcreation.fr
 Text Domain: tify
*/

namespace tiFy\Plugins\Thematizer;

use tiFy\Environment\Plugin;

class Thematizer extends Plugin
{
	/* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions				= array(
		'init',	
		'tify_options_register_node'
	);
	
	/* = Initialisation de Wordpress = */
	final public function init()
	{
		require_once $this->Dirname .'/inc/Helpers.php';
	}
	
	/* = Déclaration des boîtes à onglets d'options = */
	final public function tify_options_register_node()
	{
		tify_options_register_node(
			array(
				'id' 		=> 'tify_thematizer',
				'title' 	=> __( 'Apparence du thème', 'tify' )
			)
		);
		
		tify_options_register_node(
			array(
				'id' 		=> 'tify_thematizer-color_palette',
				'parent' 	=> 'tify_thematizer',
				'title' 	=> __( 'Palette de couleurs', 'tify' ),
				'cb'		=> "\\tiFy\\Core\\Taboox\\Admin\\Option\\ColorPalette\\ColorPalette",
				'args'		=> array( 'name' => 'tify_thematizer_color_palette', 'colors' => self::getConfig( 'colors' ) )
			)
		);
	}
}