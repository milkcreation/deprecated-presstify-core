<?php
namespace tiFy\Plugins\SocialShare\Network\YouTube\Taboox\Admin\Option;

use tiFy\Core\Taboox\Admin;

class YouTube extends Admin
{
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		\register_setting( $this->page, 'tify_social_share' );
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form()
	{
		$defaults 	= array( 'uri' => '' );
		$value 		= isset( \tiFy\Plugins\SocialShare\SocialShare::$Options['youtube'] ) ? wp_parse_args( \tiFy\Plugins\SocialShare\SocialShare::$Options['youtube'], $defaults ) : $defaults;
	?>
		<table class="form-table">
			<tbody>			
				<tr>
					<th scope="row">
						<?php _e( 'Url de la chaîne You Tube', 'tify' );?><br>
					</th>
					<td>
						<input type="text" name="tify_social_share[youtube][uri]" value="<?php echo $value['uri'];?>" size="80" placeholder="<?php _e( 'https://www.youtube.com/channel/[nom de la chaîne]', 'tify' );?>" />
					</td>
				</tr>
			</tbody>
		</table>
	<?php
	}
}