<?php
/**
 * -------------------------------------------------------------------------------
 *	Custom Columns
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
 * Colonne personnalisées des sujets de forums.
 */
function mkforums_custom_columns( $columns ){
	$end = array_slice($columns, 2);	
	array_splice($columns, 2);
	$columns['mktopics_forum'] =  __( 'Attached to Forum', 'milk-forums' );	
	$columns += $end;
	
	return apply_filters( 'mkforums_custom_columns', $columns );
}
add_filter( 'manage_edit-mktopics_columns', 'mkforums_custom_columns' );

/**
 * 
 */
function mkforums_custom_rows( $column, $post_id ){
	if( $column == 'mktopics_forum' )
		if( $forum_parent = get_post_meta( $post_id, '_mkforums_forum_attachment_id', true ) )
			printf( '<a href="%s">%s</a>',
				esc_url( add_query_arg( array( 'post_type' => 'mktopics', 'mkforum_id' => $forum_parent ), 'edit.php' ) ),
				get_the_title($forum_parent)
			);
		else 	
			_e( 'No attached forum', 'milk-forums' );

}
add_action( 'manage_mktopics_posts_custom_column', 'mkforums_custom_rows', '', 2);
	
/**
 * 
 */
function mkforums_column_filters(){
	global $post_type;

	// Bypass
	if( !isset($post_type) || $post_type != 'mktopics' )
		return;
	
	$selected = ! empty( $_REQUEST['mkforum_id'] )? $_REQUEST['mkforum_id'] : 0;

	$show_option_none = __('All Forums', 'milk-forums');
	$echo = false;
	
	$dropdown_args = array( 'selected', 'show_option_none', 'echo' );
	$args = compact( $dropdown_args );
	
	$output = mkforums_dropdown_forums( $args );
	
	echo apply_filters( 'mkforums_column_filters', $output );	
}
add_action( 'restrict_manage_posts', 'mkforums_column_filters'  );

/**
 * Filtrage des sujets par forum
 */
function mkforums_topics_table_filter_columns( $vars ) {
	if ( !empty( $vars['mkforum_id'] ) ) {
		$vars = array_merge( $vars, array(
			'meta_key' => '_mkforums_forum_attachment_id',
			'meta_value' => $vars['mkforum_id'],
			'orderby' => 'meta_value'
		) );
	}
	return $vars;
}
add_filter( 'request', 'mkforums_topics_table_filter_columns' ); 