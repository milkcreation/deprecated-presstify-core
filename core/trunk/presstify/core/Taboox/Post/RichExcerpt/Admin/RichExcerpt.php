<?php
namespace tiFy\Core\Taboox\Post\RichExcerpt\Admin;

class RichExcerpt extends \tiFy\Core\Taboox\Admin
{
	/* = CHARGEMENT DE LA PAGE = */	
	public function current_screen( $current_screen )
	{
        add_action( 'add_meta_boxes', function() use ( $current_screen ) { remove_meta_box( 'postexcerpt', $current_screen->id, 'normal' ); });
	}
        
    /* = FORMULAIRE DE SAISIE = */  
    public function form( $post )
    {
        wp_editor( html_entity_decode( $post->post_excerpt ), 'excerpt', array( 'media_buttons' => false ) );
    }
}