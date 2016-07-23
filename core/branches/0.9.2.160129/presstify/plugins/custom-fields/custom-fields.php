<?php
/*
Plugin Name: Custom Fields
Plugin URI: http://presstify.com/plugins/custom-fields
Description: Champs de saisie personnalisé
Version: 1.150328
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

class tiFy_admin_manager_custom_fields{
	public 	// Chemins
			$dir,
			$uri,
			$path,

			$options;
			
	
	/**
	 * Initialisation
	 */
	function __construct(){
		global $tiFy;
	
		$this->tiFy 	= $tiFy;
		// Définition des chemins
		$this->dir 		= dirname( __FILE__ );
		$this->path  	= $this->tiFy->get_relative_path( $this->dir );
		$this->uri		= $this->tiFy->uri . $this->path;
		
		$this->set();

		foreach( $this->active as $field => $active )
			if( $active )
				require_once( $this->dir .'/fields/'. $field .'.php' );
	}
	
	/**
	 *
	 */
	function set(){
		$this->active = apply_filters( 
			'tify_custom_fields_active', 
			get_option( 
				'tify_custom_fields_active',
				array(
					'subtitle' => true
				)
			)
		);
	}
}
new tiFy_admin_manager_custom_fields;