<?php
class tiFy_tinyMCE_PluginTable{
		/* = ARGUMENTS = */	
	private // Configuration
			$uri,
			// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_tinyMCE $master ){
		$this->master = $master;
		
		// Configuration
		$this->uri = tiFY_Plugin::get_url( $this );
		
		// Déclaration du plugin
		$this->master->register_external_plugin( 'table', $this->uri .'/plugin.min.js' );
	}
}