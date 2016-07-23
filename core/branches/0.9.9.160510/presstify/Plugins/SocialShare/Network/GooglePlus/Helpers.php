<?php
/** == Bouton de partage == **/
function tify_gplus_api_share_button( $args = array() ){
	$defaults = array(
			'class'			=> '',
			'button_text'	=> '',
			'uri'			=> is_singular() ? get_the_permalink( get_the_ID() ) : home_url( '/' ),
			'echo'			=> true
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$output = "<a href=\"https://plus.google.com/share?url=". esc_attr( $uri )."\" class=\"$class\" onclick=\"javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;\">$button_text</a>";

	if( $echo )
		echo $output;
	else
		return $output;
}

/** == Lien vers la page == **/
function tify_gplus_api_page_link( $args = array() )
{
	if( empty( tiFy\Plugins\SocialShare\SocialShare::$Options['gplus'][ 'uri' ] ) )
		return;

	$defaults = array(
			'class'		=> '',
			'title'		=> '',
			'attrs'		=> array(),
			'echo'		=> true
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$output = "<a href=\"". tiFy\Plugins\SocialShare\SocialShare::$Options['gplus'][ 'uri' ] ."\" class=\"$class\"";
	if( ! isset( $attrs['title'] ) )
		$output .= " title=\"". sprintf( __( 'Vers la page Google+ du site %s', 'tify'), get_bloginfo( 'name' ) ) ."\"";
	
	if( ! isset( $attrs['target'] ) )
		$output .= " target=\"_blank\"";
	
	foreach( (array) $attrs as $key => $value )
		$output .= " {$key}=\"{$value}\"";
				
	$output .= ">$title</a>";

	if( $echo )
		echo $output;
	else
		return $output;
}