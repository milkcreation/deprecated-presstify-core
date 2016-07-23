<?php
class tiFy_Forum_RewriteMain{
	/* = ARGUMENTS = */
	public	$master;	
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Forum_Master $master ){
		// Déclaration de la classe de référence principale
		$this->master = $master;
		
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ), 1 );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Chargement des MuPlugins achevé == **/
	final public function wp_init(){
		wp_kses_allowed_html();
		// Déclaration de la variable de requête 
		add_rewrite_tag( '%tify_forum%', '([^&]+)' );
	}
}