<?php
namespace tiFy\Plugins\WebService\Server\Admin\App\EditForm;

use tiFy\Core\View\Admin\EditForm\EditForm as tiFy_EditForm;

class EditForm extends tiFy_EditForm
{
	/* = ACTIONS = */
	/** == Mise en file des scripts de l'interface d'administration == **/
	final public function admin_enqueue_scripts()
	{
		tify_control_enqueue( 'text_remaining' );
		tify_control_enqueue( 'switch' );
		tify_control_enqueue( 'dynamic_inputs' );
		tify_control_enqueue( 'token' );
	}
	
	/* = CHAMPS D'ÉDITION = */
	/** == Description == **/
	public function field_wsapp_desc( $item )
	{
		tify_control_text_remaining( 
			array(
				'name'	=> 'wsapp_desc',
				'value'	=> $item->wsapp_desc
			)
		);
	}
	
	/** == Clé d'authentification == **/
	public function field_wsapp_key_hash( $item )
	{
		$public_key 	= ( ! $item->wsapp_public_key ) ? \tiFy\Lib\Token::KeyGen( 32 ) : $item->wsapp_public_key;
		$private_key 	= ( ! $item->wsapp_private_key ) ? \tiFy\Lib\Token::KeyGen( 64 ) : $item->wsapp_private_key;
		
		tify_control_token(
			array(
				'name'			=> 'wsapp_key_hash',
				'value'			=> $item->wsapp_key_hash,	
				'public_key'	=> $public_key,
				'private_key'	=> $private_key
			)
		);
	?>				
		<input type="hidden" name="wsapp_public_key" value="<?php echo $public_key;?>" />
		<input type="hidden" name="wsapp_private_key" value="<?php echo $private_key;?>" />
	<?php	
	}
	
	/** == Habilitations == **/
	public function field_wsapp_caps( $item )
	{
		$caps = array( 
			'READABLE' 	=> __( 'Lecture', 'tify' ), 
			'EDITABLE'	=> __( 'Écriture', 'tify' ), 
			'UPDATABLE'	=> __( 'Mise à jour', 'tify' ),				
			'DELETABLE'	=> __( 'Suppression (non recommandé)', 'tify' ) 				
		);
	?>
		<input type="hidden" name="wsapp_caps" value=""/>
		<ul>
		<?php foreach( $caps as $cap => $label ) :?>
			<li>
				<label>
					<input type="checkbox" value="<?php echo $cap;?>" name="wsapp_caps[]" <?php checked( in_array( $cap, (array) $item->wsapp_caps ) );?> autocomplete="off"/>
					<?php echo $label;?>
				</label>
			</li>
		<?php endforeach;?>
		</ul>
	<?php
	}
	
	/** == Habilitations == **/
	public function field_wsapp_ips( $item )
	{
	?>
		<input type="hidden" name="wsapp_ips" value=""/>
	<?php 	
		tify_control_dynamic_inputs( 
			array(
				'name'		=> 'wsapp_ips',
				'values'	=> ! empty( $item->wsapp_ips ) ? (array) $item->wsapp_ips : array()
			)
		);
	}
	
	/** == Activation == **/
	public function field_wsapp_active( $item )
	{
		tify_control_switch( 
			array(
				'name'			=> 'wsapp_active',
				'checked'		=> (int) $item->wsapp_active,
				'value_on'		=> 1,
				'value_off'		=> 0
			)
		);
	}
}