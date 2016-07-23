<?php
/* = HELPERS = */
/** == Affichage d'un formulaire == **/
function tify_form_display( $form = null, $echo = true ){
	if( $echo )
		echo do_shortcode( '[formulaire id="'. $form .'"]' );
	else
		return do_shortcode( '[formulaire id="'. $form .'"]' );
}

/** == Déclaration d'un formulaire == **/
function tify_form_register( $form = array() ){
	global $tify_forms;
	
	return $tify_forms->register_form( $form );
}

/** == Déclaration d'un addon == **/
function tify_form_register_addon( $id, $callback, $filename = null, $args = array() ){
	global $tify_forms;
	
	return $tify_forms->addons->register( $id, $callback, $filename, $args );
}

/** == Shortcode d'affichage de formulaire == **/
add_shortcode( 'formulaire', 'tify_form_shortcode' );
function tify_form_shortcode( $atts = array() ){
	global $tify_forms;
		
	extract( 
		shortcode_atts(
			array( 'id' => null ), 
			$atts
		) 
	);

	return $tify_forms->display( $id, false );
}

/** == Définition du formulaire courant == **/
function tify_form_set_current( $form_id ){
	global $tify_forms;
	
	return $tify_forms->forms->set_current( $form_id );
}