<?php
/**
 * --------------------------------------------------------------------------------
 *	Bootstrap
 * --------------------------------------------------------------------------------
 * 
 * @name 		Admin Control Tools
 * @package    	Worpdress Extends Milkcreation Suite
 * @copyright 	Milkcreation 2012
 * @link 		http://wpextend.milkcreation.fr/admin-control-tools
 * @author 		Jordy Manner
 * @version 	1.1
 * 
Plugin Names: Admin Control Tools
Plugin URI: http://wpextend.milkcreation.fr/admin-control-tools
Description: Wordpress plugin of Admin control tools
Version:1.1
Author: Milkcreation - Jordy Manner
Author URI: http://profil.milkcreation.fr/jordy-manner 
Text Domain: milk-admin-control-tools
*/

/**
 * Constantes du plugin.
 */ 
define( 'MKACT_DIR', dirname(__FILE__) );
define( 'MKACT_URL', WP_PLUGIN_URL.'/'. str_replace( '/'. basename(__FILE__) , '' , plugin_basename(__FILE__) ) );

/**
 * Contrôleur principal du plugin
 */
Class Milk_Admin_Control_Tools{
	var $dir = MKACT_DIR, $url = MKACT_URL, $inc = 'inc', $inc_dir, $inc_url;
	
	/**
	 * Constructeur.
	 */
	function __construct(){
		// Translation du plugin	
		load_plugin_textdomain( 'milk-admin-control-tools', null, plugin_basename( dirname(__FILE__) ).'/languages' );
			
		// Création des chemins
		$this->inc_dir = $this->dir.'/'.$this->inc;
		$this->inc_url = $this->url.'/'.$this->inc;	
		
		// Contrôleur
		require_once $this->inc_dir."/addons.php";
		require_once $this->inc_dir."/pluggable.php";
		require_once $this->inc_dir."/tools.php";
		
		
		// Chargement des addons
		mkact_load_addons();
	}
}
New Milk_Admin_Control_Tools;