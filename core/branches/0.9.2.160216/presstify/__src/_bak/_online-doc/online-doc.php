<?php
/**
 * TYPE DE POST DOCUMENTATION EN LIGNE
 *
 * @package WordPress
 * @subpackage Milkcreation Thematizer
 */

/**
 * Type de post personnalisé
 */
function mktzr_doc_register_post_type(){
	register_post_type( 'mktzr_onlinedoc', array(
		'labels' => array(
			'name'			 	=> __( 'Documentation en ligne', 'mktzr' ),
			'singular_name' 	=> __( 'Documentation en ligne', 'mktzr' ),			
			'add_new'			=> __( 'Ajout d\'une page documentation', 'mktzr' ),
			'all_items' 		=> __( 'Toutes les pages documentation', 'mktzr' ),
			'add_new_item'		=> __( 'Ajouter une page documentation', 'mktzr' ),
			'edit_item'			=> __( 'Editer la page documentation', 'mktzr' ),
			'new_item'			=> __( 'Nouvelle page documentation', 'mktzr' ),
		 	'view_item'			=> __( 'Voir la page documentation', 'mktzr' ),
			'search_items'		=> __( 'Recherche une page documentation', 'mktzr' ),
			'not_found'			=> __( 'Aucune page documentation trouvée', 'mktzr' ),		
			'not_found_in_trash'=> __( 'Aucune page documentation dans la corbeille', 'mktzr' ),
			'menu_name' 		=> __( 'Documentation en ligne', 'add new on admin bar', 'mktzr' ),				
		),
		'description'			=> __( 'Toutes les pages de documentation en ligne', 'mktzr' ),
		'public'				=> true,
		'exclude_from_search'	=> true,
		'publicly_queryable' 	=> true,
    	'show_ui' 				=> true,
    	'show_in_nav_menus' 	=> false,
    	'show_in_menu' 			=> true,		
		'show_in_admin_bar' 	=> false,
		'menu_position'			=> 999,
		'menu_icon'				=> MKTZR_URL.'/images/books-16x16.png',
		'capability_type' 		=> 'page',
		//'capabilities'		=> array(),
		'map_meta_cap' 			=> true,
		'hierarchical' 			=> false,
		'supports' 				=> array( 'title', 'thumbnail', 'editor' ),
    	'register_meta_box_cb'	=> '',
    	'taxonomies'			=> array(),
    	'has_archive' 			=> false,
    	'permalink_epmask'		=> EP_PERMALINK,
    	'rewrite' 				=> array( 'slug'=> __( 'site-online-documentation', 'mktzr' ), 'with_front'=> false, 'feeds' => true, 'pages' => true ),			
	   	'query_var' 			=> true,
    	'can_export'			=> true,		
	));	 
}
add_action('init', 'mktzr_doc_register_post_type');