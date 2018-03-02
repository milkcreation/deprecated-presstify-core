<?php
namespace tiFy\Core\Taboox\Options\ContentHook\Admin;

class ContentHook extends \tiFy\Core\Taboox\Options\Admin
{	
	/* = ARGUMENTS = */
	public static $Registered		= array();
	
	private $InstanceRegistred		= array();
	
	/* = INITIALISATION GLOBALE = */
	public function init()
	{		
		foreach( (array) $this->args as $id => $args ) :
			$this->Register( $id, $args );
		endforeach;
	}
	
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		foreach( (array) $this->InstanceRegistred as $id => $args ) :
			\register_setting( $this->page, $args['name'] );
		endforeach;
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form()
	{
	?>
	<table class="form-table">
		<tbody>
		<?php foreach( (array) $this->InstanceRegistred as $id => $args ) : ?>
			<tr>
				<th><?php echo $args['title'];?></th>
				<td>
				<?php 
					wp_dropdown_pages(
						array(
							'name' 				=> $args['name'],
							'post_type' 		=> $args['post_type'],
							'selected' 			=> $args['selected'],
							'sort_column' 		=> $args['listorder'],
							'show_option_none' 	=> $args['show_option_none']							
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
	
	/* = CONTRÔLEURS = */
	/** == Déclaration == **/
	private function Register( $id, $args )
	{
		$defaults = array(
			'title'				=> $id,
			'post_type'			=> 'page',
			'name'				=> 'tify_content_hook_'. $id,
			'selected'			=> 0,
			'listorder'			=> 'menu_order, title',
			'show_option_none'	=> __( 'Aucune page choisie', 'tify' )
		);
		$args = wp_parse_args( $args, $defaults );
		
		$args['selected'] =  ( $selected = (int) get_option( $args['name'], 0 ) ) ? $selected: 0;
		
		$this->InstanceRegistred[$id] = self::$Registered[$id] = $args;		
	}
}