<?php
namespace tiFy\Taboox\Admin\Post\CustomHeader;

use tiFy\Taboox\Admin;

class CustomHeader extends Admin
{
	/* = CHARGEMENT DE LA PAGE = */
	public function current_screen( $current_screen )
	{
		// Déclaration des metadonnées à enregistrer
		register_post_meta( $current_screen->id, '_custom_header' );	
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function admin_enqueue_scripts()
	{
		tify_control_enqueue( 'media_image' );
		wp_enqueue_script( 'tify_taboox_custom_header', $this->Url .'/admin.js', array( 'jquery' ), '150325', true );
		wp_enqueue_style( 'tify_taboox_custom_header', $this->Url .'/admin.css', array( ), '150325' );
	}
	
	/* = FORMULAIRE DE SAISIE = */	
	public function form( $post )
	{
		$this->args['media_library_title'] 	= __( 'Personnalisation de l\'image d\'entête', 'tify' );
		$this->args['media_library_button']	= __( 'Utiliser comme image d\'entête', 'tify' );
		$this->args['name'] 				= 'tify_post_meta[single][_custom_header]';
		$this->args['value'] 				= get_post_meta( $post->ID, '_custom_header', true );
		
		tify_control_media_image( $this->args );
	}
}