<?php
/**
 * --------------------------------------------------------------------------------
 *	Navigation Menu
 * --------------------------------------------------------------------------------
 * 
 * @name 		Milkcreation Forums
 * @package    	G!PRESS SNCF PROXIMITES - Espaces Collaboratifs
 * @copyright 	Milkcreation 2012
 * @link 		http://g-press.com/plugins/mocca
 * @author 		Jordy Manner
 * @version 	1.1
 */

/**
 * Liens vers la homepage des formulaires dans la selection des formulaires
 */
function mkforums_topics_nav_menu_items( $posts, $args, $post_type ){
	array_unshift( $posts, (object) array(
						'_add_to_top' => false,
						'ID' => 0,
						'object_id' => -1,
						'post_content' => '',
						'post_excerpt' => '',
						'post_parent' => '',
						'post_title' => __( 'Topics archives', 'milk-forums' ),
						'post_type' => 'nav_menu_item',
						'type' => 'custom',
						'url' => add_query_arg( 'post_type', 'mktopics', home_url() )
					) );
	return $posts;
}
add_filter( 'nav_menu_items_mktopics',  'mkforums_topics_nav_menu_items', false, 3 );