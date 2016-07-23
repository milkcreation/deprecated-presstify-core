<?php
class tiFy_Wistify_AdminOptions{
	/* = ARGUMENTS = */
	public	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Wistify_Master $master ){
		// Declaration de la classe de référence principale
		$this->master = $master;		
		
		// Actions et Filtres PressTiFy
		add_action( 'tify_taboox_register_box', array( $this, 'tify_taboox_register_box' ) );
		add_action( 'tify_taboox_register_node', array( $this, 'tify_taboox_register_node' ) );
		add_action( 'tify_taboox_register_form', array( $this, 'tify_taboox_register_form' ) );	
	}
	
	/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == Déclaration de la boîte à onglets == **/
	public function tify_taboox_register_box(){
		tify_taboox_register_box( 
			$this->master->hookname['options'],
			'option',
			array(
				'title'		=> __( 'Options', 'tify' ),
				'page'		=> $this->master->menu_slug['options']
			)
		);
	}
	
	/** == Déclaration des sections de boîte à onglets == **/
	public function tify_taboox_register_node(){
		tify_taboox_register_node(
			$this->master->hookname['options'],
			array(
				'id' 			=> 'wistify-global_options',
				'title' 		=> __( 'Réglages généraux', 'tify' ),
				'cb'			=> 'tiFy_Wistify_Options_Global_Taboox',
				'order'			=> 1
			)
		);
		tify_taboox_register_node(
			$this->master->hookname['options'],
			array(
				'id' 			=> 'wistify-subscribe-form',
				'title' 		=> __( 'Formulaire d\'inscription', 'tify' ),
				'cb'			=> 'tiFy_Wistify_Options_SubscribeForm_Taboox',
				'order'			=> 2
			)
		);
		tify_taboox_register_node(
			$this->master->hookname['options'],
			array(
				'id' 			=> 'wistify-contact_options',
				'title' 		=> __( 'Information de contact', 'tify' ),
				'cb'			=> 'tiFy_Wistify_Options_ContactInformations_Taboox',
				'order'			=> 3
			)
		);
	}
	
	/** == Déclaration des interfaces de saisie == **/
	public function tify_taboox_register_form(){
		tify_taboox_register_form( 'tiFy_Wistify_Options_Global_Taboox', $this->master );
		tify_taboox_register_form( 'tiFy_Wistify_Options_SubscribeForm_Taboox', $this->master );	
		tify_taboox_register_form( 'tiFy_Wistify_Options_ContactInformations_Taboox', $this->master );		
	}	
	
	/* = = */
	public function admin_render(){
	?>		
	<div class="wrap">
		<h2><?php _e( 'Réglages des options', 'tify' ); ?></h2>
		<?php settings_errors(); ?>
		
		<form method="post" action="options.php">
			<div style="margin-right:300px; margin-top:20px;">
				<div style="float:left; width: 100%;">
					<?php settings_fields( $this->master->menu_slug['options'] );?>	
					<?php do_settings_sections( $this->master->menu_slug['options'] );?>
				</div>					
				<div style="margin-right:-300px; width: 280px; float:right;">
					<div id="submitdiv">
						<h3 class="hndle"><span><?php _e( 'Enregistrer', 'tify' );?></span></h3>
						<div style="padding:10px;">
							<div class="submit">
							<?php submit_button();?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	}	
}

/* = TABOOXES - FORMULAIRE DE SAISIE = */
/** == Réglages généraux == **/
class tiFy_Wistify_Options_Global_Taboox extends tiFy\Taboox\Form{
	/* = ARGUMENTS = */
	public 	// Paramètres
			$data_name 		= 'wistify_global_options',			
			$data_key 		= 'wistify_global_options';
			
	private	// Référence
			$master;			
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Wistify_Master $master ){
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
					<?php _e( 'Clé d\'API', 'tify' );?>
				</th>
				<td>
					<input type="text" name="<?php echo $this->data_name;?>[api_key]" value="<?php echo esc_attr( $this->data_value['api_key'] );?>" size="25" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Clé d\'API de test', 'tify' );?>
				</th>
				<td>
					<input type="text" name="<?php echo $this->data_name;?>[api_test_key]" value="<?php echo esc_attr( $this->data_value['api_test_key'] );?>" size="25" />
				</td>
			</tr>
		</tbody>
	</table>
	<?php
	}
}

/** == Formulaire d'inscription à la newsletter == **/
class tiFy_Wistify_Options_SubscribeForm_Taboox extends tiFy\Taboox\Form{
	/* = ARGUMENTS = */
	public 	// Paramètres
			$data_name 		= 'wistify_subscribe_form',
			$data_key 		= 'wistify_subscribe_form';				
			
	private	// Référence
			$master;			
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Wistify_Master $master ){
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
				<?php wistify_mailing_lists_dropdown( 
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


/** == Informations de contact == **/
class tiFy_Wistify_Options_ContactInformations_Taboox extends tiFy\Taboox\Form{
	/* = ARGUMENTS = */
	public 	$data_name 		= 'wistify_contact_information',
			$data_key 		= 'wistify_contact_information';			
			
	private	// Référence
			$master;			
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Wistify_Master $master ){
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