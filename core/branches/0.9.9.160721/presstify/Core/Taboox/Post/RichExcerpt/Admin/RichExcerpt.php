<?php
namespace tiFy\Core\Taboox\Post\RichExcerpt\Admin;

use tiFy\Core\Taboox\Admin;

class RichExcerpt extends Admin
{
	/* = FORMULAIRE DE SAISIE = */	
	public function form( $post )
	{
		wp_editor( html_entity_decode( $post->post_excerpt ), 'excerpt', array( 'media_buttons' => false ) );
	}
}