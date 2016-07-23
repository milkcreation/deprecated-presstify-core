<?php
function tify_backdrop(){
	static $instance;
	if( $instance++ )
		return;
	
	$defaults	= array(
	);	
	
	add_action( 'wp_footer', 'tify_backdrop_wp_footer' ); 
	add_action( 'admin_footer', 'tify_backdrop_wp_footer' );
}

function tify_backdrop_wp_footer(){
	echo 	"<div id=\"tify_backdrop\"></div>";
}
