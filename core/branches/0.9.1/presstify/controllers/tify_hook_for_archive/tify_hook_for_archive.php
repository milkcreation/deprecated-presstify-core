<?php
/**
 * Usage :
	// Déclaration d'une relation type de post d'accroche <> archive
	add_action( 'tify_hook_for_archive_register', '[function]' );
	function [function](){
		tify_h4a_register( 
			array( 
				'[custom_post_type]',			// [hook_post_type] = page
 				// OU
 			 	'[custom_post_type]' => '[hook_post_type]', //
 				// OU
 			 	'[custom_post_type]' => array(
					'hook_post_type'	=> '[hook_post_type]',
					'hook_id'			=> 0,			// @todo Définition du hook_id
					'entity_type'		=> 'post',		// post (defaut) | @todo taxonomy	
					'display'			=> 'static', 	// static (defaut) | dynamic
					'taboox_auto'		=> true,
					'custom_column'		=> true
				)
			) 
		);
	}
 	// Résultat équivalent à page_for_post
*/
		
class tiFy_HookForArchive_Master{
	/* = ARGUMENTS = */
	public	// Chemins
			$dir,
			$uri,
			
			// Configuration			
			$hooks = array(),
			$hook_ids,			
			$debug = false;
	
	/* = CONSTRUCTEUR = */
	function __construct(){
		// Définition des chemins
		$this->dir = dirname( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );
		
		// Contrôleurs
		/// Post Static
		require_once( $this->dir .'/post-static.php' );
		new tiFy_HookForArchive_PostStatic( $this );
		/// Post Dynamic
		require_once( $this->dir .'/post-dynamic.php' );
		new tiFy_HookForArchive_PostDynamic( $this );
		
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ), 9 );		
		add_action( 'admin_init', array( $this, 'wp_admin_init' ) );
		add_action( 'current_screen', array( $this, 'wp_current_screen' ) );
		add_action( 'admin_bar_menu', array( $this, 'wp_admin_bar_menu' ), 80 );
		add_filter( 'wp_nav_menu_objects', array( $this, 'wp_nav_menu_objects' ), 10, 2 );		
		add_filter( 'post_type_archive_title', array( $this, 'wp_post_type_archive_title' ), 10, 2 );
				
		if( $this->debug )
			add_action( 'admin_init', array( $this, 'debug' ) );	
	}
	
	/* = CONFIGURATION = */
	/** == Déclaration == **/
	function register( $hooks ){
		$defaults = array(
			'hook_post_type'			=> 'page',
			'hook_id'					=> 0,
			'entity_type'				=> 'post',		// post (defaut) | taxonomy	
			'display'					=> 'static', 	// static (defaut) | dynamic | dynamic-multi
			'taboox_auto'				=> true,
			'custom_column'				=> true,
			'hook_post_type_supports'	=> array()
		);
		
		foreach( $hooks as $k => $v ) :
			if( is_string( $k ) ) :		
				if( is_string( $v ) ) :
					$this->hooks[$k] = wp_parse_args( array( 'hook_post_type' => $v  ), $defaults );
				elseif( is_array( $v ) ) :
					$this->hooks[$k] = wp_parse_args( $v, $defaults );
				endif;
			elseif( is_numeric( $k ) ) :
				$this->hooks[$v] = $defaults;
			endif;
		endforeach;
		
		foreach( $this->hooks as $k => &$v ) :			
			if( ! $v['hook_id'] ) :
				$v['hook_id'] = (int) get_option( $v['hook_post_type'] .'_for_'. $k, 0 );
				$this->hook_ids[$v['hook_id']] = $k;
			endif;
		endforeach;
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	function wp_init(){
		do_action( 'tify_hook_for_archive_register' );

		// Réécriture d'url
		add_rewrite_tag( '%tify_hook_id%', '([0-9]{1,})' );	
	}	
	
	/** == Initialisation de l'interface d'administration == **/
	function wp_admin_init(){
		// Bypass
		if( empty( $this->hooks ) )
			return;
		
		add_settings_section( 
			'tify_hook_for_archive_reading_section', 
			__( 'Affichage des archives (page de flux)', 'tify' ), 
			null,
			'reading' 
		);
		
		foreach( $this->hooks as $archive_post_type => $args ) :
			register_setting( 'reading', $args['hook_post_type'] .'_for_'. $archive_post_type );
			$label = get_post_type_object( $archive_post_type )->label;
			\add_settings_field( 
				$args['hook_post_type'] .'_for_'. $archive_post_type, 
				sprintf( __( 'Archives "%s"', 'tify' ), $label ), 
				array( $this, 'admin_option_render' ), 'reading', 
				'tify_hook_for_archive_reading_section', 
				array( 'archive_post_type' => $archive_post_type, 'hook_post_type' => $args['hook_post_type'] )  
			);				
		endforeach;		
	}	
	
	/** == Barre d'administration == */
	function wp_admin_bar_menu( $wp_admin_bar ){
		// Bypass
		if( is_admin() )
			return;
		if( ! is_post_type_archive() )
			return;
		if( ! $hook_id = get_query_var( 'tify_hook_id') )
			return;
		
		$post_type_object = get_post_type_object( get_post_type( $hook_id ) );
		
		// Ajout d'un lien de configuration du Diaporama
		$wp_admin_bar->add_node(
			array(
				'id' 	=> 'edit',
	    		'title' => $post_type_object->labels->edit_item,
	    		'href' 	=> get_edit_post_link( $hook_id )
			)
		);
	}
	
	/** == Affichage de l'écran courant == **/
	function wp_current_screen( $current_screen ){
		foreach( $this->hooks as $archive_post_type => $args ) :
			// Bypass
			if( $current_screen->id !== $args['hook_post_type'] )
				continue;
			$label = get_post_type_object( $archive_post_type )->label;
			
			if ( isset( $_GET['post'] ) )
				$post_id = (int) $_GET['post'];
			elseif ( isset( $_POST['post_ID'] ) )
				$post_id = (int) $_POST['post_ID'];
			else
				$post_id = 0;
			
			if( $post_id !== $args['hook_id'] )
				continue;
					
			$admin_notice = function () use( $label ) {
				echo 	"<div class=\"notice notice-warning inline\">\n".
						"\t<p>Vous êtes en train de modifier la page qui affiche vos \"{$label}\".</p>\n".
						"</div>";
			};
			add_action( 'edit_form_after_title', $admin_notice, 99 );
			
			if( ! empty( $args['hook_post_type_supports'] ) ) :
				global $_wp_post_type_features;
				foreach( $_wp_post_type_features[$args['hook_post_type']] as $feature => $features )
					if( isset( $args['hook_post_type_supports'][$feature] ) )
						if( $args['hook_post_type_supports'][$feature] )
							add_post_type_support( $args['hook_post_type'], $feature );
						else
							remove_post_type_support( $args['hook_post_type'], $feature );
			endif;
		endforeach;
	}
		
	/** == Modification du menu de navigation == **/
	function wp_nav_menu_objects( $sorted_menu_items, $args ){
		if( ! $hook_id = get_query_var( 'tify_hook_id' ) )
			return $sorted_menu_items;
		
		foreach( $sorted_menu_items as &$item ) :
			if( $item->object_id == $hook_id ) :
				$item->classes[] = 'current-menu-item';
				$item->classes[] = 'current_page_item';		
			elseif( ! empty( $this->hooks[get_post_type( $hook_id )]['hook_id'] ) && ( $item->object_id == $this->hooks[get_post_type( $hook_id )]['hook_id'] ) ) :
				$item->classes[] = 'current-menu-ancestor';
				$item->classes[] = 'current-menu-parent';
				$item->classes[] = 'current_page_parent';
				$item->classes[] = 'current_page_ancestor';
			endif;
		endforeach;	
		
		return $sorted_menu_items;
	}	
	
	/** == Titre de la page des archives == **/
	function wp_post_type_archive_title( $archive_title, $archive_post_type ){
		//Bypass
		if( ! $hook_id = get_query_var( 'tify_hook_id' ) )
			return $archive_title;
		
		return get_the_title( $hook_id );
	}
		
	/* = VUES = */
	/** == Administration des options == **/
	function admin_option_render( $args = array( ) ){
		extract( $args );

		wp_dropdown_pages( 
			array( 
				'name' 				=> $hook_post_type .'_for_'. $archive_post_type, 
				'post_type' 		=> $hook_post_type, 
				'selected' 			=> $this->hooks[$archive_post_type]['hook_id'], 
				'show_option_none' 	=> __( 'Aucune page choisie', 'tify' ), 
				'sort_column'  		=> 'menu_order' 
			) 
		);
	}	
	
	/** == Deboguage == **/
	function debug(){
		global $wp_rewrite;

		//var_dump( $wp_rewrite );
		//var_dump( get_option( 'rewrite_rules') );
		exit;
	}	
}
global $tify_h4a;
$tify_h4a = new tiFy_HookForArchive_Master;


class tiFy_HookForArchive_Post{
	/* = ARGUMENTS = */
	public $hooks = array();
	
	/* = CONSTRUCTEUR = */
	function __construct(){	}
	
	/* = CONTROLEUR = */
	/** == Récupération des types d'archives == **/
	function get_hooks(){
		return $this->hooks;
	}
	
	/** == Récupération des types d'archives == **/
	function get_archive_post_types(){
		return array_keys( $this->hooks );
	}	
	
	/** == Récupération du type de post d'accroche selon un type de post d'archive == **/
	function get_hook_post_type( $archive_post_type ){
		if( isset( $this->hooks[ $archive_post_type ]['hook_post_type'] ) )
			return $this->hooks[ $archive_post_type ]['hook_post_type'];
	}
	
	/** == Récupération de l'ID du post d'accroche selon un type de post d'archive == **/
	function get_hook_id( $archive_post_type ){
		if( isset( $this->hooks[ $archive_post_type ]['hook_id'] ) )
			return $this->hooks[ $archive_post_type ]['hook_id'];	
	}
	
	/** == Vérifie si un post est un post d'accroche == **/
	function is_hook( $post_id = null ){
		if( ! $post_id && is_singular( ) )
			$post_id = get_the_ID();
		if( ! $post_id )
			return false;
		if( ! $post = get_post( $post_id ) )
			return false;
		
		foreach( $this->hooks as $archive_post_type => $args )
			if( $this->get_hook_id( $archive_post_type ) == $post->ID )
				return true;
	}
	
	/** == Vérifie si un post est une lié à un post d'accroche == **/
	function is_archive_post( $post_id = null ){
		return ( $this->get_archive_post_hook_id( $post_id ) ) ? true : false;	
	}
	
	/** == Récupération de l'ID du post d'accroche pour un post d'archive == **/
	function get_archive_post_hook_id( $post_id = null ){
		if( ! $post_id && is_singular( ) )
			$post_id = get_the_ID();
		if( ! $post_id )
			return false;
		
		if( $post = get_post( $post_id ) )	
			return $this->get_hook_id( $post->post_type );	
	}
	
	/** == Récupération de l'ID du post d'accroche pour un post d'archive == **/
	function get_archive_post_hook_child_id( $post_id = null, $single = true ){
		if( ! $post_id && is_singular( ) )
			$post_id = get_the_ID();
		if( ! $post_id )
			return false;
		if( ! $post = get_post( $post_id ) )
			return;	
		
		$archive_post_type = $post->post_type;
		$hook_post_type = $this->get_hook_post_type( $archive_post_type );	
		
		if( ! $ids =  get_post_meta( $post->ID, '_'. $hook_post_type .'_for_'. $archive_post_type ) )
			return;	
		
		if( $single )
			return current( $ids );
		else
			return $ids;
	}
			
	/** == Récupération de la structure des permaliens == **/
	function get_permalink_structure( $archive_post_type, $hook_post_type, $hook_id ){
		$permalink_structure = array();
		
		// Récupération des parents du post d'accroche
		if( $ancestors = get_ancestors( $hook_id, $hook_post_type ) ) :
			$ancestors = array_reverse( $ancestors );
			foreach( $ancestors as $post_id ) : 
				$post = get_post( $post_id );
				$permalink_structure[] = array( 'permalink' => get_permalink( $post->ID ), 'name' => $post->post_name, 'title' => $post->post_title );
			endforeach;
		endif;
		
		// Interrruption
		if( ! $post = get_post( $hook_id ) )
			return;
		
		// Recursivité des sous éléments
		if( ( $parent_hook_post_type = $this->get_hook_post_type( $post->post_type ) ) && ( $parent_hook_id = $this->get_hook_id( $post->post_type ) ) )
			$permalink_structure = array_merge( $this->get_permalink_structure( $post->post_type, $parent_hook_post_type, $parent_hook_id ), $permalink_structure );
		
		$permalink_structure[] = array( 'permalink' => get_permalink( $post->ID ), 'name' => $post->post_name, 'title' => $post->post_title );

		return $permalink_structure;
	}

	/** == Chemin vers les archives == **/
	function get_archive_slug( $archive_post_type, $hook_post_type, $hook_id ){
		if( ! $permalink_structure = $this->get_permalink_structure( $archive_post_type, $hook_post_type, $hook_id ) )
			return;
		
		$archive_slug = "";
		foreach( $permalink_structure as $permalink )
			$archive_slug .= $permalink['name']. '/';
	
		$archive_slug = untrailingslashit( $archive_slug );	
		
		return $archive_slug;	
	}	
}

/* = HELPERS = */
/** == Déclaration d'un type de post d'accroche pour des archives == **/ 
function tify_h4a_register( $hooks = array() ){
	global $tify_h4a;
	
	return $tify_h4a->register( $hooks );
}

/** == Récupération du hook_id selon un type d'archive == **/
function tify_h4a_hook_id_by_archive( $archive_post_type ){
	global $tify_h4a;
	
	if( isset( $tify_h4a->hooks[$archive_post_type] ) )
		return $tify_h4a->hooks[$archive_post_type]['hook_id'];
}

/** == Récupération d'un type d'archive selon son hook_id == **/
function tify_h4a_archive_by_hook_id( $hook_id ){
	global $tify_h4a;

	if( isset( $tify_h4a->hook_ids[$hook_id] ) )
		return $tify_h4a->hook_ids[$hook_id];
}