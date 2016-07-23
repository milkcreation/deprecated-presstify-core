<?php
namespace tiFy\Plugins\Membership\Admin;

use tiFy\Core\Entity\AdminView\EditForm;
 
class EditUser extends EditForm
{	
	final public function current_screen( $current_screen ){
		global $tify_forms;
		$tify_forms->forms->set_current( 'tify_membership_subscribe_form' );
		var_dump( $tify_forms->errors->get( 'tify_membership_subscribe_form' ) );
	}
	
	protected function process_bulk_actions()
	{
		
	}
	
	/** == Formulaire de saisie == **/
	public function form()
	{
		tify_taboox_display( get_userdata( $this->item->ID ) );
	}
	
	/** == Affichage des actions principale de la boîte de soumission du formulaire == **/
	public function major_actions()
	{
		global $tify_forms;
		$form =  $tify_forms->forms->get( );
				
		echo $tify_forms->forms->hidden_fields( $form );
	?>
		<div class="updating">
			<?php echo $tify_forms->buttons->submit( $form, array( 'class' => 'button-primary' ) );?>
		</div>
	<?php
	}
}