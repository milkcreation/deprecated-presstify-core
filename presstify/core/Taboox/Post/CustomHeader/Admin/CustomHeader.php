<?php
namespace tiFy\Core\Taboox\Post\CustomHeader\Admin;

class CustomHeader extends \tiFy\Core\Taboox\Admin
{
	/* = CHARGEMENT DE LA PAGE = */
	public function current_screen( $current_screen )
	{
		// Déclaration des metadonnées à enregistrer
		tify_meta_post_register( $current_screen->id, '_custom_header', true );	
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function admin_enqueue_scripts()
	{
		tify_control_enqueue( 'media_image' );
	}
	
	/* = FORMULAIRE DE SAISIE = */	
	public function form( $post )
	{
		$this->args['media_library_title'] 	= __( 'Personnalisation de l\'image d\'entête', 'tify' );
		$this->args['media_library_button']	= __( 'Utiliser comme image d\'entête', 'tify' );
		$this->args['name'] 				= 'tify_meta_post[_custom_header]';
		$this->args['value'] 				= get_post_meta( $post->ID, '_custom_header', true );
		
		tify_control_media_image( $this->args );
	}
}