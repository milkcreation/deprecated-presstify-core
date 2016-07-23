<?php
/** == Affichage de la timeline == **/
function tify_timeline_display( $args = array(), $echo = true )
{
		
	$query_args = isset( $args['query_args'] ) ? _http_build_query( $args['query_args'] ) : '';
	unset( $args['query_args'] );
	
	$_args = '';
	foreach( (array) $args as $k => $v )
		$_args .= "$k=\"$v\" ";
	
	if( $echo )
		echo do_shortcode( '[tify_timeline query_args="'. $query_args .'" '. $_args .']' );
	else
		return do_shortcode( '[tify_timeline query_args="'. $query_args .'" '. $_args .']' );
}