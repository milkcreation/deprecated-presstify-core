<?php
namespace tiFy\Plugins\SocialShare\Network\Facebook\Taboox\Option\PageLink\Admin;

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
		$defaults 	= array( 'appId' => '', 'uri' => '' ); 
		$value 		= isset( \tiFy\Plugins\SocialShare\SocialShare::$Options['fb'] ) ? wp_parse_args( \tiFy\Plugins\SocialShare\SocialShare::$Options['fb'], $defaults ) : $defaults;
	?>
		<table class="form-table">
			<tbody>			
				<tr>
					<th scope="row">
						<?php _e( 'Identifiant de l\'API Facebook', 'tify' );?>*<br>
						<em style="font-size:11px; color:#999;"><?php _e( 'Requis', 'tify' );?></em>	
					</th>
					<td>
						<input type="text" name="tify_social_share[fb][appId]" value="<?php echo $value['appId'];?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php _e( 'Url de la page Facebook', 'tify' );?><br>
					</th>
					<td>
						<input type="text" name="tify_social_share[fb][uri]" value="<?php echo $value['uri'];?>" size="80" placeholder="<?php _e( 'https://www.facebook.com/[nom de la page]','tify' );?>" />
					</td>
				</tr>
			</tbody>
		</table>
	<?php
	}
}