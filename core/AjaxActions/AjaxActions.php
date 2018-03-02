<?php
namespace tiFy\Core\AjaxActions;

class AjaxActions extends \tiFy\App\Core
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
	    parent::__construct();
	    
		// Actions et Filtres Wordpress
		/// Récupération d'un permalien de post selon son ID 
		add_action( 'wp_ajax_tify_get_post_permalink', array( $this, 'getPostPermalink' ) );
		
		/// Récupération du code d'intégration d'une vidéo
		add_action( 'wp_ajax_tiFyVideoGetEmbed', array( $this, 'videoGetEmbed' ) );
		add_action( 'wp_ajax_nopriv_tiFyVideoGetEmbed', array( $this, 'videoGetEmbed' ) );	
	}
	
	/* = CONTRÔLEURS = */
	/** == Récupération d'un permalien de post selon son ID == **/
	final public function getPostPermalink()
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
		if( $relative ) :
		    $url_path = parse_url( site_url( '/' ), PHP_URL_PATH );		    
		    $permalink = $url_path . preg_replace( '/'. preg_quote( site_url( '/' ), '/' ) .'/', '', $permalink );
		endif;
		
		wp_die( $permalink );
	}
	
	/** == Récupération du code d'intégration d'une vidéo == **/
	final public function videoGetEmbed()
	{
		if( empty( $_REQUEST['attr']['src'] ) )
			die(0);
		
		wp_die( \tiFy\Lib\Video\Video::getEmbed( $_REQUEST['attr'] ) );
		exit;
	} 
}