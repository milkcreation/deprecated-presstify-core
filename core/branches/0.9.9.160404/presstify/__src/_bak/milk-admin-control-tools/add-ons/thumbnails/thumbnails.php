<?php
Class MKCAT_THB{
	/**
	 * Constructeur.
	 */
	function __construct(){
		// Translation du plugin	
		load_plugin_textdomain( 'mkact-addon-thb', null, plugin_basename( dirname(__FILE__) ).'/languages' );
		
		// Contrôleur
		require_once dirname(__FILE__)."/inc/regenerate-thumbnails.php";
		require_once dirname(__FILE__)."/inc/tools.php";
	}
}
New MKCAT_THB;