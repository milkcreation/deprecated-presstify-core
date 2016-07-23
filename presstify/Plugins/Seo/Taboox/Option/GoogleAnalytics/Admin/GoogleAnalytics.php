<?php
namespace tiFy\Plugins\Seo\Taboox\Option\GoogleAnalytics\Admin;

use tiFy\Core\Taboox\Admin;

class GoogleAnalytics extends Admin
{
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		\register_setting( $this->page, 'tify_google_analytics_ua_code' );
	}
		
	/* = FORMULAIRE DE SAISIE = */
	public 	function form()
	{
		$value = get_option( 'tify_google_analytics_ua_code', '' );
	?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<?php _e( 'Code Google Analytics', 'tify' );?><br>
						<em><a href="http://www.google.com/analytics/" title="<?php _e( 'Vers le site officiel de Google Analytics', 'tify' );?>" style="font-size:11px; text-decoration:none;" target="_blank"><?php _e( 'Site Google Analytics', 'tify' );?></a></em>
					</th>			
					<td>
						<input type="text" name="tify_google_analytics_ua_code" value="<?php echo $value;?>"/>
					</td>
				</tr>
			</tbody>
		</table>
	<?php
	}
}