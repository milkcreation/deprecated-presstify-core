<?php
namespace tiFy\Components\HookForArchive\Taboox\Admin\Option;

use tiFy\Core\Taboox\Admin;

class PostTypeForArchive extends Admin
{	
	/* = ARGUMENTS = */
	private $Hooks;
	
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		$this->Hooks = \tiFy\Components\HookForArchive\HookForArchive::$hooks;
		
		foreach( (array) $this->Hooks as $archive_post_type => $args )
			\register_setting( $this->page, "{$args['hook_post_type']}_for_{$archive_post_type}" );
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form()
	{
	?>
		<table class="form-table">
			<tbody>
			<?php foreach( (array) $this->Hooks as $archive_post_type => $args ) : ?>	
				<tr>
					<th scope="row">
						<?php echo get_post_type_object( $archive_post_type )->label;?>
					</th>
					<td>
					<?php 
						wp_dropdown_pages(
							array(
								'name' 				=> "{$args['hook_post_type']}_for_{$archive_post_type}",
								'post_type' 		=> $args['hook_post_type'],
								'selected' 			=> $args['hook_id'],
								'show_option_none' 	=> __( 'Aucune page choisie', 'tify' ),
								'sort_column'  		=> 'menu_order'
							)
						);
					?>
					</td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
		
	<?php	
	}
}