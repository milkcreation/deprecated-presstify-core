<?php
/**
 * -------------------------------------------------------------------------------
 *	Meta Boxes
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
function mkforums_forum_attachment_add_meta_boxes( $post_type, $post = array() ){
	if( $post_type == 'mktopics' ) 	
		add_meta_box('div_mkforums_select_forum_attachment', __( 'Select forum attachment', 'milk-forums' ), 'mkforums_select_forum_attachment_meta_box', $post_type, 'side', 'default' );	
}
add_action( 'add_meta_boxes', 'mkforums_forum_attachment_add_meta_boxes', '', 2); 
  
/**
 * Metaboxe de saisie des détails d'un événement
 */ 
function mkforums_select_forum_attachment_meta_box( $post, $box ){
	if( ! $post = &get_post( $post) )
		return;	
	$name = '_mkforums_forum_attachment_id';
	$selected = get_post_meta( $post->ID, '_mkforums_forum_attachment_id', true );
	$show_option_none = __('No Forum', 'milk-forums');
	
	$dropdown_args = array( 'name', 'selected', 'show_option_none' );
	$args = compact( $dropdown_args );
	
	mkforums_dropdown_forums( $args );		
}

/**
 * Sauvegarde des details de l'événement
 * @param int $post_id
 */
function mkforums_forum_attachment_save_post( $post_id, $post ) {
		// Contrôle s'il s'agit d'une routine de sauvegarde automatique.	
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

	//Bypass
	if( !isset($_POST['post_type']) )
		return;
		
	// Contrôle des permissions
  	if ( 'page' == $_POST['post_type'] )
    	if ( !current_user_can( 'edit_page', $post_id ) )
        	return;
  	else
    	if ( !current_user_can( 'edit_post', $post_id ) )
        	return;
			
	if( ( ! $post = &get_post( $post_id ) ) || ( ! $post = &get_post( $post ) ) )
		return;		

	if( $post->post_type != 'mktopics' )
		return;
		 
	$data = ( isset( $_POST['_mkforums_forum_attachment_id'] ) ) ? $_POST['_mkforums_forum_attachment_id'] : 0;

	if ( empty( $data ) )
		delete_post_meta( $post->ID, '_mkforums_forum_attachment_id' );
	elseif( get_post_meta( $post->ID, '_mkforums_forum_attachment_id', true ) == '' )
		add_post_meta( $post->ID, '_mkforums_forum_attachment_id', $data, true );
	elseif ( !empty( $data ) && $data != get_post_meta( $post->ID, '_mkforums_forum_attachment_id', true ) )
		update_post_meta( $post->ID, '_mkforums_forum_attachment_id', $data );	
}
add_action( 'save_post', 'mkforums_forum_attachment_save_post', '', 2 );