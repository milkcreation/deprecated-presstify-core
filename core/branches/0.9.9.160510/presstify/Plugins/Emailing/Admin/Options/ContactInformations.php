<?php
/** == Informations de contact == **/
class tiFy_Wistify_Options_ContactInformations_Taboox extends \tiFy_Taboox{
	/* = ARGUMENTS = */
	public 	$data_name 		= 'wistify_contact_information',
			$data_key 		= 'wistify_contact_information';			
			
	private	// Référence
			$master;			
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy\Plugins\Wistify\Wistify $master ){
		// Déclaration de la classe de référence
		$this->master = $master;
		
		// Instanciation de la classe parente
		parent::__construct();				
	}
	
	/** == Initialisation de la vue courante == **/
	public function current_screen( $current_screen ){
		// Définition des options par défaut
		$this->defaults	= $this->master->options->get_defaults( $this->data_name );	
	}

	/* = FORMULAIRE = */
	public function form(){
	?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<?php _e( 'Nom de contact', 'tify' );?>
				</th>
				<td>
					<div class="tify_input_user">
						<input type="text" name="<?php echo $this->data_name;?>[contact_name]" value="<?php echo esc_attr( $this->data_value['contact_name'] );?>" size="50"/>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Email de contact', 'tify' );?>
				</th>
				<td>
					<div class="tify_input_email">
						<input type="text" name="<?php echo $this->data_name;?>[contact_email]" value="<?php echo esc_attr( $this->data_value['contact_email'] );?>" size="50"/>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Email de réponse', 'tify' );?>
				</th>
				<td>
					<div class="tify_input_email">
						<input type="text" name="<?php echo $this->data_name;?>[reply_to]" value="<?php echo esc_attr( $this->data_value['reply_to'] );?>" size="50"/>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Société / Organisation', 'tify' );?>
				</th>
				<td>
					<div class="tify_input_building">
						<input type="text" name="<?php echo $this->data_name;?>[company_name]" value="<?php echo esc_attr( $this->data_value['company_name'] );?>" size="50"/>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Site internet', 'tify' );?>
				</th>
				<td>
					<div class="tify_input_link">
						<input type="text" name="<?php echo $this->data_name;?>[website]" value="<?php echo esc_url( $this->data_value['website'] );?>" size="50"/>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Adresse postale', 'tify' );?>
				</th>
				<td>
					<textarea name="<?php echo $this->data_name;?>[address]" cols="48" rows="4" style="resize:none;box-shadow:none;"><?php echo esc_attr( $this->data_value['address'] );?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Téléphone', 'tify' );?>
				</th>
				<td>
					<div class="tify_input_phone">
						<input type="text" name="<?php echo $this->data_name;?>[phone]" value="<?php echo esc_attr( $this->data_value['phone'] );?>" />
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
	}
}