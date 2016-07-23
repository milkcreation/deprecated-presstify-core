<?php
namespace tiFy\Core\Taboox\Taxonomy\CustomHeader\Admin;

use tiFy\Core\Taboox\Admin;

class CustomHeader extends Admin
{
	/* = CHARGEMENT DE LA PAGE = */
	public function current_screen( $current_screen )
	{
		tify_meta_term_register( $current_screen->taxonomy, '_custom_header', true );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'Taboox_Taxonomy_CustomHeader_Admin', $this->Url .'/CustomHeader.css', array( 'tify_control-media_image' ), '150325' );
		wp_enqueue_script( 'Taboox_Taxonomy_CustomHeader_Admin', $this->Url .'/CustomHeader.js', array( 'jquery', 'tify_control-media_image' ), '150325', true );
	}
	
	/* = FORMULAIRE DE SAISIE = */	
	public function form( $term, $taxonomy )
	{
		$this->args['media_library_title'] 	= __( 'Personnalisation de l\'image d\'entête', 'tify' );
		$this->args['media_library_button']	= __( 'Utiliser comme image d\'entête', 'tify' );
		$this->args['name'] 				= 'tify_meta_term[_custom_header]';
		$this->args['value'] 				= get_term_meta( $term->term_id, '_custom_header', true );
				
		tify_control_media_image( $this->args );
	}
}