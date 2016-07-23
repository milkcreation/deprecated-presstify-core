<?php
class tiFy_ContactFormHelpers{
	/** == Affichage d'un élément de template == **/
	public function display( $id, $content = '', $echo = true ){
		if( ! isset( $this->{$id} ) )
			return;
		
		if( method_exists( $this->{$id}, 'display' ) )
			return call_user_func( array( $this->{$id}, 'display' ), $content, $echo );
	}
}

/** == Affichage du formulaire de contact == **/
function tify_contact_form_display( $id, $content = '', $echo = true ){
	global $tify_contact_form;
	
	return $tify_contact_form->display( $id, $content, $echo );
}