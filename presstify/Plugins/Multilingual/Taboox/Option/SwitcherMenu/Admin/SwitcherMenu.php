<?php
namespace tiFy\Plugins\Multilingual\Taboox\Option\SwitcherMenu\Admin;

use tiFy\Core\Taboox\Admin;

class SwitcherMenu extends Admin
{
	/* = ARGUMENTS = */
	private $name = 'tify_multilingual_switcher';

	
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		\register_setting( $this->page, $this->name );
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form()
	{
		$sites 			= \tiFy\Plugins\Multilingual\Multilingual::$Sites;
		$translations 	= \tiFy\Plugins\Multilingual\Multilingual::$Translations;
		$values			= get_option( $this->name, array() );
	?>
	<table class="form-table">
		<tbody>
		<?php foreach( $sites as $site ) : 
				$blog_id 	= $site['blog_id']; 
				$locale 	= ( $_locale = get_blog_option( $blog_id, 'WPLANG' ) ) ? $_locale : 'en_US'; 
				$label	 	= isset( $translations[$locale] ) ? $translations[$locale]['native_name'] : __( 'English (United States)');
				$default 	= isset( $translations[$locale] ) ? $translations[$locale]['iso'][1] : $locale;
		?>
			<tr>
				<th scope="row">
					<?php echo $label;?>
				</th>
				<td>
					<input type="text" name="<?php echo $this->name;?>[<?php echo $blog_id;?>]" value="<?php echo isset( $values[$blog_id] ) ? $values[$blog_id] : $default;?>">
				</td>
			</tr>
		<?php endforeach;?>
		</tbody>
	</table>
	<?php
	}
}