<?php
/** == Bouton de partage ==
 * @see https://dev.twitter.com/web/tweet-button
 */
function tify_tweet_api_share_button( $args = array() ){
	if( is_singular() )
		$post = get_post();

	$defaults = array(
			'class'				=> '',
			'button_text'		=> '',
			'url'				=> wp_get_shortlink( ),
			'text'				=> is_singular() ? get_the_title( get_the_ID() ) : get_bloginfo( 'name' ),
			'echo'				=> true
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$output = "<a href=\"https://twitter.com/intent/tweet?url=".esc_attr( $url ) ."&text=".esc_attr( $text ) ."\" class=\"$class\">$button_text</a>";

	if( $echo )
		echo $output;
	else
		return $output;
}

/** == lien vers la page == **/
function tify_tweet_api_page_link( $args = array() )
{
	if( empty( tiFy\Plugins\SocialShare\SocialShare::$Options['tweet'][ 'uri' ] ) )
		return;

	$defaults = array(
			'class'		=> '',
			'text'		=> '',
			'attrs'		=> array(),
			'echo'		=> true
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$output = "<a href=\"". tiFy\Plugins\SocialShare\SocialShare::$Options['tweet'][ 'uri' ] ."\" class=\"$class\"";
	if( ! isset( $attrs['title'] ) )
		$output .= " title=\"". sprintf( __( 'Vers le compte Twitter du site %s', 'tify' ), get_bloginfo( 'name' ) ) ."\"";
	if( ! isset( $attrs['target'] ) )
		$output .= " target=\"_blank\"";
	foreach( (array) $attrs as $key => $value )
		$output .= " {$key}=\"{$value}\"";
	$output .= ">{$text}</a>";

	if( $echo )
		echo $output;
	else
		return $output;
}