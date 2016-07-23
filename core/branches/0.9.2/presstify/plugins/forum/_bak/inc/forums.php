<?php
/**
 * -------------------------------------------------------------------------------
 *	Forums
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
 * Type de post personnalisé des Forums
 */
function mkforums_register_forums_post_type(){
	register_post_type( 'mkforums', array(
		'labels' => array(
			'name'			 	=> __( 'Forums', 'milk-forums' ),
			'singular_name' 	=> __( 'Forum', 'milk-forums' ),			
			'add_new'			=> __( 'Add forum', 'milk-forums' ),
			'all_items' 		=> __( 'All forums', 'milk-forums' ),
			'add_new_item'		=> __( 'Add a new forum', 'milk-forums' ),
			'edit_item'			=> __( 'Edit forum', 'milk-forums' ),
			'new_item'			=> __( 'New forum', 'milk-forums' ),
		 	'view_item'			=> __( 'Display forum', 'milk-forums' ),
			'search_items'		=> __( 'Search forum', 'milk-forums' ),
			'not_found'			=> __( 'No forum found', 'milk-forums' ),		
			'not_found_in_trash'=> __( 'No forum in trash', 'milk-forums' ),
			'parent_item_colon'	=> __( 'Parent', 'milk-forums' ),
			'menu_name' 		=> __( 'Forums', 'add new on admin bar', 'milk-forums' ),				
		),
		'description'			=> __( 'Forums based on Wordpress Posts', 'milk-forums' ),
		'public'				=> true,
		'exclude_from_search'	=> false,
		'publicly_queryable' 	=> true,
    	'show_ui' 				=> true,
    	'show_in_nav_menus' 	=> true,
    	'show_in_menu' 			=> true,		
		'show_in_admin_bar' 	=> true,
		'menu_position'			=> null,
		'menu_icon'				=> MKFORUMS_URL.'/images/cup-16x16.png',
		'capability_type' 		=> 'page',
		//'capabilities'			=> array(),
		'map_meta_cap' 			=> true,
		'hierarchical' 			=> false,
		'supports' 				=> array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
    	'register_meta_box_cb'	=> '',
    	'taxonomies'			=> array(),
    	'has_archive' 			=> true,
    	'permalink_epmask'		=> EP_PERMALINK,
    	//'rewrite' 				=> array( 'slug'=> __( 'mkforums', 'milk-forums' ), 'with_front'=> false ),			
	   	'query_var' 			=> true,
    	'can_export'			=> true,		
	));	
	return; 
}
add_action('init', 'mkforums_register_forums_post_type');

/**
 * NAVMENU
 */
/**
 * Liens vers la page des archives
 */
function mkforums_forum_nav_menu_items( $posts, $args, $post_type ){
	array_unshift( $posts, (object) array(
			'_add_to_top' => false,
			'ID' => 0,
			'object_id' => -1,
			'post_content' => '',
			'post_excerpt' => '',
			'post_parent' => '',
			'post_title' => __( 'All forums', 'milk-forums' ),
			'post_type' => 'nav_menu_item',
			'type' => 'custom',
			'url' => get_post_type_archive_link( 'mkforums' )
		) );
	return $posts;
}
add_filter( 'nav_menu_items_mkforums',  'mkforums_forum_nav_menu_items', 10, 3 );


/**
 * Retourne ou affiche la liste des forums dans une liste de selection
 */
function mkforums_dropdown_forums( $args = array() ){
	$defaults = array(
		'depth' => 0, 'child_of' => 0,
		'selected' => 0, 'echo' => 1,
		'name' => 'mkforum_id', 'id' => '',
		'show_option_none' => '', 'show_option_no_change' => '',
		'option_none_value' => ''
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$output = '';
	$name = esc_attr($name);
	// Retro-compatibilité avec les anciens système ou l'id et le name été identiques
	if ( empty($id) )
		$id = $name;
	
	$query_args = array( 
		'post_type' => 'mkforums',
		'posts_per_page' => -1,
		'orderby' => 'menu_order title',
		'order' => 'ASC'
	);
	$query_forums = new WP_Query;
	$forums = $query_forums->query( $query_args );	
	
	$output = "<select name=\"$name\" id=\"$id\">\n";
	if ( $show_option_no_change )
		$output .= "\t<option value=\"-1\">$show_option_no_change</option>";
	if ( $show_option_none )
		$output .= "\t<option value=\"" . esc_attr($option_none_value) . "\">$show_option_none</option>\n";
	if( $query_forums->post_count )
		$output .= mkforums_forums_dropdown_tree($forums, $depth, $r);
	$output .= "</select>\n";
	
	wp_reset_postdata();
	
	$output = apply_filters('mkforums_dropdown_forums', $output);

	if ( $echo )
		echo $output;

	return $output;
}

/**
 * Retourne la liste de selection HTML des forums.
 */
function mkforums_forums_dropdown_tree() {
	$args = func_get_args();
	if ( empty($args[2]['walker']) ) // the user's options are the third parameter
		$walker = new mkforums_Walker_ForumDropdown;
	else
		$walker = $args[2]['walker'];

	return call_user_func_array(array(&$walker, 'walk'), $args);
}

/**
 * Création de la liste de selection HTML des forums.
 */
class mkforums_Walker_ForumDropdown extends Walker {
	/**
	 * @see Walker::$tree_type
	 */
	var $tree_type = 'mkforums';

	/**
	 * @see Walker::$db_fields
	 */
	var $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');

	/**
	 * @see Walker::start_el()
	 */
	function start_el(&$output, $forum, $depth, $args) {
		$pad = str_repeat('&nbsp;', $depth * 3);

		$output .= "\t<option class=\"level-$depth\" value=\"$forum->ID\"";
		$output .= selected( $forum->ID, $args['selected'], false );
		$output .= '>';
		$title = $forum->post_title;
		$output .= $pad . esc_html( $title );
		$output .= "</option>\n";
	}
}