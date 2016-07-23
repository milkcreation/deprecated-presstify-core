<?php
/**
 * Metaboxe de saisie
 */ 
function mkpbx_date_range_render( $post, $args = array()){
	// Bypass	
	if( ! $post = get_post( $post) )
		return;
	
	$args = wp_parse_args( $args, array(
			'name' => 'mkpbx_postbox',
			'time' => false,
		)
	);
	extract($args );
	
	$start = array(
		'selected' => get_post_meta( $post->ID, '_start_datetime', true ),
		'time' => $time, 
		'name' => $name.'[single][start_datetime]',
	);
	$end = array(
		'period' => 'end',
		'selected' =>  get_post_meta( $post->ID, '_end_datetime', true ),
		'time' => $time,
		'name' => $name.'[single][end_datetime]',
	);
	
?>	
	<div class="date_range-postbox">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label>
							<?php _e('date de début', 'mktzr_postbox' );?>							
						</label>
					</th>
					<td><?php mk_touch_time( $start );?></td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label>
							<?php _e('date de fin', 'mktzr_postbox' );?>							
						</label>
					</th>
					<td><?php mk_touch_time( $end );?></td>
				</tr>		
			</tbody>
		</table>			
	</div>				
<?php	
}

/**
 * Nettoyage des metadonnées avant enregistrement
 */
function mkpbx_date_range_sanitize_metadata( $metas ){	
	if( isset( $metas['start_datetime'] ) )
		$metas['start_datetime'] = $metas['start_datetime']['aa'].'-'.$metas['start_datetime']['mm'].'-'.$metas['start_datetime']['jj'].' '.$metas['start_datetime']['hh'].':'.$metas['start_datetime']['mn'].':'.$metas['start_datetime']['ss'];
	if( isset( $metas['end_datetime'] ) )
		$metas['end_datetime'] = $metas['end_datetime']['aa'].'-'.$metas['end_datetime']['mm'].'-'.$metas['end_datetime']['jj'].' '.$metas['end_datetime']['hh'].':'.$metas['end_datetime']['mn'].':'.$metas['end_datetime']['ss'];
	
	if( isset( $metas['start_datetime'] ) && isset( $metas['end_datetime'] ) && (  $metas['start_datetime'] > $metas['end_datetime'] ) )
		$metas['end_datetime'] = $metas['start_datetime'];
	
	return $metas;
}
add_filter( 'mkpbx_sanitize_single_metadata', 'mkpbx_date_range_sanitize_metadata' );

/**
 * Affiche ou retrouve la date du contenu courant
 */
function the_start_date_range( $d = '', $before = '', $after = '', $echo = true ) {
	$the_date = '';
	$the_date .= $before;
	$the_date .= get_the_date_range( $d, 'start' );
	$the_date .= $after;

	$the_date = apply_filters('the_start_date_range', $the_date, $d, $before, $after);

	if ( $echo )
		echo $the_date;
	else
		return $the_date;

	return null;
}

/**
 * Affiche ou retrouve la date du contenu courant
 */
function the_end_date_range( $d = '', $before = '', $after = '', $echo = true ) {
	$the_date = '';
	$the_date .= $before;
	$the_date .= get_the_date_range( $d, 'end' );
	$the_date .= $after;

	$the_date = apply_filters( 'the_end_date_range', $the_date, $d, $before, $after );

	if ( $echo )
		echo $the_date;
	else
		return $the_date;

	return null;
}

/**
 * Retrouve la date du contenu courant
 */
function get_the_date_range( $d = '', $prefix ) {
	$post = get_post();
	$the_date = '';

	if ( '' == $d )
		$the_date .= mysql2date(get_option('date_format'), get_post_meta( $post->ID, "_{$prefix}_datetime", true ) );
	else
		$the_date .= mysql2date( $d, get_post_meta( $post->ID, "_{$prefix}_datetime", true ) );

	return apply_filters( 'get_the_date_range', $the_date, $d, $prefix );
}