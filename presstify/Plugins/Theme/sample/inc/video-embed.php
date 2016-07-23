<?php
/**
 * Récupération du code html d'une vidéo 
 */
function mk_get_html_video_embed( $url, $args=array() ){
	$defaults = array( 
		'width' => 840,
		'poster' => '',
	);
	
	if( ! parse_url( $url ) )
		return new WP_Error( 'get_video_embed', __('Format de vidéo invalide', 'adtc' ) );

	$args = wp_parse_args( $args, $defaults );
	
	if( ( $ext = pathinfo( $url, PATHINFO_EXTENSION ) ) && in_array( $ext, array( 'avi', 'flv',  'mov', 'mp4', 'ogg', 'ogv', 'webm' ) ) ) :
		$filetype = wp_check_filetype( $url );
		$output = '<video id="projekktor" title="this is Projekktor" width="'.$args['width'].'" height="500"';
		if( $args['poster'] )
			$output .= ' poster="'.$args['poster'].'"';
		$output .= ' controls><source src="'.$url.'" type="'.$filetype['type'].'" /></video>';
		
		return array( 'html' =>$output, 'player' => true );
	elseif( preg_match('/<iframe.*src=\"(.*)\".*><\/iframe>/isU', $url, $matches ) ) :
		return array( 'html' => $matches[0] );
	else :
		require_once( ABSPATH.'wp-includes/class-oembed.php' );
		$oembed = new WP_oEmbed();
		return array( 'html' => $oembed->get_html( $url, $args ) );
	endif;	
		
	return new WP_Error( 'get_video_embed', __('Format de vidéo invalide', 'adtc' ) );
}

/**
 * Récupération via Ajax du code html d'une vidéo 
 */
function mk_ajax_get_video_embed( ){
	$reponse = mk_get_html_video_embed( $_POST['url'], $_POST['args'] );	
	if( is_wp_error( $reponse ) )	
		echo json_encode( array( 'html' => '<h3 class="error">'.$reponse->get_error_message().'</h3>' ) );
	else
		echo json_encode( $reponse ); 
	exit;
}
add_action( 'wp_ajax_get_video_embed', 'mk_ajax_get_video_embed' );
add_action( 'wp_ajax_nopriv_get_video_embed', 'mk_ajax_get_video_embed' );