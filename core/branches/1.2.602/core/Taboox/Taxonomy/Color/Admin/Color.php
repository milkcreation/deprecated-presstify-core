<?php
namespace tiFy\Core\Taboox\Taxonomy\Color\Admin;

use tiFy\Core\Taboox\Admin;

class Color extends Admin
{
	/* = CHARGEMENT DE LA PAGE = */
	public function current_screen( $current_screen )
	{
		tify_meta_term_register( $current_screen->taxonomy, '_color', true );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'tify_control-colorpicker' );
		wp_enqueue_script( 'tify_control-colorpicker' );
	}
	
	/* = FORMULAIRE DE SAISIE = */	
	public function form( $term, $taxonomy )
	{
		tify_control_colorpicker(
			array(
				'name'		=> 'tify_meta_term[_color]',
				'value'	=> get_term_meta( $term->term_id, '_color', true ),
				'options'	=> array(
					'showInput'	=> true
				)
			)
		);
	}
}