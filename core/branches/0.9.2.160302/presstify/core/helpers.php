<?php
/* = CHEMINS = */
/** == Récupération du répertoire de PressTiFy == **/
function tify_get_directory(){
	global $tiFy;
	
	return $tiFy->get_directory();
}

/** == Récupération du répertoire de PressTiFy == **/
function tify_get_directory_uri(){
	global $tiFy;
	
	return $tiFy->get_directory_uri();
}

/* = HABILITATIONS = */
/** == == **/
function tify_user_can(){
	global $tiFy;
	
	return $tiFy->capabilities->user_can();
}

/* = LIBRAIRIES = */
/** == Récupération d'une librairie == 
 * @todo DEPRECATED => tify_require_lib + parcour du repertoire afin de définir les librairies disponibles
 **/
function tify_require_lib( $require ){
	
	if( ! in_array( $require, array( 'admin_view', 'calendar', 'custom_column', 'facebook_sdk', 'login', 'mailer', 'mandrill' ) ) )
		return;

	$sections = preg_split( "/_/", $require );
	$name = '';
	foreach( $sections as $section )
		$name .= ucfirst( (string) $section );
	
	global $tiFy;	
	$filename = $tiFy->dir .'/lib/tiFy_'. $name .'/tiFy_'. $name .'.php'; 

	if( ! file_exists( $filename ) )
		return;
	require_once $filename;	
}

function tify_control_enqueue( $scripts ){
	global $tiFy;
	
	if( is_string( $scripts ) )
		$scripts = array( $scripts );

	foreach( $scripts as $script )
		$tiFy->Kernel->Control->{$script}->enqueue_scripts();			
}