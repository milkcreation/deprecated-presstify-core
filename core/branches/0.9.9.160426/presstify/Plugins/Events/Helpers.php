<?php
/* = HELPERS = */
/** == Récupérère les dates d'événement d'un post == **/
function tify_events_get_the_dates( $post = 0, $query_args = array() )
{
	return \tiFy\Plugins\Events\Query::PostGetDates( $post, $query_args = array() );
}

/** == Affichage d'une date == **/
function tify_events_date_display( $date, $echo = true )
{
	return \tiFy\Plugins\Events\GeneralTemplate::DateRender( $date, $echo );
}

/** == Récupération d'une metadonnée d'événement == **/
function tify_events_get_meta( $event_id, $meta_key = null, $single = false )
{
	return get_metadata( 'tify_events', $event_id, $meta_key, $single );
}

/** == Récupération de l'événement voisin == **/
function tify_events_get_adjacent( $previous = true )
{
	global $wpdb, $tify_events;
	
	if ( ! $post = get_post() )
		return null;
	
	$adjacent = $previous ? 'previous' : 'next';
	$op = $previous ? '<' : '>';
	$diffop = $previous ? '>' : '<';
	$order = $previous ? 'DESC' : 'ASC';
	
	$join 	= "INNER JOIN {$tify_events->db->wpdb_table} as tify_events ON ( p.ID = tify_events.event_post_id )";
	
	$where 	= "WHERE 1";
	$where 	.= " AND p.ID != {$post->ID}";
	$where 	.= " AND p.post_type = '{$post->post_type}'";
	$where 	.= " AND p.post_status = 'publish'";
	$where 	.= " AND tify_events.event_start_datetime {$op} '{$post->event_start_datetime}'";
	if( $previous ) :
	else:
		$where 	.= " AND p.ID NOT IN (SELECT diff_tify_events.event_post_id FROM {$tify_events->db->wpdb_table} AS diff_tify_events WHERE diff_tify_events.event_start_datetime {$diffop} '{$post->event_start_datetime}' )";
	endif;
	
	$sort 	= "ORDER BY tify_events.event_start_datetime {$order} LIMIT 1";
	
	$query = "SELECT DISTINCT p.ID FROM $wpdb->posts AS p $join $where $sort";
	
	$query_key = 'tify_events_adjacent_' . md5( $query );
	$result = wp_cache_get( $query_key, 'counts' );
	if ( false !== $result ) {
		if ( $result )
			$result = get_post( $result );
			return $result;
	}
	
	$result = $wpdb->get_var( $query );
	if ( null === $result )
		$result = '';
	
	wp_cache_set( $query_key, $result, 'counts' );

	if ( $result )
		$result = get_post( $result );

	return $result;
}

/** == Calendrier == **/
function tify_events_calendar( $date = null, $echo = true )
{
	return \tiFy\Plugins\Events\GeneralTemplate::Calendar( $date, $echo );
}