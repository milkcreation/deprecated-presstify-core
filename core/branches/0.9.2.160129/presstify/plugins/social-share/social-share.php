<?php
/*
 Addon Name: Social Share
 Addon URI: http://presstify.com/theme_manager/addons/social-share
 Description: Partage sur les réseaux sociaux
 Version: 1.150701
 Author: Milkcreation
 Author URI: http://milkcreation.fr
 */

class tiFy_SocialShare{
	/* = ARGUMENTS = */
	public	// Paramètres
			$dir,
			$uri,
			$config;
						
			
	public	// Contrôleurs
			$fb,
			$tweet,
			$gplus,
			$youtube,
			$instagram,
			$lkin;
	
	/* = CONSTRUCTEUR = */
	function __construct(){
		// Définition des chemins
		$this->dir = tiFY_Plugin::get_dirname( $this );
		$this->uri = tiFY_Plugin::get_url( $this );
		$this->config = tiFY_Plugin::get_config( $this );
				
		// Chargement des contrôleurs
		require_once( $this->dir .'/inc/facebook/facebook-api.php' );
		$this->fb = new tiFy_SocialShare_Facebook( $this );
		
		require_once( $this->dir .'/inc/googleplus/googleplus-api.php' );
		$this->gplus = new tiFy_SocialShare_GooglePlus( $this );	
		
		require_once( $this->dir .'/inc/instagram/instagram-api.php' );
		$this->instagram = new tiFy_SocialShare_Instagram( $this );		
		
		require_once( $this->dir .'/inc/linkedin/linkedin-api.php' );
		$this->lkin = new tiFy_SocialShare_Linkedin( $this );		
		
		require_once( $this->dir .'/inc/pinterest/pinterest-api.php' );
		$this->pinterest = new tiFy_SocialShare_Pinterest( $this );
		
		require_once( $this->dir .'/inc/twitter/twitter-api.php' );			
		$this->tweet = new tiFy_SocialShare_Twitter( $this );
		
		require_once( $this->dir .'/inc/viadeo/viadeo-api.php' );
		$this->viadeo = new tiFy_SocialShare_Viadeo( $this );
		
		require_once( $this->dir .'/inc/youtube/youtube-api.php' );
		$this->youtube = new tiFy_SocialShare_YouTube( $this );
		
		// Actions et Filtres Wordpress
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_admin_enqueue_scripts' ) );
		
		// Actions et Filtres PressTiFy
		add_action( 'tify_options_register_node', array( $this, 'tify_options_register_node' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Mise en file des scripts de l'interface d'administration == **/
	public function wp_admin_enqueue_scripts(){
		wp_enqueue_style( 'font-awesome' );
	}
	
	/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == Déclaration de la boîte à onglets == **/
	public function tify_options_register_node(){
		if( ! empty( $this->config['tify_options'] ) )
			tify_options_register_node(
				array(
					'id' 		=> 'tify_social_share',
					'title' 	=> __( 'Réseaux sociaux', 'tify' ),
				)
			);
	}
	
	/* = CONTRÔLEURS = */
	/** == Vérification de l'activité d'un réseaux == **/
	final public function is_active( $network = null ){
		return ! empty( $this->config['active'][$network] );
	}  
}
global $tify_social_share;
$tify_social_share = new tiFy_SocialShare;