<?php
namespace tiFy\Plugins\SocialShare\Network\GooglePlus\Taboox\Option\PageLink\Admin;

use tiFy\Core\Taboox\Admin;

class PageLink extends Admin
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
		$value 		= isset( \tiFy\Plugins\SocialShare\SocialShare::$Options['gplus'] ) ? wp_parse_args( \tiFy\Plugins\SocialShare\SocialShare::$Options['gplus'], $defaults ) : $defaults;
	?>
		<table class="form-table">
			<tbody>			
				<tr>
					<th scope="row">
						<?php _e( 'Url de la page Google Plus', 'tify' );?><br>
					</th>
					<td>
						<input type="text" name="tify_social_share[gplus][uri]" value="<?php echo $value['uri'];?>" size="80" placeholder="<?php _e( 'https://plus.google.com/[nom de la page]', 'tify' );?>" />
					</td>
				</tr>
			</tbody>
		</table>
	<?php
	}
}