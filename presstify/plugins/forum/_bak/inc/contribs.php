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
 * 
 */
function mkforums_pre_get_comments( &$wpcomment ){
	$args = array();	
	if( !empty( $_REQUEST['post_type']) && $_REQUEST['post_type']==='mktopics' )	
		$args['post_type'] = 'mktopics';

	$wpcomment->query_vars = wp_parse_args( $args, $wpcomment->query_vars );
}
add_action( 'pre_get_comments', 'mkforums_pre_get_comments' );

/**
 * 
 */
function mkforums_post_contrib_handle(){
	if( get_query_var('mkforums_post_contrib') )
		exit;
}
add_action( 'init', 'mkforums_post_contrib_handle');