<?php
function tify_facebook_jsinit( $app_id = null )
{
	add_action( 'wp_footer', array( \tiFy\Lib\Facebook\JSInit\JSInit, 'FBRoot' ), 1 );
}