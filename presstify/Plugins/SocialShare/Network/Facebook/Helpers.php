<?php
/* = GENERAL TEMPLATE = */
/** == Bouton de partage Facebook == **/
function tify_fb_api_share_button( $args = array() ){
	if( is_singular() )
		$post = get_post();

	$defaults = array(
		'class'				=> '',
		'text'				=> __( 'Partager sur Facebook', 'tify' ),
		'uri'				=> ( is_singular() && ! empty( $post ) ) ? get_permalink( $post->ID ) : home_url(),
		'image'				=> ( is_singular() && ! empty( $post ) && ( $attachment_id = get_post_thumbnail_id( $post->ID ) ) ) ? wp_get_attachment_url( $attachment_id ) : '',
		'callback_attrs'	=> array(),
		'title'				=> ( is_singular() && ! empty( $post ) ) ? $post->post_title : get_bloginfo( 'name' ),
		'desc'				=> ( is_singular() && ! empty( $post ) ) ? $post->post_excerpt : get_bloginfo( 'description' ),
		'echo'				=> true
	);
	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'tify_fb_api_share_button_args', $args, $defaults );
	extract( $args );

	if( $image_src = tiFy\Lib\Utils::get_context_img_src( $image, 1200, 630, true ) ) :
	elseif( $image_src = tiFy\Lib\Utils::get_context_img_src( $image, 600, 315, true ) ) :
	elseif( $image_src = tiFy\Lib\Utils::get_context_img_src( $image, 200, 200, true ) ) :
	endif;

	$output = 	"<a href=\"". esc_url( $uri )."\"".
			" class=\"{$class}\"".
			" data-action=\"tify-fb-api_share_button\"".
			" data-url=\"{$uri}\"".
			" data-title=\"". esc_attr( $title ). "\"".
			" data-desc=\"". esc_attr( $desc ) ."\"".
			" data-image=\"". esc_attr( $image_src ) ."\"".
			" data-callback_attrs=\"". ( htmlentities( json_encode( $callback_attrs ) ) ) ."\"".
			">{$text}</a>";

	if( $echo ) :
		echo $output;
	else :
		return $output;
	endif;
}

/** == Lien vers la page Facebook == **/
function tify_fb_api_page_link( $args = array() )
{
	if( empty( tiFy\Plugins\SocialShare\SocialShare::$Options['fb'][ 'uri' ] ) )
		return;

	$defaults = array(
		'class'		=> '',
		'text'		=> '',
		'attrs'		=> array(),
		'echo'		=> true
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$output = "<a href=\"". tiFy\Plugins\SocialShare\SocialShare::$Options['fb'][ 'uri' ] ."\" class=\"$class\"";
	if( ! isset( $attrs['title'] ) )
		$output .= " title=\"". sprintf( __( 'Vers la page Facebook du site %s', 'tify' ), get_bloginfo( 'name' ) ) ."\"";
	if( ! isset( $attrs['target'] ) )
		$output .= " target=\"_blank\"";
	
	foreach( (array) $attrs as $key => $value )
		$output .= " {$key}=\"{$value}\"";
	$output .= ">{$text}</a>";

	if( $echo ) :
		echo $output;
	else :
		return $output;
	endif;
}