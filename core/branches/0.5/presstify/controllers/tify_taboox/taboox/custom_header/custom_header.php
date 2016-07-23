<?php
/**
 * PERSONNALISATION DE L'IMAGE D'ENTÊTE
 * 
 * @author Jordy Manner
 * @copyright Milkcreation
 * @version 1.150313
 */
/* = Déclaration de la taboox = */
add_action( 'tify_taboox_register_form', 'tify_taboox_register_form_custom_header' );
function tify_taboox_register_form_custom_header(){
	tify_taboox_register_form( 'tify_Taboox_CustomHeader' );
}

class tify_Taboox_CustomHeader extends tiFy_Taboox{
	public 	$name = 'custom_header';
	
	public function current_screen( $screen ){
		tify_control_enqueue( 'media_image' );
		wp_enqueue_script( 'tify_taboox_custom_header', $this->uri .'/admin.css', array( ), '150325' );
		wp_enqueue_style( 'tify_taboox_custom_header', $this->uri .'/admin.js', array( 'jquery' ), '150325', true );
	}
		
	public function form( $_args = array() ){
		$this->args['media_library_title'] 	= __( 'Personnalisation de l\'image d\'entête', 'tify' );
		$this->args['media_library_button']	= __( 'Utiliser comme image d\'entête', 'tify' );
		$this->args['name'] = $this->name;
		$this->args['value'] = $this->value;
		
		tify_control_media_image( $this->args );
	}
}

/**
 * Filtre de remplacement de l'image d'entête
 */
add_filter( 'theme_mod_header_image', 'tify_taboox_custom_header_theme_mod_header_image' );
function tify_taboox_custom_header_theme_mod_header_image( $url ){
	if( is_singular() ) :
		$header = get_post_meta( get_the_ID(), '_custom_header', true );
		if( is_numeric( $header ) && ( $image = wp_get_attachment_image_src( $header, 'full' ) ) ) :
			$url = $image[0];	
		elseif( is_string( $header ) ) :
			$url = $header;
		endif;
	elseif( is_home() && get_option( 'page_for_posts' ) ) :
		$header = get_post_meta( get_option( 'page_for_posts' ), '_custom_header', true );
		if( is_numeric( $header ) && ( $image = wp_get_attachment_image_src( $header, 'full' ) ) ) :
			$url = $image[0];	
		elseif( is_string( $header ) ) :
			$url = $header;
		endif;
	endif;
		
	return $url;
}