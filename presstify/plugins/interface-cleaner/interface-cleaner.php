<?php
/*
Plugin Name: Interface Cleaner
Plugin URI: http://presstify.com/admin-manager/addons/interface-cleaner
Description: Nettoyage de l'interface d'administration Wordpress
Version: 1.150324
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

new tiFy_InterfaceCleaner;
class tiFy_InterfaceCleaner{
	/* = ARGUMENTS = */
	private	// Paramètres
			$uri,
			$config,
			$meta_boxes = array(
				'post'		=> array(
					'authordiv'			=> 'normal',
					'categorydiv'		=> 'side',
					'commentstatusdiv'	=> 'normal',
					'commentsdiv'		=> 'normal',
					'formatdiv'			=> 'normal',
					'postcustom'		=> 'normal',
					'postexcerpt'		=> 'normal',
					'postimagediv'		=> 'normal',
					'revisionsdiv'		=> 'normal',
					'slugdiv'			=> 'normal',
					'submitdiv'			=> 'side',
					'tagsdiv-post_tag'	=> 'side',
					'trackbacksdiv'		=> 'normal'									
				),
				'page'		=> array(
					'authordiv'			=> 'normal',
					'commentstatusdiv'	=> 'normal',
					'commentsdiv'		=> 'normal',
					'pageparentdiv'		=> 'side',
					'postcustom'		=> 'normal',
					'postimagediv'		=> 'normal',
					'revisionsdiv'		=> 'normal',
					'slugdiv'			=> 'normal',
					'submitdiv'			=> 'side'						
				)			
			);
				
	/* = CONTRUCTEUR = */
	public function __construct(){
		$this->uri = tiFY_Plugin::get_url( $this );
		$this->config = tiFY_Plugin::get_config( $this );

		// Actions et Filtres Wordpress
		add_action( 'after_setup_theme', array( $this, 'wp_after_setup_theme' ) );	
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'widgets_init', array( $this, 'wp_widgets_init' ) );
		add_action( 'admin_menu', array( $this, 'wp_admin_menu' ) );
		add_action( 'wp_before_admin_bar_render', array( $this, 'wp_before_admin_bar_render' ) );
	}
	
	/* = PARAMETRAGE = */
	/** == Définition de la configuration == **/
	private function set_config(){
		$defaults =  array(
			'remove_menu' 					=> array(
				'dashboard' 		=> false,
				'posts' 			=> false,
				'media' 			=> false,
				'pages' 			=> false,
				'comments'			=> false,
				'appearence'		=> false,
				'plugins'			=> false,
				'users'				=> false,
				'tools'				=> false,
				'settings'			=> false
			),
			'remove_post_support' 		=> array( 
				'title'				=> false,
				'editor'			=> false, 
				'author'			=> false,
				'thumbnail'			=> false,
				'excerpt'			=> false,
				'post-formats'		=> false,
				'comments'			=> false,
				'trackbacks'		=> false,
				'custom-fields'		=> false,
				'category'			=> false,
				'revisions'			=> false,
				'post_tag'			=> false 
			),
			'remove_page_support' 		=> array(
				'title'				=> false,
				'editor'			=> false, 
				'author'			=> false,
				'thumbnail'			=> false,
				'page-attributes'	=> false,
				'comments'			=> false,
				'trackbacks'		=> false,
				'custom-fields'		=> false,
				'revisions'			=> false
			),			
			'unregister_widget' 		=> array(
				'pages'				=> false,
				'calendar'			=> false,
				'archives'			=> false,
				'links'				=> false,
				'meta'				=> false,
				'search'			=> false,
				'text'				=> false,
				'categories'		=> false,
				'recent posts'		=> false,
				'recent comments'	=> false,
				'rss'				=> false,
				'tag cloud'			=> false,
				'nav menu'			=> false
			),
			'remove_dashboard_meta_box' 	=> array(
				'right_now' 		=> false,
				'recent_comments'	=> false,
				'incoming_links'	=> false,
				'plugins'			=> false,
				'quick_press'		=> false,
				'recent_drafts'		=> false,
				'activity' 			=> false,
				'primary' 			=> false,
				'secondary' 		=> false			
			),
			'remove_post_meta_box'			=> array(
				'authordiv'			=> false,
				'categorydiv'		=> false,
				'commentstatusdiv'	=> false,
				'commentsdiv'		=> false,
				'formatdiv'			=> false,
				'postcustom'		=> false,
				'postexcerpt'		=> false,
				'postimagediv'		=> false,
				'revisionsdiv'		=> false,
				'slugdiv'			=> false,
				'submitdiv'			=> false,
				'tagsdiv-post_tag'	=> false,
				'trackbacksdiv'		=> false
			),
			'remove_page_meta_box'			=> array(
				'authordiv'			=> false,
				'categorydiv'		=> false,
				'commentstatusdiv'	=> false,
				'commentsdiv'		=> false,
				'formatdiv'			=> false,
				'postcustom'		=> false,
				'postexcerpt'		=> false,
				'postimagediv'		=> false,
				'revisionsdiv'		=> false,
				'slugdiv'			=> false,
				'submitdiv'			=> false,
				'tagsdiv-post_tag'	=> false,
				'trackbacksdiv'		=> false
			),			
			'remove_admin_bar_menu'			=> array(
				'wp_logo' 			=> false,
				'about'				=> false,
				'wporg'				=> false,
				'documentation'		=> false,
				'support-forums'	=> false,
				'feedback'			=> false,
				'site-name'			=> false,
				'view-site'			=> false,
				'updates'			=> false,
				'comments'			=> false,
				'new-content'		=> false,
				'my-account'		=> false
			),
			// Global Actions
			'disable_post'			=> false,
			'disable_post_category'	=> false,
			'disable_post_tag'		=> false,
			'disable_comment'		=> false										
		);

		foreach( (array) array_keys( $defaults ) as $item )
			if( isset( $this->config[$item] ) )
				$this->config[$item] = wp_parse_args( $this->config[$item], $defaults[$item] );
			else
				$this->config[$item] = $defaults[$item];
			
		if( $this->config['disable_post'] ) :
			add_action( 'admin_init', array( $this, 'disable_post_dashboard_meta_box' ) );
			add_action( 'admin_menu', array( $this, 'disable_post_remove_menu' )  );
			add_filter( 'nav_menu_meta_box_object', array( $this, 'disable_post_nav_menu_meta_box_object' ) );
			add_action( 'wp_before_admin_bar_render', array( $this, 'disable_post_wp_before_admin_bar_render' ) );
		endif;
		
		if( $this->config['disable_comment'] ) :			
			add_action( 'init', array( $this, 'disable_comment_init' ) );
			add_action( 'wp_widgets_init', array( $this, 'disable_comment_wp_widgets_init' ) );
			add_action( 'admin_menu', array( $this, 'disable_comment_remove_menu' ) );
			add_action( 'wp_before_admin_bar_render', array( $this, 'disable_comment_wp_before_admin_bar_render' ) );
		endif;
		
		if( $this->config['disable_post_category'] ) :
			add_action( 'init', array( $this, 'disable_post_category' ) );
		endif;
		
		if( $this->config['disable_post_tag'] ) :
			add_action( 'init', array( $this, 'disable_post_tag' ) );
		endif;
	}

	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Chargement du thème == **/
	final public function wp_after_setup_theme(){
		$this->set_config();
	}

	/** == Inititalisation globale == **/
	final public function wp_init() {
		$this->remove_native_post_types_support();
	}

	/** == Initialisation des widgets == **/
	final public function wp_widgets_init() {
		$this->unregister_widget();
	}	
		 
	/** == Initialisation du menu d'administration == **/
	final public function wp_admin_menu(){
		 $this->remove_menu();
		 $this->remove_dashboard_meta_box();
		 $this->remove_post_meta_box();
		 $this->remove_page_meta_box();
	}
	
	/** == Barre d'administration == **/
	final public function wp_before_admin_bar_render(){
		$this->remove_admin_bar_menu();
	}
	
	/* = CONTROLEURS = */
	/** == Suppression des entrées de menus == **/
	private function remove_menu(){
		foreach( $this->config['remove_menu'] as $menu => $disabled )
			if( $disabled )
				switch( $menu ) :
					default :
						remove_menu_page( $menu ); 
						break;
					case 'dashboard' 	:
						remove_menu_page( 'index.php' ); 
						break;
					case 'posts' 		:
						remove_menu_page( 'edit.php' );
						break;
					case 'media' 		:
						remove_menu_page( 'upload.php' );
						break;
					case 'pages'		:
						remove_menu_page( 'edit.php?post_type=page' );
						break;
					case 'comments'		:
						remove_menu_page( 'edit-comments.php' ); 
						break;
					case 'appearence'	:
						remove_menu_page( 'themes.php' );
						break;
					case 'plugins'	:
						remove_menu_page( 'plugins.php' );
						break;
					case 'users'	:
						remove_menu_page( 'users.php' ); 
						break;
					case 'tools'	:
						remove_menu_page( 'tools.php' ); 
						break;
					case 'settings'	:
						remove_menu_page( 'options-general.php' ); 
						break;
				endswitch;
	}

	/** == Suppression des attributs de support natif des articles et des pages == **/
	private function remove_native_post_types_support(){		
		foreach( $this->config['remove_post_support'] as $post_support => $disabled )
			if( $disabled )
				switch( $post_support ) :
					case 'author' :
						remove_post_type_support( 'post', 'author' );
						break;					
					case 'comments' :
						remove_post_type_support( 'post', 'comments' );
						break;
					case 'custom-fields' :
						remove_post_type_support( 'post', 'custom-fields' );
						break;
					case 'editor':
						remove_post_type_support( 'post', 'editor' );
						break;
					case 'excerpt':
						remove_post_type_support( 'post', 'excerpt' );
						break;
					case 'post-formats' :
						remove_post_type_support( 'post', 'post-formats' );
						break;
					case 'revisions' :
						remove_post_type_support( 'post', 'revisions' );
						break;
					case 'title' :
						remove_post_type_support( 'post', 'title' );
						break;
					case 'thumbnail' :
						remove_post_type_support( 'post', 'thumbnail' );
						break;
					case 'trackbacks' :
						remove_post_type_support( 'post', 'trackbacks' );
						break;
				endswitch;
		
		foreach( $this->config['remove_page_support'] as $page_support => $disabled )
			if( $disabled )
				switch( $page_support ) :
					case 'author' :
						remove_post_type_support( 'page', 'author' );
						break;
					case 'comments' :
						remove_post_type_support( 'page', 'comments' );
						break;					
					case 'custom-fields' :
						remove_post_type_support( 'page', 'custom-fields' );
						break;
					case 'editor':
						remove_post_type_support( 'page', 'editor' );
						break;
					case 'page-attributes' :
						remove_post_type_support( 'page', 'page-attributes' );
						break;
					case 'revisions' :
						remove_post_type_support( 'page', 'revisions' );
						break;
					case 'thumbnail' :
						remove_post_type_support( 'page', 'thumbnail' );
						break;
					case 'title' :
						remove_post_type_support( 'page', 'title' );
						break;
					case 'trackbacks' :
						remove_post_type_support( 'page', 'trackbacks' );
						break;
				endswitch;
	}
	
	/** == Suppression des Widgets == **/
	private function unregister_widget(){
		foreach( $this->config['unregister_widget'] as $widget => $disabled )
			if( $disabled )
				switch( $widget ) :
					default :
						unregister_widget( 'WP_Widget_'. preg_replace( '/\s/', '_', ucwords( $widget ) ) );
						break;
					case 'rss' :
						unregister_widget( 'WP_Widget_RSS' );
						break;
					case 'nav menu' :
						unregister_widget( 'WP_Nav_Menu_Widget' );
						break;
				endswitch;		
	}
	
	/** == Suppression de Metaboxe du Tableau de Bord == **/
	private function remove_dashboard_meta_box(){
		foreach( (array) $this->config['remove_dashboard_meta_box'] as $meta_box => $disabled )
			if( $disabled )
				remove_meta_box( 'dashboard_'. $meta_box, 'dashboard', $disabled );
	}
	
	/** == Suppression de Metaboxe des articles == **/
	private function remove_post_meta_box(){
		foreach( (array) $this->config['remove_post_meta_box'] as $meta_box => $context )
			if( $context )
				switch( $context ) :
					default :
						if( in_array( $meta_box, array_keys( $this->meta_boxes['post'] ) ) )						
							remove_meta_box( $meta_box, 'post', $this->meta_boxes['post'][$meta_box] );
						else
							remove_meta_box( $meta_box, 'post', ( is_bool( $context ) ? 'normal' : $context ) );
						break;
					case 'advanced' :
						remove_meta_box( $meta_box, 'post', 'advanced' );
						break;	
					case 'normal' :
						remove_meta_box( $meta_box, 'post', 'normal' );
						break;					
					case 'side' :
						remove_meta_box( $meta_box, 'post', 'side' );
						break;		
				endswitch;		
	}
	
	/** == Suppression de Metaboxe des pages == **/
	private function remove_page_meta_box(){
		foreach( (array) $this->config['remove_page_meta_box'] as $meta_box => $context )
			if( $context )
				switch( $context ) :
					default :
						if( in_array( $meta_box, array_keys( $this->meta_boxes['post'] ) ) )						
							remove_meta_box( $meta_box, 'page', $this->meta_boxes['post'][$meta_box] );
						else
							remove_meta_box( $meta_box, 'page', ( is_bool( $context ) ? 'normal' : $context ) );
						break;
					case 'advanced' :
						remove_meta_box( $meta_box, 'page', 'advanced' );
						break;	
					case 'normal' :
						remove_meta_box( $meta_box, 'page', 'normal' );
						break;					
					case 'side' :
						remove_meta_box( $meta_box, 'page', 'side' );
						break;		
				endswitch;	
	}
	
	/** == Suppression d'entrée de menu de la barre d'administration == **/
	private function remove_admin_bar_menu( ){
		global $wp_admin_bar;
		
		foreach( (array) $this->config['remove_admin_bar_menu'] as $admin_bar_node => $disabled )
			if( $disabled ) 
	   	 		$wp_admin_bar->remove_node( $admin_bar_menu );
	}
	
	/** == Suppression des articles == **/
	/*** === === ***/
	final public function disable_post_dashboard_meta_box() {
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'normal' );
	}
	
	/*** === === ***/
	final public function disable_post_remove_menu(){
		remove_menu_page( 'edit.php' );  
	}
	
	/*** === === ***/
	final public function disable_post_nav_menu_meta_box_object( $post_type ){
		if( $post_type->name === 'post' )
			return false;
		else
			return $post_type;	
	}
	
	/*** === === ***/
	final public function disable_post_wp_before_admin_bar_render() {
		global $wp_admin_bar;
		
		$wp_admin_bar->remove_node( 'new-post' );
	}
	
	/** == Suppression des commentaires == **/
	/*** === === ***/
	final public function disable_comment_init(){
		remove_post_type_support( 'post', 'comments' );
		remove_post_type_support( 'page', 'comments' );
		update_option( 'default_comment_status', 0 );
	}
	
	/*** === === ***/
	final public function disable_comment_wp_widgets_init(){
		unregister_widget( 'WP_Widget_Recent_Comments' );
	}
	
	/*** === === ***/
	final public function disable_comment_remove_menu(){
		remove_menu_page( 'edit-comments.php' ); 
		remove_submenu_page( 'options-general.php', 'options-discussion.php' );
	}
	
	/*** === === ***/
	final public function disable_comment_wp_before_admin_bar_render(){
		global $wp_admin_bar;
		
		$wp_admin_bar->remove_node( 'comments' );
		
		if( is_multisite() )
	   		foreach( wp_get_sites() as $site )
				$wp_admin_bar->remove_menu( 'blog-'. $site['blog_id'] .'-c' );
	}
	
	/** == Suppression des catégories d'article == **/
	final public function disable_post_category(){
		global $wp_taxonomies;
		if( isset( $wp_taxonomies['category'] ) )
			$wp_taxonomies['category']->show_in_nav_menus = false;
		unregister_taxonomy_for_object_type( 'category', 'post' );
	}
	
	/** == Suppression des étiquettes d'article == **/
	final public function disable_post_tag(){
		global $wp_taxonomies;
		if( isset( $wp_taxonomies['post_tag'] ) )
			$wp_taxonomies['post_tag']->show_in_nav_menus = false;
		unregister_taxonomy_for_object_type( 'post_tag', 'post' );
	}	
}