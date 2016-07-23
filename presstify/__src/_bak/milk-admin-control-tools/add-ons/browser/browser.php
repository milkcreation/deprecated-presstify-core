<?php
/**
 * --------------------------------------------------------------------------------
 *	Bootstrap
 * --------------------------------------------------------------------------------
 * 
 * @name 		Milkcreation File Browser
 * @package    	Worpdress Extends Milkcreation Suite
 * @copyright 	Milkcreation 2012
 * @link 		http://wpextend.milkcreation.fr/file-browser
 * @author 		Jordy Manner
 * @version 	1.1
 * 
Plugin Name: File Browser
Plugin URI: http://wpextend.milkcreation.fr/admin-control-tools
Description: Wordpress plugin of File Browser based on Elfinder
Version:1.1
Text Domain: milk-file-browser
Author: Milkcreation - Jordy Manner
Author URI: http://profil.milkcreation.fr/jordy-manner
*/
define( 'MKBROWSER_URL', WP_PLUGIN_URL.'/'. str_replace( '/'. basename(__FILE__) , '' , plugin_basename(__FILE__) ) );


Class Milk_File_Browser{
	/**
	 * Constructeur.
	 */
	function __construct(){
		// Translation du plugin	
		load_plugin_textdomain( 'milk-file-browser', null, plugin_basename( dirname(__FILE__) ).'/languages' );
		
		// Contrôleurs
		require_once dirname(__FILE__)."/inc/tools.php";		
	}
}
New Milk_File_Browser;