<?php
/** == Formulaire d'inscription à la newsletter == **/
class tiFy_Wistify_Options_SubscribeForm_Taboox extends \tiFy_Taboox{
	/* = ARGUMENTS = */
	public 	// Paramètres
			$data_name 		= 'wistify_subscribe_form',
			$data_key 		= 'wistify_subscribe_form';				
			
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
					<?php _e( 'Titre du formulaire', 'tify' );?>
				</th>
				<td>
					<input type="text" name="<?php echo $this->data_name;?>[title]" value="<?php echo esc_attr( $this->data_value['title'] );?>" size="30" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Intitulé du champ email', 'tify' );?>
				</th>
				<td>
					<input type="text" name="<?php echo $this->data_name;?>[label]" value="<?php echo esc_attr( $this->data_value['label'] );?>" size="30" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Texte de remplacement du champ email', 'tify' );?>
				</th>
				<td>
					<input type="text" name="<?php echo $this->data_name;?>[placeholder]" value="<?php echo esc_attr( $this->data_value['placeholder'] );?>" size="30" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Texte du bouton de soumission', 'tify' );?>
				</th>
				<td>
					<input type="text" name="<?php echo $this->data_name;?>[button]" value="<?php echo esc_attr( $this->data_value['button'] );?>" size="30" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Message de succès d\'enregistrement', 'tify' );?>
				</th>
				<td>
					<textarea name="<?php echo $this->data_name;?>[success]" style="resize:none;" cols="30" rows="4"><?php echo esc_attr( $this->data_value['success'] );?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Liste de diffusion par défaut', 'tify' );?>
				</th>
				<td>
				<?php tiFy\Plugins\Wistify\Core\wistify_mailing_lists_dropdown( 
						array( 
							'name' 				=> $this->data_name ."[list_id]", 
							'selected' 			=> $this->data_value['list_id'], 
							'orderby' 			=> 'title',
							'order' 			=> 'ASC',
							'show_option_none' 	=> __( 'Aucune', 'tify' )
						) 
					);?>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
	}
}	