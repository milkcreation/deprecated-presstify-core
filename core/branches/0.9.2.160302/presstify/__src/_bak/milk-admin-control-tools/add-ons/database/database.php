<?php
Class MKCAT_DB{
	/**
	 * Constructeur.
	 */
	function __construct(){
		// Translation du plugin	
		load_plugin_textdomain( 'mkact-addon-db', null, plugin_basename( dirname(__FILE__) ).'/languages' );
		
		// Contrôleur
		require_once dirname(__FILE__)."/inc/tools.php";
	}
}
New MKCAT_DB;
