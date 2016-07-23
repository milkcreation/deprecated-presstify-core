<?php
namespace tiFy\Plugins\SocialShare\Network\Twitter\Taboox\Admin\Option;

use tiFy\Core\Taboox\Admin;

class Twitter extends Admin
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
		$value 		= isset( \tiFy\Plugins\SocialShare\SocialShare::$Options['tweet'] ) ? wp_parse_args( \tiFy\Plugins\SocialShare\SocialShare::$Options['tweet'], $defaults ) : $defaults;
	?>
		<table class="form-table">
			<tbody>			
				<tr>
					<th scope="row">
						<?php _e( 'Url du compte Twitter', 'tify' );?>
					</th>
					<td>
						<input type="text" name="tify_social_share[tweet][uri]" value="<?php echo $value['uri'];?>" size="80" placeholder="<?php _e( 'https://twitter.com/[nom de la page]', 'tify' );?>" />
					</td>
				</tr>
			</tbody>
		</table>
	<?php
	}
}