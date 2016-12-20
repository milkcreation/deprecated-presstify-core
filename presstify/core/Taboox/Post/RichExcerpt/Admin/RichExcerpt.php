<?php
namespace tiFy\Core\Taboox\Post\RichExcerpt\Admin;

class RichExcerpt extends \tiFy\Core\Taboox\Admin
{
	/* = FORMULAIRE DE SAISIE = */	
	public function form( $post )
	{
		wp_editor( html_entity_decode( $post->post_excerpt ), 'excerpt', array( 'media_buttons' => false ) );
	}
}