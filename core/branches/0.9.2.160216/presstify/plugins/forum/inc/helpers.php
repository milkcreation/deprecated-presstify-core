<?php
/* = HELPER = */
/** == Affichage des tmplate de forum == **/
function tify_forum_template( $template_name ){
	global $tify_forum;
	
	$template_name = 'tpl_'. $template_name;
	
	$args = $args = array_slice( func_get_args(), 1 );

	if( method_exists( $tify_forum->template, $template_name ) )
		return call_user_func_array( array( $tify_forum->template, $template_name ), $args );
}