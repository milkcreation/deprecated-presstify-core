<?php
/**
 * Modification des requêtes
 */
function mkcrm_pre_get_posts( $query ){
	if( $query->is_post_type_archive( 'mkcrm_hosting' ) && !isset( $_REQUEST['orderby'] ) ):			
		$query->set( 'orderby', 'title' );
		$query->set( 'order', 'ASC' );
	endif;
	
	return $query;
}
add_action( 'pre_get_posts', 'mkcrm_pre_get_posts' );