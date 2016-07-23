<?php 
namespace tiFy\Plugins\CookieLaw\Taboox\Option\CookiePolicyPage\Admin;

use tiFy\Core\Taboox\Admin;

class CookiePolicyPage extends Admin
{	
	/* = ARGUMENTS = */
	private $option_name = 'page_for_cookie_law';
			
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
				'name' 				=> 'page_for_cookie_law', 
				'post_type' 		=> 'page', 
				'selected' 			=> get_option( 'page_for_cookie_law', 0 ), 
				'show_option_none' 	=> __( 'Aucune page choisie', 'tify' ), 
				'sort_column'  		=> 'menu_order' 
			) 
		);
	}
}
