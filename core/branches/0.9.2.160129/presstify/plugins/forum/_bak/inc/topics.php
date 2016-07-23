<?php
/**
 * -------------------------------------------------------------------------------
 *	Topics
 * -------------------------------------------------------------------------------
 *
 * @name 		Milkcreation Forums
 * @package    	G!PRESS SNCF PROXIMITES - Espaces Collaboratifs
 * @copyright 	Milkcreation 2012
 * @link 		http://g-press.com/plugins/mocca
 * @author 		Jordy Manner
 * @version 	1.1
 */

/**
 * Type de post personnalisé des Topics (Sujet de forum)
 */
function mkforums_register_topics_post_type(){
	register_post_type( 'mktopics', array(
		'labels' => array(
			'name'			 	=> __( 'Topics', 'milk-forums' ),
			'name_admin_bar' 	=> __( 'Topic', 'add new on admin bar', 'milk-forums' ),
			'singular_name' 	=> __( 'Topic', 'milk-forums' ),
			'add_new'			=> __( 'Add topic', 'milk-forums' ),
			'all_items' 		=> __( 'All topics', 'milk-forums' ),
			'add_new_item'		=> __( 'Add a new topic', 'milk-forums' ),
			'edit_item'			=> __( 'Edit topic', 'milk-forums' ),
			'new_item'			=> __( 'New topic', 'milk-forums' ),
		 	'view_item'			=> __( 'Display topic', 'milk-forums' ),
			'search_items'		=> __( 'Search topic', 'milk-forums' ),
			'not_found'			=> __( 'No topic found', 'milk-forums' ),		
			'not_found_in_trash'=> __( 'No topic in trash', 'milk-forums' )				
		),
		'description'			=> __( 'Wordpress Forums based on posts', 'milk-forums' ),
		'public'				=> true,
		'exclude_from_search'	=> false,
		'publicly_queryable' 	=> true,
    	'show_ui' 				=> true,
    	'show_in_nav_menus' 	=> true,
    	'show_in_menu' 			=> false,		
		'show_in_admin_bar' 	=> true,
		'menu_position'			=> null,
		'menu_icon'				=> MKFORUMS_URL.'/images/cookies-16x16.png',
    	'capability_type' 		=> 'page',
		//'capabilities'			=> array(),
		'map_meta_cap' 			=> true,
		'hierarchical' 			=> false,
		'supports' 				=> array( 'title', 'editor', 'thumbnail', 'page-attributes', 'comments' ),
    	'register_meta_box_cb'	=> '',
    	'taxonomies'			=> array(),
    	'has_archive' 			=> false,
    	'permalink_epmask'		=> EP_PERMALINK,
    	'rewrite' 				=> array( 'slug'=> __( 'mktopics', 'milk-forums' ), 'with_front'=> false ),			
	   	'query_var' 			=> true,
    	'can_export'			=> true		
	));	
				
	return; 
}
add_action('init', 'mkforums_register_topics_post_type');

/**
 * Taxonomies personnalisées des Topics
 */
function mkforums_topics_custom_taxonomies(){
	register_taxonomy( 'mktopics-cats', array( 'mocca-topics' ), array(
		'labels' => array(
			'name'			=> __( 'Topic\'s categories', 'milk-forums' ), 
			'singular_name' => __( 'Topic\'s category', 'milk-forums' ),
			'search_items' 	=> __( 'Search topic\'s category', 'milk-forums' ),
			'popular_items' => __( 'Most popular topic\'s categories', 'milk-forums' ),
			'all_items' 	=> __( 'All topic\'s categories', 'milk-forums' ),
			'parent_item' => __( 'Parent topic\'s category', 'milk-forums' ),
			'parent_item_colon' => null,
			'edit_item' 	=> __( 'Edit topic\'s category', 'milk-forums' ),
			'update_item' => __( 'Update topic\'s category' ),
			'add_new_item' 	=> __( 'Add topic\'s category', 'milk-forums' ),				
			'new_item_name' 		=> __( 'New topic\'s category', 'milk-forums' ),
			'separate_items_with_commas' => null,
			'add_or_remove_items' => null,
			'choose_from_most_used' => null,
			'menu_name' => null				
		),
		'public' => true,
		'show_in_nav_menus'=>false,
		'show_ui' =>true,
		'show_tagcloud' => false,
		'hierarchical' => true,		
		'update_count_callback' => '',
		'rewrite' => array( 'slug'=>__( 'topic-category', 'milk-forums' ), 'with_front'=> false )	
	));
	
	register_taxonomy( 'mktopics-tags', array( 'mocca-topics' ), array(
		'labels' => array(
			'name'			=> __( 'Topic\'s tags', 'milk-forums' ), 
			'singular_name' => __( 'Topic\'s tag', 'milk-forums' ),
			'search_items' 	=> __( 'Search topic\'s tag', 'milk-forums' ),
			'popular_items' => __( 'Most popular topic\'s tags', 'milk-forums' ),
			'all_items' 	=> __( 'All topic\'s tags', 'milk-forums' ),
			'parent_item' => __( 'Parent topic\'s tag', 'milk-forums' ),
			'parent_item_colon' => null,
			'edit_item' 	=> __( 'Edit topic\'s tag', 'milk-forums' ),
			'update_item' => __( 'Update topic\'s tag' ),
			'add_new_item' 	=> __( 'Add topic\'s tag', 'milk-forums' ),				
			'new_item_name' 		=> __( 'New topic\'s tag', 'milk-forums' ),
			'separate_items_with_commas' => null,
			'add_or_remove_items' => null,
			'choose_from_most_used' => null,
			'menu_name' => null				
		),
		'public' => true,
		'show_in_nav_menus'=>false,
		'show_ui' => true,
		'show_tagcloud' => true,
		'hierarchical' => false,		
		'update_count_callback' => '',
		'rewrite' => array( 'slug'=>__( 'topic-tag', 'milk-forums' ), 'with_front'=> false )	
	));	
}
add_action( 'init', 'mkforums_topics_custom_taxonomies' );

/**
 * Récupération des sujets liés à un forum
 */ 
function mkforums_get_topics_for_forum( $args = array() ){
	global $post;
		
	$defaults = array(
		'attached_forum' => 0,
		'orderby' => 'menu_order',
		'order' => 'ASC'
	);
	$query_args = wp_parse_args( $args, $defaults );
	
	if(	empty( $query_args['attached_forum'] ) && isset( $post->post_type ) && ( $post->post_type == 'mkforums' )  )
		$query_args['attached_forum'] = $post->ID;

	// Arguments obligatoires
	$query_args['post_type'] = 'mktopics';
	$query_args['meta_key'] = '_mkforums_forum_attachment_id';
	$query_args['meta_value'] = $query_args['attached_forum'];
	
	$query_topics = new WP_Query;
	$topics = $query_topics->query( $query_args );
	
	if( $query_topics->post_count )
		return $topics;	
} 	