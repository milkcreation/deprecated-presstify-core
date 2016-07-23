<?php
class tiFy_Wistify_Forms_Main{
	/* = ARGUMENT = */
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Wistify_Master $master ){
		// Déclaration des Références
		$this->master = $master;
							
		// Actions et Filtres PressTiFy
		add_action( 'tify_form_register', array( $this, 'tify_form_register' ) );
		add_action( 'tify_form_register_addon', array( $this, 'tify_form_register_addon' ) );
	}
	
	/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == Déclaration des formulaires == **/	
	function tify_form_register(){	
		// Formulaire d'inscription à la newsletter
		tify_form_register( 
			array(
				'ID' 				=> 'tify_wistify_subscribe',
				'title' 			=>  __( 'Formulaire d\'inscription à la newsletter', 'tify' ),
				'prefix' 			=> 'tify_contest_poll_submit',
				'container_class' 	=> 'tify_form_container tify_contest_contest_form-recipe',				
				'fields' 			=> array(
					array(
						'slug'			=> 'email',
						'label' 		=> $this->master->options->get( 'wistify_subscribe_form', 'label' ),
						'placeholder' 	=> $this->master->options->get( 'wistify_subscribe_form', 'placeholder' ),
						'type' 			=> 'input',
						'value'			=> is_user_logged_in() ? wp_get_current_user()->user_email : '',
						'integrity_cb'	=> 'is_email',
						'required'		=> true
					)
				),
				'options' 			=> array( 
					'success' => array( 'message' => $this->master->options->get( 'wistify_subscribe_form', 'success' ) )
				),
				'buttons'			=> array( 'submit' => array( 'label' => $this->master->options->get( 'wistify_subscribe_form', 'button' ) ) ),
				'add-ons' 			=> array(
					'tify_wistify_subscribe'
				)
			)
		);		
	}
	
	/** == Déclaration des addons de formulaire == **/
	function tify_form_register_addon(){
		tify_form_register_addon( 'tify_wistify_subscribe', 'tiFy_Wistify_Forms_Addon' );
	}
}

/** == Enregistrement des votes == **/
Class tiFy_Wistify_Forms_Addon extends tiFy_Forms_Addon{
	/* = ARGUMENTS = */
	public	// Paramètres
			$list_id = 0,
			$subscriber_id = 0;
	
	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Forms $master ){
		// Définition des options de formulaire par défaut
		$this->default_form_options = array( );
		
		// Définition des options de champ de formulaire par défaut
		$this->default_field_options = array( );
		
		// Définition des fonctions de callback
		$this->callbacks = array(
			'handle_check_request'		=> array( $this, 'cb_handle_check_request' ),
			'handle_submit_request'		=> array( $this, 'cb_handle_submit_request' ),
			'handle_before_redirect'	=> array( $this, 'cb_handle_before_redirect' ),
		);
		
		parent::__construct( $master );
    }
	
	/* = FONCTION DE RAPPELS TIFY_FORMS = */
	/** == == **/
	public function cb_handle_check_request( &$errors, $field ){
		global $wistify;
		
		$this->list_id = $wistify->options->get( 'wistify_subscribe_form', 'list_id' );
		
		// Vérification si l'adresse email fournie correspond à un utilisateur habilité (qui n'est pas à la corbeille)
		if( $wistify->db_subscriber->count_items( array( 'email' => $field['value'], 'status' => 'trash' ) ) )
			return $errors[] = __( 'Désolé vous n\'êtes pas autorisé à vous inscrire avec cette adresse email', 'tify' );
		// Vérification si l'adresse email fournie correspond à un utilisateur inscrit à la newsletter
		if( $wistify->db_subscriber->count_items( array( 'email' => $field['value'], 'list_id' => $this->list_id, 'active' => -1 ) ) )
			return $errors[] = __( 'Une demande d\'inscription à la newsletter est déjà enregistrée pour cette adresse email', 'tify' );
		// Vérification si l'adresse email fournie correspond à un utilisateur inscrit à la newsletter
		if( $wistify->db_subscriber->count_items( array( 'email' => $field['value'], 'list_id' => $this->list_id, 'active' => 1 ) ) )
			return $errors[] = __( 'Cette adresse email est déjà inscrite à la newsletter', 'tify' );
	}
	
	/** == == **/
	public function cb_handle_submit_request( &$parsed_request, $original_request ){
		global $wistify;
		
		$email = $parsed_request['fields']['email']['value'];
				
		// Récupération de l'utilisateur exitant
		if( ! $subscriber = $wistify->db_subscriber->get_item( array( 'email' => $email ) ) ) :
			$data = array( 
				'subscriber_uid'		=> tify_generate_token( 32 ),
				'subscriber_email'		=> $email,
				'subscriber_date'		=> current_time( 'mysql' )
			);
			$this->subscriber_id = (int) $wistify->db_subscriber->insert_item( $data );
		else :
			$this->subscriber_id = (int) $subscriber->subscriber_id;
		endif;
		
		$wistify->db_list_rel->insert_subscriber_for_list( (int) $this->subscriber_id, (int) $this->list_id, -1 );
	}
	
	/** == Traitement avant la redirection == **/
	public function cb_handle_before_redirect( &$parsed_request, $original_request ){
		global $wistify;		
		
		$subscriber_uid = $wistify->db_subscriber->get_item_var_by_id( $this->subscriber_id, 'uid' );
		$list_uid		= $wistify->db_list->get_item_var_by_id( $this->list_id, 'uid' );
		
		$validate_link 		= add_query_arg( array( 'u' => $subscriber_uid, 'l' => $list_uid ), site_url( '/wistify/subscribe_list' ) );
		$invalidate_link 	= add_query_arg( array( 'u' => $subscriber_uid, 'l' => $list_uid ), site_url( '/wistify/unsubscribe_list' ) );
		
		$message = "";	
		$message .= 	"<table width=\"600\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">";
		$message .= 		"<tbody>";
		$message .= 			"<tr>";
		$message .= 				"<td style=\"width:600px;\">";
		$message .= 					"<p>";
		$message .= 						__( 'Nous avons bien reçu votre demande d\'inscription à la newsletter, cliquez sur le lien suivant pour valider celle-ci :', 'tify' );
		$message .= 					"</p>";
		$message .= 					"<p style=\"margin-bottom:30px;\">";
		$message .= 						"<a href=\"{$validate_link}\" style=\"font-size:13px;line-height:1.1;\">{$validate_link}</a>";
		$message .= 					"</p>";
		$message .= 				"</td>";
		$message .= 			"</tr>";
		$message .= 			"<tr>";
		$message .= 				"<td style=\"width:600px;\">";
		$message .= 					"<p>";
		$message .= 						__( 'Si toutefois vous n\'étiez pas à l\'origine de cette demande, cliquez sur le lien suivant pour l\'annuler :', 'tify' ) ."";
		$message .= 					"</p>";
		$message .= 					"<p style=\"margin-bottom:30px;\">";
		$message .= 						"<a href=\"{$invalidate_link}\" style=\"font-size:13px;line-height:1.1;\">{$invalidate_link}</a>";
		$message .= 					"</p>";
		$message .= 				"</td>";
		$message .= 			"</tr>";	
		$message .= 		"</tbody>";
		$message .= 	"</table>";		
		
		tify_require_lib( 'mailer' );
		$tiFy_Mailer = new tiFy_Mailer( 
			array( 
				'from' 		=> array( 'name' => get_bloginfo('blogname'), 'email' => get_option( 'admin_email' ) ),
				'to'   		=> array( 'email' => $wistify->db_subscriber->get_item_var_by_id( $this->subscriber_id, 'email' ) ),
				'subject' 	=> __( get_bloginfo('blogname').' | Validez votre inscription à la newsletter', 'deficl' ),
				'html' 		=> $message
			)
		);
		$tiFy_Mailer->send();
		exit;
	}
}