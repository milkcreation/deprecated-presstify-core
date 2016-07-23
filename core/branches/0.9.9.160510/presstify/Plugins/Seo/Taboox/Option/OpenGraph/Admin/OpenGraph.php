<?php
namespace tiFy\Plugins\Seo\Taboox\Option\OpenGraph\Admin;

use tiFy\Core\Taboox\Admin;

class OpenGraph extends Admin
{
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		register_setting( $this->page, 'tify_opengraph' );
	}
	
	/* = MISE EN FILE DES SCRIPTS DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_enqueue_scripts()
	{
		wp_enqueue_media();
		tify_control_enqueue( 'switch' );
		tify_control_enqueue( 'media_image' );
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public 	function form()
	{
		$value = wp_parse_args( get_option( 'tify_opengraph', array() ), array( 'active' => 'off', 'default_image' => 0 ) );	
	?>
		<table class="form-table">
			<tbody>			
				<tr>
					<th scope="row">
						<label for="tify_social_share-og_active"><?php _e( 'Activer l\'OpenGraph', 'tify' );?></label><br>
					</th>
					<td>
						<?php tify_control_switch( array( 'name' => 'tify_opengraph[active]', 'checked' => $value['active'] ) );?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php _e( 'Image représentative du site (pour le partage)', 'tify' );?><br>
						<em style="font-size:11px; color:#999;"><?php printf( __( 'HD : %s | LD : %s', 'tify' ), '[1200x1200]', '[600x600]')?></em>
					</th>
					<td>
						<?php tify_control_media_image( 
							array( 
								'name' 		=> 'tify_opengraph[default_image]',
								'value'		=> $value['default_image'],
								'width'		=> 300,
								'height'	=> 300
							)
						);?>
					</td>
				</tr>
			</tbody>
		</table>
	<?php
	}
}