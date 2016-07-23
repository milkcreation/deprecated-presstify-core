<?php
namespace tiFy\Core;

use tiFy\Environment\App;

class AjaxActions extends App
{
	/* = CONSTRUCTEUR = */
	public function __construct( )
	{
		// Actions et Filtres Wordpress
		add_action( 'wp_ajax_tify_get_post_permalink', array( $this, 'ajax_get_post_permalink' ) );
	}
	
	/* = TRAITEMENT AJAX = */
	/** == Récupération d'un permalien de post selon son ID ==
	 * @param (int)		post_id		// (requis) id du post
	 * @param (bool)	relative	// Récupération du lien en relatif 
	 * @param (string)	default		// Url par defaut si le permalien n'est pas trouvé 
	 **/
	public function ajax_get_post_permalink()
	{
		// Arguments par defaut à passer en $_POST
		$args = array(
			'post_id'	=> 0,	
			'relative'	=> true,
			'default'	=> site_url( '/' )
		);
		extract( $args );
		
		// Traitement des arguments de requête
		if( isset( $_POST['post_id'] ) )
			$post_id = intval( $_POST['post_id'] );
		if( ! empty( $_POST['relative'] ) ) 
			$relative = $_POST['relative'];
		if( isset( $_POST['default'] ) )
			$default = $_POST['default'];
		
		// Traitement du permalien
		$permalink = ( $_permalink = get_permalink( $post_id ) ) ? $_permalink : $default;
		if( $relative )
			$permalink = preg_replace( '/'. preg_quote( site_url(), '/' ) .'/', '', $permalink );
		
		wp_die( $permalink );
	}	
}