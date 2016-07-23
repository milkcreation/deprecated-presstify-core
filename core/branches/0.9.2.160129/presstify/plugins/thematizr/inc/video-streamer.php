<?php
/**
 * 
 */
function video_streamer_init(){		
	if( ! is_attachment() )
		return;	
	
	global $posts;
	
	if ( ! empty( $posts ) && isset( $posts[0]->post_mime_type ) )
		$type = explode( '/', $posts[0]->post_mime_type );
	
	if( $type[0] != 'video' )
		return;
	require_once( dirname(__FILE__).'/video.php');
	exit;
}
add_action( 'template_redirect', 'video_streamer_init' );

/**
 * 
 */
function mk_video_stream_html( $args ){
	global $content_width;	
	$default_types = wp_get_video_extensions();	
	$defaults = array(
		'src'      => '',
		'poster'   => '',
		'loop'     => '',
		'autoplay' => '',
		'preload'  => 'metadata',
		'height'   => '100%',
		'width'    => '100%',
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );		
	
	require_once( ABSPATH.'wp-includes/class-oembed.php' );
	$oembed = new WP_oEmbed();
	
	$output = '';
	
	if( ( $type = wp_check_filetype( $src, wp_get_mime_types() ) ) && in_array( $type['ext'], $default_types ) ) :
		$atts = array(
			'class'    => 'milk-video',
			'id'       => 'milk-video',
			'width'    => $width,
			'height'   => $height,
			'poster'   => esc_url( $poster ),
			'loop'     => $loop,
			'autoplay' => $autoplay,
			'preload'  => $preload,
		);	

		foreach ( array( 'poster', 'loop', 'autoplay', 'preload' ) as $a ) {
			if ( empty( $atts[$a] ) )
				unset( $atts[$a] );
		}
	
		$attr_strings = array();
		foreach ( $atts as $k => $v ) {
			$attr_strings[] = $k . '="' . esc_attr( $v ) . '"';
		}
	
		$output = '';
		$output .= sprintf( '<video %s controls="controls">', join( ' ', $attr_strings ) );

		$fileurl = '';
		$source = '<source type="%s" src="%s" />';
		$output .= sprintf( $source, $type['type'], esc_url( $src ) );
		$output .= wp_mediaelement_fallback(  $src );
		$output .= '</video>';
		
	endif;	
	//video responsive
	$output = sprintf( '<div id="player" class="full-frame" style="width: 100%%; height: 100%%; overflow: hidden;">%s</div>', $output );
		
	return $output;	
}