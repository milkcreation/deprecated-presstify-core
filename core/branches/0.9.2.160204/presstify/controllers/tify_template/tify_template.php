<?php
Class tiFy_Template{
	/* = ARGUMENTS = */
	public	// Paramètres
			$registred_templates = array();
	
	/* = CONSTRUCTEUR = */
	public function __construct(){
		// Actions et Filtres Wordpress
		add_filter( 'query_vars', array( $this, 'wp_query_vars' ) );
		add_action( 'pre_get_posts', array( $this, 'wp_pre_get_posts' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Définition des arguments de requête == **/	
	public function wp_query_vars( $aVars ) {
		$aVars[] = 'tify_template';
		
	  	return $aVars;
	}
	
	/** == == **/
	public function wp_pre_get_posts( &$query ){
		// Bypass
		if( ! $tify_template = $query->get( 'tify_template' ) )
			return;
		
		$query->init_query_flags();

		status_header( 200 );
		nocache_headers();
		
		if( ! array_key_exists( $tify_template, $this->registred_templates ) )	:
			$query->set_404();
		endif;
	}
	
	/* = CONTROLEUR = */
	/** == == **/
	public function register( $template ){
		if( ! array_key_exists( $template, $this->registred_templates ) )
			$this->registred_templates[$template] = array();	
	}
}
global $tify_template;
$tify_template = new tiFy_Template;