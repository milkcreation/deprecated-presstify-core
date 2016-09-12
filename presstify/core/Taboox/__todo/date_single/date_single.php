<?php
/**
 * Metaboxe de saisie
 */ 
function mkpbx_single_date_render( $post, $args = array()){
	// Bypass	
	if( ! $post = get_post( $post) )
		return;
	
	$args = wp_parse_args( $args, array(
			'name' => 'datetime',
			'selected' => '0000-00-00 00:00:00'
		)
	);
	extract($args );
	
	if( $saved = get_post_meta( $post->ID, '_'.$name, true ) )
		$selected = $saved;
?>
	<input type="hidden" name="mkpbx_postbox[date_single][names][]" value="<?php echo esc_attr( $name );?>" />
<?php
	mk_touch_time( array( 'name' => 'mkpbx_postbox[single]['.$name.']', 'selected' => $selected ) );	
}

/**
 * Sauvegarde
 */
function mkpbx_single_date_sanitize_metadata( $metas ){
	if( ! isset( $_REQUEST['mkpbx_postbox']['date_single']['names'] ) )
		return $metas;
	foreach( $_REQUEST['mkpbx_postbox']['date_single']['names'] as $name )
		if( isset( $metas[$name] ) )
			$metas[$name] = $metas[$name]['aa'].'-'.$metas[$name]['mm'].'-'.$metas[$name]['jj'].' '.$metas[$name]['hh'].':'.$metas[$name]['mn'].':'.$metas[$name]['ss'];
	
	return $metas;
}
add_filter( 'mkpbx_sanitize_single_metadata', 'mkpbx_single_date_sanitize_metadata' );

/**
 * Affiche ou retrouve la date du contenu courant
 */
function the_single_date( $d = '', $before = '', $after = '', $echo = true, $name = 'datetime' ) {
	$the_date = '';
	$the_date .= $before;
	$the_date .= get_the_single_date( $d, $name );
	$the_date .= $after;

	$the_date = apply_filters('the_single_date', $the_date, $d, $before, $after, $name );

	if ( $echo )
		echo $the_date;
	else
		return $the_date;

	return null;
}

/**
 * Retrouve la date du contenu courant
 */
function get_the_single_date( $d = '', $name = 'datetime' ) {
	$post = get_post();
	$the_date = '';

	if ( '' == $d )
		$the_date .= mysql2date( get_option('date_format'), get_post_meta( $post->ID, '_'.$name, true ) );
	else
		$the_date .= mysql2date( $d, get_post_meta( $post->ID, '_datetime', true ) );

	return apply_filters( 'get_the_single_date', $the_date, $d, $name );
}