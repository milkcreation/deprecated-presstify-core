<?php
/**
 * -------------------------------------------------------------------------------
 *	RELOCATE SITE
 * --------------------------------------------------------------------------------
 * 
 * @name 		Milkcreation Site Relocate
 * @package    	Worpdress Extend Milkcreation Pack
 * @copyright 	Milkcreation 2013
 * @link 		http://presstify.com/site-relocate
 * @author 		Jordy Manner
 * @version 	1.1

Plugin Name: Site Relocate
Plugin URI: http://presstify.com/site-relocate
Version:1.1
Description: Wordpress plugin of site blocking
Author: Milkcreation - Jordy Manner
Author URI: http://profil.milkcreation.fr/jordy-manner 
Text Domain: milk-site-relocate
*/

define( 'MKRELOC_DIR', dirname(__FILE__) );
define( 'MKRELOC_URL', WP_PLUGIN_URL.'/'. str_replace( '/'. basename(__FILE__) , '' , plugin_basename(__FILE__) ) );

class Milk_Site_Relocate{
	/**
	 * Constructeur.
	 */
	function __construct(){
		// Translation du plugin	
		load_plugin_textdomain( 'milk-site-relocate', null, plugin_basename( MKRELOC_DIR.'/languages/' ) );
		
		// Contrôleurs		
		require_once MKRELOC_DIR.'/inc/tools.php';
	}
}
New Milk_Site_Relocate();