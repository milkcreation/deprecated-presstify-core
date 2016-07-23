<?php
namespace tiFy\Core\Taboox\Admin\Option\Imprint;

use tiFy\Core\Taboox\Admin;

class Imprint extends Admin
{	
	/* = ARGUMENTS = */
	private $option_name = 'page_for_imprint';
			
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		\register_setting( $this->page, $this->option_name );
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form()
	{
		wp_dropdown_pages(
			array(
				'name' 				=> $this->option_name,
				'post_type' 		=> 'page',
				'selected' 			=> get_option( 'page_for_imprint', false ),
				'show_option_none' 	=> __( 'Aucune page choisie', 'bigben' ),
				'sort_column' 		=> 'menu_order'
			)
		);
	}
}