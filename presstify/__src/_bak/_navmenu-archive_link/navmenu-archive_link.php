<?php
/**
 * Lien de menu vers la pages des archives
 * 
 * @package WordPress
 * @subpackage Milkcreation Thematizer
 */

/**
 * Types de post pour lequels activé le lien de menu vers la page des archives
 */
function mktzr_nav_menu_items_post_type(){
	$post_types = apply_filters( 'mktzr_nav_menu_items_post_type', array() );
	
	foreach( $post_types as $post_type )
		add_filter( 'nav_menu_items_'.$post_type,  'mktzr_nav_menu_items', null, 3 );
}
add_action( 'admin_init', 'mktzr_nav_menu_items_post_type' );

/**
 * Liens vers la page des archives
 */
function mktzr_nav_menu_items( $posts, $args, $post_type ){
	array_unshift( $posts, (object) array(
			'_add_to_top' => false,
			'ID' => 0,
			'object_id' => -1,
			'post_content' => '',
			'post_excerpt' => '',
			'post_parent' => '',
			'post_title' => $post_type['args']->labels->all_items,
			'post_type' => 'nav_menu_item',
			'type' => 'custom',
			'url' => get_post_type_archive_link( $post_type['args']->name )
		) );
	return $posts;
}