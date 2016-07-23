<?php
namespace tiFy\Plugins\Membership\Taboox\Admin\Option;

use tiFy\Core\Taboox\Admin;

class Membership extends Admin
{
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		register_setting( $this->page, 'page_for_tify_membership' );
	}

	/* = FORMULAIRE DE SAISIE = */
	public function form()
	{
	?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e( 'Page  d\'affichage de l\'accès pro.', 'tify' ); ?></th>
					<td>
					<?php 
						wp_dropdown_pages(
							array(
								'name' 				=> 'page_for_tify_membership',
								'post_type' 		=> 'page',
								'selected' 			=> get_option( 'page_for_tify_membership', 0 ),
								'show_option_none' 	=> __( 'Aucune page choisie', 'tify' ),
								'sort_column'  		=> 'menu_order' 
							)
						);
					?>
					</td>
				</tr>
			</tbody>
		</table>
	<?php 
	}
}