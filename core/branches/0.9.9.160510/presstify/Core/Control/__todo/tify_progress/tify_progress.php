<?php
function tify_progress( $args = array() ){
	static $instance;
	if( $instance++ )
		return;
	
	$defaults	= array(
		'backdrop'	=> true
	);	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	if( $backdrop )
		tify_backdrop();	
	
	add_action( 'wp_footer', 'tify_progress_wp_footer' ); 
	add_action( 'admin_footer', 'tify_progress_wp_footer' ); 
}

function tify_progress_wp_footer(){
	echo 	"<div id=\"tify_progress\">\n".
			"\t<h3 class=\"title\"></h3>\n".
			"\t<div class=\"content-bar\">\n".
			"\t\t<div class=\"progress-bar\"></div>\n".
			"\t\t<div class=\"text-bar\">\n".
			"\t\t\t<span class=\"current\"></span><span class=\"sep\"></span><span class=\"total\"></span>\n".
			"\t\t</div>\n".
			"\t\t<div class=\"infos\"></div>\n".				
			"\t</div>\n".
			"</div>\n";
}