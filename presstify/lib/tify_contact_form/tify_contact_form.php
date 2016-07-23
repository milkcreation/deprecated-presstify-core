<?php
require_once( dirname( __FILE__) .'/helpers.php' );
class tiFy_ContactForm{
	public	// Paramètres
			$form_id,
			$form_before 	= '',		// HTML affiché avant le formulaire. Ajouter %s pour afficher le contenu de la page.
			$form_after 	= '',		// HTML affiché après le formulaire. Ajouter %s pour afficher le contenu de la page.
			$form_fields	= array(),	// Champs de formulaire
			$form_options	= array(),	// Options de formulaire
			$form_addons	= array();	// Addons de formulaire			
			
	protected static $instance = 0;
	
	private $id;
			
	/* = CONSTRUCTEUR = */
	public function __construct( $id = null ){
		$this->id = ( ! empty( $id ) ) ? $id : ( is_subclass_of( $this, __CLASS__ ) ? get_class( $this ) :  get_class() .'-'. ++self::$instance );

		// Configuration
		$this->set_form_ID();
		$this->set_form_fields();
		$this->set_form_options();
		$this->set_form_addons();
		
		// Instanciation du Helpers
		global $tify_contact_form;		
		if( ! $tify_contact_form instanceof tiFy_ContactFormHelpers )
			$tify_contact_form = new tiFy_ContactFormHelpers;
		
		// 
		$tify_contact_form->{$this->id} = $this;		
						
		// Actions et Filtres Wordpress
		add_filter( 'the_content', array( $this, 'wp_the_content' ) );
		
		// Actions et Filtre PressTiFy
		/// Réglages des options
		add_action( 'tify_options_register_node', array( $this, 'tify_options_register_node' ) );		
		add_action( 'tify_taboox_register_form', array( $this, 'tify_taboox_register_form' ) );	
		/// Formulaire
		add_action( 'tify_form_register', array( $this, 'tify_form_register' ) );
	}
	
	/* = CONFIGURATION = */	
	/** == Définition de l'ID du formulaire == **/
	private function set_form_ID(){
		$this->form_id = 'form-'. $this->id;
	}
	
	/** == Définition des champs de formulaire == **/
	private function set_form_fields(){
		$this->form_fields = array(
			array(
				'slug'			=> 'lastname',
				'label' 		=> __( 'Nom', 'tify' ),
				'placeholder'	=> __( 'Renseignez votre nom', 'tify' ),
				'type' 			=> 'input',
				'required'		=> true,
				'add-ons'		=> array(
					'record' => array(
						'column' => true
					)
				)
			),
			array(
				'slug'			=> 'firstname',
				'label' 		=> __( 'Prénom', 'tify' ),
				'placeholder'	=> __( 'Renseignez votre prénom', 'tify' ),
				'type' 			=> 'input',
				'required'		=> true,
				'add-ons'		=> array(
					'record' => array(
						'column' => true
					)
				)
			),
			array(
				'slug'			=> 'email',
				'label' 		=> __( 'Adresse mail', 'tify' ),
				'placeholder'	=> __( 'Indiquez votre adresse email', 'tify' ),
				'integrity_cb'	=> 'is_email',
				'type' 			=> 'input',
				'required'		=> true,
				'add-ons'		=> array(
					'record' => array(
						'column' => true
					)
				)
			),
			array(
				'slug'			=> 'subject',
				'label' 		=> __( 'Sujet du message', 'tify' ),
				'placeholder'	=> __( 'Sujet de votre message', 'tify' ),
				'type' 			=> 'input',
				'required'		=> true,
				'add-ons'		=> array(
					'cookie_transport' 	=> array( 'ignore' => true ),					
					'record' 			=> array(
						'column' => true
					)
				)
			),	
			array(
				'slug'			=> 'message',
				'label' 		=> __( 'Message', 'tify' ),
				'placeholder'	=> __( 'Votre message', 'tify' ),
				'type' 			=> 'textarea',
				'required'		=> true,
				'add-ons'		=> array(
					'cookie_transport' 	=> array( 'ignore' => true ),
					'record' 			=> array(
						'column' => true
					)
				)						
			),	
			array(
				'slug'			=> 'captcha',
				'label' 		=> __( 'Code de sécurité', 'tify' ),
				'placeholder'	=> __( 'Code de sécurité', 'tify' ),
				'type' 			=> 'simple-captcha-image',					
			)
		);
	}

	/** == Déclaration des options du formulaire == **/
	private function set_form_options(){
		$this->form_options = array();
	}

	/** == Déclaration des addons du formulaire == **/
	private function set_form_addons(){
		$this->form_addons = array(
			'record',
			'cookie_transport',
			'mailer' => array(					
				'debug' => false,
				'confirmation' => array(
					'send' 		=> ( get_option( $this->id .'-confirmation', 'on' ) === 'on' ) ? true : false,
					'from' 		=> get_option( $this->id .'-sender' ),
					'to' 		=> array( array( 'email' => '%%email%%', 'name' => '%%firstname%% %%lastname%%' ) ),			
					'subject' 	=> __( get_bloginfo( 'blogname' ).' | Votre message a bien été réceptionné', 'tify' )
				),
				'notification' => array(
					'send' 		=> ( get_option( $this->id .'-notification', 'off' ) === 'on' ) ? true : false,
					'from' 		=> array( 'name' => get_bloginfo( 'blogname' ), 'email' => get_option( 'admin_email' ) ),			
					'to' 		=> get_option( $this->id .'-recipients' ),
					'subject' 	=> __( get_bloginfo( 'blogname' ).' | Vous avez reçu une nouvelle demande de contact', 'tify' )
				)
			)
		);
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Affichage du formulaire de contact == **/
	public function wp_the_content( $content ){
		if( ! is_singular() )
			return $content;
		if( get_option( 'page_for_'. $this->id, 0 ) != get_the_ID() )
			return $content;
				
		return $this->display( $content, false );
	}
	
	/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == OPTIONS == **/
	/*** === Déclaration de l'entrée de gestion des options de formulaire === ***/
	public function tify_options_register_node(){
		tify_options_register_node(
			array(
				'id' 		=> 'tify_options_node-'. $this->id,
				'title' 	=> __( 'Formulaire de contact', 'tify' ),
				'cb'		=> 'tiFy_ContactForm_Taboox',
			)
		);
	}
		
	/*** === Déclaration de la boîte de saisie des options === ***/
	public function tify_taboox_register_form(){
		tify_taboox_register_form( 'tiFy_ContactForm_Taboox', $this );
	}
	
	/** == FORMULAIRES == **/
	/** == Déclaration du formulaire de contact == **/
	public function tify_form_register(){
		tify_form_register(		
			array(
				'ID' 				=> $this->form_id,
				'title' 			=> __( 'Formulaire de contact', 'tify' ),
				'prefix' 			=> 'tify_contact_form',
				'container_class'	=> 'tify_form_container tify_contact_form',
				'fields' 			=> apply_filters( 'tify_contact_form_fields-'. $this->id, apply_filters( 'tify_contact_form_fields', $this->form_fields ) ),
				'options' 			=> apply_filters( 'tify_contact_form_options-'. $this->id, apply_filters( 'tify_contact_form_options', $this->form_options ) ),
				'buttons'			=> apply_filters( 'tify_contact_form_buttons-'. $this->id, apply_filters( 'tify_contact_form_buttons', array() ) ),
				'add-ons' 			=> apply_filters( 'tify_contact_form_addons-'. $this->id, apply_filters( 'tify_contact_form_addons', $this->form_addons ) ) 
			)
		);
	}
	
	/* = CONTROLEUR = */
	/** == Récupération de l'identifiant de la classe == **/
	final public function get_id(){
		return $this->id;
	}
	
	/* = AFFICHAGE = */
	/** == Formulaire == **/
	final public function display( $content, $echo = true ){
		$output  = "";
		$output .= sprintf( apply_filters( 'tify_contact_form_before-'. $this->id, apply_filters( 'tify_contact_form_before', $this->form_before ) ), $content );
		$output .= tify_form_display( $this->form_id, false );
		$output .= sprintf( apply_filters( 'tify_contact_form_after-'. $this->id, apply_filters( 'tify_contact_form_after', $this->form_after ) ), $content );
		
		if( $echo )
			echo $output;
		else
			return $output;
	}		
}

/* = FORMULAIRE DE SAISIE DES OPTIONS = */
class tiFy_ContactForm_Taboox extends tiFy_Taboox{
	/* = ARGUMENTS = */
	private $master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( $master ){	
		parent::__construct();
		
		$this->master = $master;	
	}
	
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init(){
		register_setting( $this->page, 'page_for_'. $this->master->get_id() );
		register_setting( $this->page, $this->master->get_id() .'-confirmation' );
		register_setting( $this->page, $this->master->get_id() .'-sender', array( $this, 'sanitize_sender' ) );
		register_setting( $this->page, $this->master->get_id() .'-notification' );
		register_setting( $this->page, $this->master->get_id() .'-recipients', array( $this, 'sanitize_recipients' ) );		
	}
	
	/* = CHARGEMENT DE LA PAGE = */
	public function current_screen( $screen ){
		tify_control_enqueue( 'switch' );
		tify_control_enqueue( 'dynamic_inputs' );
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form( $_args = array() ){
	?>
		<h3><?php _e( 'Affichage du formulaire', 'tify' );?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e( 'Page  d\'affichage du formulaire de contact', 'tify' ); ?></th>
					<td>
					<?php 
						wp_dropdown_pages(
							array(
								'name' => 'page_for_'. $this->master->get_id(),
								'post_type' => 'page',
								'selected' => get_option( 'page_for_'. $this->master->get_id(), 0 ),
								'show_option_none' => __( 'Aucune page choisie', 'tify' ),
								'sort_column'  => 'menu_order' 
							)
						);
					?>
					</td>
				</tr>
			</tbody>
		</table>

		<h3><?php _e( 'Message de confirmation de réception de la demande', 'tify' );?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e( 'Envoyer un message de confirmation de réception à l\'utilisateur', 'tify' );?></th>
					<td><?php tify_control_switch( array( 'name' => $this->master->get_id() .'-confirmation', 'checked' => get_option( $this->master->get_id() .'-confirmation', 'on' ) ) );?>
				</td>
				
				<?php $s = get_option( $this->master->get_id() .'-sender' );?>
				<?php $value['email'] = ! empty( $s['email'] ) ?  $s['email'] : get_option( 'admin_email' ); $value['name'] = ! empty( $s['name'] ) ? $s['name'] : '';?>
				<tr>
					<th scope="row"><?php _e( 'Email de l\'expéditeur (requis)', 'tify' );?></th>
					<td>
						<div class="tify_input_email">
							<input type="text" name="<?php echo $this->master->get_id();?>-sender[email]" placeholder="<?php _e( 'Email (requis)', 'tify' );?>" value="<?php echo $value['email'];?>" size="40" autocomplete="off">
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Nom de l\'expéditeur (optionnel)', 'tify' );?></th>
					<td>
						<div class="tify_input_user">
							<input type="text" name="<?php echo $this->master->get_id();?>-sender[name]" placeholder="<?php _e( 'Nom (optionnel)', 'tify' );?>" value="<?php echo $value['name'];?>" size="40" autocomplete="off">
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<h3><?php _e( 'Message de notification aux administrateurs', 'tify' );?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e( 'Envoyer un message de notification aux administrateurs du site', 'tify' );?></th>
					<td><?php tify_control_switch( array( 'name' => $this->master->get_id() .'-notification', 'checked' => get_option( $this->master->get_id() .'-notification', 'off' ) ) );?>
				</td>
			</tbody>
		</table>
		<hr>
		<?php 
			tify_control_dynamic_inputs( 
				array( 
					'default' 			=> array( 'name'=> '', 'email' => get_option( 'admin_email' ) ),
					'add_button_txt'	=> __( 'Ajouter un destinataire', 'tify' ),
					'values' 			=> get_option( $this->master->get_id() .'-recipients' ), 
					'name' 				=> $this->master->get_id() .'-recipients',
					'sample_html'		=> 	
						"<table class=\"form-table\">\n".
						"\t<tbody>\n".
						"\t\t<tr>\n".
						"\t\t\t<th scope=\"row\">". __( 'Email du destinataire (requis)', 'tify' ) ."</th>\n".
						"\t\t\t<td>\n".
						"\t\t\t\t<div class=\"tify_input_email\">\n".
						"\t\t\t\t\t<input type=\"text\" name=\"%%name%%[%%index%%][email]\" value=\"%%value%%[email]\" placeholder=\"". __( 'Email de l\'expéditeur', 'tify' ) ."\" size=\"40\" autocomplete=\"off\">\n".
						"\t\t\t\t</div>\n".
						"\t\t\t</td>\n".
						"\t\t</tr>\n".
						"\t\t<tr>\n".
						"\t\t\t<th scope=\"row\">". __( 'Nom du destinataire (optionnel)', 'tify' ) ."</th>\n".
						"\t\t\t<td>\n".
						"\t\t\t\t<div class=\"tify_input_user\">\n".
						"\t\t\t\t\t<input type=\"text\" name=\"%%name%%[%%index%%][name]\" value=\"%%value%%[name]\" placeholder=\"". __( 'Nom de l\'expéditeur', 'tify' ) ."\" size=\"40\" autocomplete=\"off\">\n".
						"\t\t\t\t</div>\n".
						"\t\t\t</td>\n".
						"\t\t</tr>\n".	
						"\t</tbody>\n".
						"</table>"	 
				) 
			);
		?>
	<?php
	}
	
	/* = INTEGRITE = */
	/** == Vérification du format de l'email de l'expéditeur == **/
	function sanitize_sender( $sender ){
		if( empty( $sender['email'] ) ) :
			add_settings_error( $this->page, 'sender-email_empty', sprintf( __( 'L\'email "%s" ne peut être vide', 'tify' ), __( 'Expéditeur du message de confirmation de reception', 'tify' ) ) ); 
		elseif( ! is_email( $sender['email'] ) ) :
			add_settings_error( $this->page, 'sender-email_format', sprintf( __( 'Le format de l\'email "%s" n\'est pas valide' ), __( 'Expéditeur du message de confirmation de reception', 'tify' ) ) ); 
		endif;
		
		return $sender;
	} 
	
	/** == Vérification du format de l'email du destinataire de notification == **/
	function sanitize_recipients( $recipients ){
		foreach( (array) $recipients as $recipient => $recip ) :
			if( empty( $recip['email'] ) ) :
				add_settings_error( $this->page, $recipient .'-email_empty', sprintf( __( 'L\'email du destinataire des messages de notification #%d ne peut être vide', 'tify' ), $recipient+1 ) ); 
			elseif( ! is_email( $recip['email'] ) ) :
				add_settings_error( $this->page, $recipient .'-email_format', sprintf( __( 'Le format de l\'email du destinataire des messages de notification #%d n\'est pas valide', 'tify' ), $recipient+1 ) ); 
			endif;
		endforeach;
		
		return $recipients;
	}
}