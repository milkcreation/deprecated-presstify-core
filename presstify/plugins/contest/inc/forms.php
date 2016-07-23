<?php
Class tiFy_Contest_Forms{
	/* = ARGUMENTS = */
	public	// Paramètres
			$subscribe_form_id;
			
	private	// Références
			$master;
	
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Contest_Master $master ){
		// Déclaration des Références
		$this->master = $master;
		
		// Actions et Filtres PressTiFy
		add_action( 'tify_form_register', array( $this, 'tify_form_register' ) );
		add_action( 'tify_form_register_addon', array( $this, 'tify_form_register_addon' ) );		
	}
	
	/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == Déclaration des formulaires == **/	
	function tify_form_register(){		
		// Formulaire d'inscription
		$form = apply_filters( 'tify_contest_subscribe_form', array(
				'ID' 		=> 'tify_contest_subscribe',
				'title' 	=>  __( 'Formulaire d\'inscription au jeu concours', 'tify' ),
				'prefix' 	=> 'tify_contest_subscribe_form',
				'fields' => array(
					array(
						'slug'			=> 'login',
						'label' 		=> __( 'Identifiant', 'tify' ),
						'type' 			=> 'hidden',
						'required'		=> true,
						'value'			=> tify_generate_token(),
						'add-ons'		=> array(
							'user'				=> array( 'userdata' => 'user_login' )
						)
					),
					array(
						'slug'			=> 'fb_user_id',
						'label' 		=> __( 'Id Facebook', 'tify' ),
						'type' 			=> 'hidden',
						'add-ons'		=> array(
							'user'				=> array( 'userdata' => true )
						)
					),			
					array(
						'slug'			=> 'email',
						'label' 		=> __( 'Adresse email', 'tify' ),
						'placeholder' 	=> __( 'Adresse email...*', 'tify' ),
						'type' 			=> 'input',
						'required'		=> true,
						'onpaste'		=> false,
						'integrity_cb'	=> 'is_email',
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => 'user_email' )
						)
					),
					array(
						'slug'			=> 'email_confirm',
						'label' 		=> __( 'Confirmation email', 'tify' ),
						'placeholder' 	=> __( 'Confirmation email...*', 'tify' ),
						'type' 			=> 'input',
						'autocomplete'	=> 'off',
						'onpaste'		=> false,
						'required'		=> true,
						'integrity_cb'	=> array( 
							'function' => 'compare', 
							'args' => array( '%%email%%' ), 
							'error' => __( 'Les champs "Email" et "Confirmation email" doivent correspondre', 'tify' ) 
						)
					),
					array(
						'slug'			=> 'password',
						'label' 		=> __( 'Mot de passe', 'tify' ),
						'placeholder' 	=> __( 'Mot de passe...*', 'tify' ),
						'type' 			=> 'password',
						'autocomplete'	=> 'off',
						'onpaste'		=> false,
						'required'		=> true,
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => 'user_pass' )
						)
					),
					array(
						'slug'			=> 'password_confirm',
						'label' 		=> __( 'Confirmation de mot de passe', 'tify' ),
						'placeholder' 	=> __( 'Confirmation de mot de passe...*', 'tify' ),
						'type' 			=> 'password',
						'autocomplete'	=> 'off',
						'onpaste'		=> false,
						'required'		=> true,
						'integrity_cb'	=> array( 
							'function' => 'compare', 
							'args' => array( '%%password%%' ), 
							'error' => __( 'Les champs "Mot de passe" et "Confirmation de mot de passe" doivent correspondre', 'tify' ) 
						)
					),
					array(
						'slug'			=> 'lastname',
						'label' 		=> __( 'Nom', 'tify' ),
						'placeholder' 	=> __( 'Nom...*', 'tify' ),
						'type' 			=> 'input',
						'required'		=> true,
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => 'last_name' )
						)
					),
					array(
						'slug'			=> 'firstname',
						'label' 		=> __( 'Prénom', 'tify' ),
						'placeholder' 	=> __( 'Prénom...*', 'tify' ),
						'type' 			=> 'input',
						'required'		=> true,
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => 'first_name' )
						)
					),
					array(
						'slug'			=> 'zipcode',
						'label' 		=> __( 'Code postal', 'tify' ),
						'placeholder' 	=> __( 'Code postal...*', 'tify' ),
						'type' 			=> 'input',
						'required'		=> true,
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => true )
						)
					),
					array(
						'slug'			=> 'town',
						'label' 		=> __( 'Ville', 'tify' ),
						'placeholder' 	=> __( 'Ville...*', 'tify' ),
						'type' 			=> 'input',
						'required'		=> true,
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => true )
						)
					),
					array(
						'slug'			=> 'phone',
						'label' 		=> __( 'Telephone', 'tify' ),
						'placeholder' 	=> __( 'Telephone', 'tify' ),
						'type' 			=> 'input',
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => true )
						)
					),								
					array(
						'slug'			=> 'nickname',
						'label' 		=> __( 'Votre nom de chef', 'tify' ),
						'placeholder' 	=> __( 'Votre nom de chef...*', 'tify' ),
						'type' 			=> 'input',
						'required'		=> true,
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => 'nickname' )
						)
					),
					array(
						'slug'			=> 'captcha',
						'label' 		=> __( 'Code de sécurité', 'tify' ),
						'placeholder'	=> __( 'Code de sécurité*', 'tify' ),
						'type' 			=> 'simple-captcha-image'						
					),
					array(
						'slug'			=> 'terms_n_conditions',
						'label' 		=> __( 'Conditions générales', 'tify' ),
						'placeholder' 	=> __( 'Conditions générales...*', 'tify' ),
						'type' 			=> 'tify_checkbox',
						'choices'		=> array(
							sprintf( 
								__( 'J\'ai lu et j\'accepte les %s', 'tify' ), 
								tify_modal_toggle(
									'cgu',
									array(
										'text' 				=> 	__( 'conditions générales d\'utilisation' ),
										'echo' 				=> 	false,
										'autoload'			=> 	false								
									) 
								) 
							)
						),
						'required'		=> __( 'Merci d\'accepter les conditions générales d\'utilisation', 'tify' ),
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => true )
						)
					),
					array(
						'slug'			=> 'newsletter',
						'label' 		=> __( 'Inscription à la lettre d\'informations', 'tify' ),
						'placeholder' 	=> __( 'Inscription à la lettre d\'informations', 'tify' ),
						'type' 			=> 'tify_checkbox',
						'choices'		=> array(
							__( 'Je désire recevoir des informations par email', 'tify' )
						),
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => true )
						)
					),
					array(
						'slug'			=> 'fb_infos_query',
						'type'			=> 'html',
						'html'			=> 	"<ul class=\"list-2-items-by-row fb_actions subscribe\">\n".
											"\t<li class=\"info\">\n".
												__( 'Inscrivez-vous avec<br>votre compte Facebook', 'tify' ).
											"\t</li>\n".
											"\t<li class=\"button\">\n".
												tify_facebook_sdk_login_button( 
													array( 
														'text' 		=> __( 'Inscription avec Facebook', 'deficl' ) ."<span class=\"tify_spinner white\"></span>",
														'class'		=> 'fb_subscribe',
														'ajax_api'	=> true,
														'echo'		=> false 
													)				
												).
											"\t</li>\n".
											"</ul>\n"	
						
					)
				), 
				'options' => array(					
					'success' 	=> array(
						'message'	=>  __( 'Votre demande d\'inscription a été enregistrée, merci de vous identifier pour participer.', 'tify' )
					)
				),
				'buttons' => array(
					'submit' => __( 'Je valide mon inscription et je poste ma recette', 'tify' ),
				),	
				'add-ons' => array(
					'user' => array(
						'roles' 		=> $this->master->roles, 
						'user_profile' 	=> true,
						'edit_hookname'	=> 'jeux-concours_page_tify_contest_participant'
					)
				)
			)
		);
		$this->subscribe_form_id = tify_form_register( $form );		
		
		// Formulaire de soumission de vote
		tify_form_register( 
			array(
				'ID' 				=> 'tify_contest_poll_submit',
				'title' 			=>  __( 'Formulaire de participation au jeu concours', 'tify' ),
				'prefix' 			=> 'tify_contest_poll_submit',
				'container_class' 	=> 'tify_form_container tify_contest_contest_form-recipe',				
				'fields' 			=> array(
					array(
						'slug'			=> 'email',
						'label' 		=> __( 'Email', 'tify' ),
						'placeholder' 	=> __( 'Renseignez votre email...*', 'tify' ),
						'type' 			=> is_user_logged_in() ? 'hidden': 'input',
						'value'			=> is_user_logged_in() ? wp_get_current_user()->user_email : '',
						'required'		=> true
					)
				),
				'options' 			=> array( 'success' => array( 'message' => __( 'Un email de validation vous a été adressé, cliquez sur le lien de confirmation pour valider votre vote', 'tify' ) ) ),
				'buttons'			=> array( 'submit' => __( 'Voter', 'tify') ),
				'add-ons' 			=> array(
					'tify_contest_poll_record'
				)
			)
		);		
		
		// Formulaires de participation
		foreach( (array) $this->master->registred_contest as $contest_id => $params )
			tify_form_register( $params['form'] );
	}
	
	/** == Déclaration des addons de formulaire == **/
	function tify_form_register_addon(){
		tify_form_register_addon( 'tify_contest_participation_record', 'tiFy_Contest_ParticipationRecord', null, $this->master );
		tify_form_register_addon( 'tify_contest_poll_record', 'tiFy_Contest_PollRecord', null, $this->master );
	}
}

/* = ADDONS DE FORMULAIRE = */
/** == Enregistrement des participations == **/
Class tiFy_Contest_ParticipationRecord extends tiFy_Forms_Addon{
	/* = ARGUMENTS = */
	private	// Paramètres
			$insert_id = 0,		// Id de la participation enregistrée
			// Référence
			$tify_contest;
	
	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Forms $master, tiFy_Contest_Master $tify_contest ){
		// Définition des options de formulaire par défaut
		$this->default_form_options = array( );
		
		// Définition des options de champ de formulaire par défaut
		$this->default_field_options = array( );
		
		// Définition des fonctions de callback
		$this->callbacks = array(
			'handle_submit_request'	=> array( $this, 'cb_handle_submit_request' ),
			'handle_redirect'		=> array( $this, 'cb_handle_redirect' )
		);
		
        parent::__construct( $master );	
		
		// Déclaration de la classe de référence
		$this->tify_contest = $tify_contest;
    }

	/** == == **/
	public function cb_handle_submit_request( &$parsed_request, $original_request ){
		$datas = array( 
			'part_contest_id'	=> $parsed_request['form_id'],
			'part_user_id'		=> get_current_user_id(),
			'part_session'		=> $parsed_request['session'],
			'part_status'		=> 'moderate',
			'part_date'			=> current_time('mysql'),
			'meta'				=> array_map( array( $this->master->functions, 'base64_decode' ), $parsed_request['values'] )
		);

		$this->insert_id = $this->tify_contest->db_participation->insert_item( $datas );
	}
	
	/** == == **/
	public function cb_handle_redirect( &$location ){
		if( $this->insert_id )
			$location = add_query_arg( array_merge( $this->master->handle->get_results_arg(), array( 'tify_template' => 'participation', 'tify_contest_part' => $this->insert_id ) ), site_url( '/' ) );
	}
}

/** == Enregistrement des votes == **/
Class tiFy_Contest_PollRecord extends tiFy_Forms_Addon{
	/* = ARGUMENTS = */
	private	// Référence
			$tify_contest;
	
	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Forms $master, tiFy_Contest_Master $tify_contest ){
		// Définition des options de formulaire par défaut
		$this->default_form_options = array( );
		
		// Définition des options de champ de formulaire par défaut
		$this->default_field_options = array( );
		
		// Définition des fonctions de callback
		$this->callbacks = array(
			'handle_submit_request'		=> array( $this, 'cb_handle_submit_request' ),
			'handle_before_redirect'	=> array( $this, 'cb_handle_before_redirect' ),
		);
		
        parent::__construct( $master );	
		
		// Déclaration de la classe de référence
		$this->tify_contest = $tify_contest;
		
		// Actions et Filtres Wordpress
		add_action( 'wp', array( $this, 'wp' ), 11 );
    }
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	public function wp(){
		if( ! isset( $_REQUEST['tify_contest_poll_token'] ) )
			return;
		if( ! isset( $_REQUEST['action'] ) || ! in_array( $_REQUEST['action'], array( 'validate', 'invalidate') ) )
			return;		
		if( ! get_query_var( 'tify_contest_part', 0 ) )
			return;
		
		$activation_key = $_REQUEST['tify_contest_poll_token'];
		$action = $_REQUEST['action'];
		$part_id = (int) get_query_var( 'tify_contest_part', 0 );
		
		if( ! $poll = $this->tify_contest->db_poll->get_item( array( 'poll_part_id' => $part_id, 'activation_key' => $activation_key, 'active' => 0 ) ) )
			return;

		if( $action === 'validate' )
			$this->tify_contest->db_poll->update_item( $poll->poll_id, array( 'active' => 1 ) );
		elseif( $action === 'invalidate' )
			$this->tify_contest->db_poll->delete_item( $poll->poll_id );
		
	}

	/* = FONCTION DE RAPPELS TiFy_Forms = */
	/** == == **/
	public function cb_handle_submit_request( &$parsed_request, $original_request ){
		$current_user = wp_get_current_user();
		$data = array( 
			'poll_part_id'			=> (int) get_query_var( 'tify_contest_part', 0 ),
			'poll_date'				=> current_time( 'mysql' ),
			'poll_user_id'			=> $current_user->ID,
			'poll_user_remote_addr'	=> ! empty( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '',
			'poll_user_email'		=> ! empty( $parsed_request['values']['email'] ) ? esc_attr( $parsed_request['values']['email'] ) : '',
			'poll_http_referer'		=> ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
			'poll_activation_key'	=> tify_generate_token( 32 ),
			'poll_active'			=> is_user_logged_in() ? 1 : 0
		);

		// Vérification des données
		if( ! current_user_can( 'tify_contest_poll', $data['poll_part_id'] ) )
			return $this->master->errors->errors[ $parsed_request['form_id'] ] =  array( array( tify_contest_error_message() ) );
		elseif( $current_user->user_email && (  $current_user->user_email !=  $data['poll_user_email'] ) )
			$data = new WP_Error( 'tify_contest_poll_missing_part', __( 'Votre vote ne peut être pris en compte : L\'email fourni diffère de celui de l\'utilisateur connecté', 'tify' ) );
		elseif( empty( $data['poll_part_id'] ) || ! $this->tify_contest->db_participation->get_item_by_id( $data['poll_part_id'] ) )
			$data = new WP_Error( 'tify_contest_poll_missing_part', __( 'Votre vote ne peut être pris en compte : La participation n\'est pas valide', 'tify' ) );
		elseif( empty( $data['poll_user_remote_addr'] ) )
			$data = new WP_Error( 'tify_contest_poll_missing_ip', __( 'Votre vote ne peut être pris en compte : Le système n\'est pas en mesure de définir l\'origine de votre vote', 'tify' ) );
		elseif( ! is_email( $data['poll_user_email'] ) )
			$data = new WP_Error( 'tify_contest_poll_email_invalid', __( 'Votre vote ne peut être pris en compte : L\'email fourni n\'est pas valide', 'tify' ) );
		elseif( $this->tify_contest->db_poll->count_items( array( 'part_id' => $data['poll_part_id'], 'user_email' => $data['poll_user_email'] ) ) >= 1 )
			$data = new WP_Error( 'tify_contest_poll_max_attempt', __( 'Votre vote ne peut être pris en compte : Le nombre de vote maximum atteint pour cet adresse email', 'tify' ) );
		
		if( is_wp_error( $data ) )
			return $this->master->errors->errors[ $parsed_request['form_id'] ] = array( array( $data->get_error_message() ) );
		
		$this->poll_id = $this->tify_contest->db_poll->insert_item( $data );
	}
	
	/** == Traitement avant la redirection == **/
	public function cb_handle_before_redirect( &$parsed_request, $original_request ){
		if( ! $poll = $this->tify_contest->db_poll->get_item_by_id( $this->poll_id ) )
			return;
		if( $poll->poll_active == 1 )
			return;
		
		// Envoi du mail
		tify_require( 'mailer' );
		// Préparation du mail
		$validate_link 		= add_query_arg( array( 'tify_template' => 'participation', 'tify_contest_part' => $poll->poll_part_id, 'tify_contest_poll_token' => $poll->poll_activation_key, 'action' => 'validate' ), site_url( '/' ) );
		$invalidate_link 	= add_query_arg( array( 'tify_template' => 'participation', 'tify_contest_part' => $poll->poll_part_id, 'tify_contest_poll_token' => $poll->poll_activation_key, 'action' => 'invalidate' ), site_url( '/' ) );
		$tiFy_Mailer = new tiFy_Mailer( 
			array(
				'to'   			=> 	array( 'email' => $poll->poll_user_email ),
				'from' 			=> 	array( 'name' => get_bloginfo('blogname'), 'email' => 'noreply@ledefidecyril.fr' ),				
				'subject' 		=> 	__( get_bloginfo('blogname').' | Validez votre vote', 'deficl' ),
				'html' 			=> 	"<table width=\"600\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n".
										"<tbody>\n".
											"<tr>\n".
												"<td style=\"width:600px;\">\n".
													"<img src=\"". get_template_directory_uri() ."/images/mail-header-poll-min.png\" style=\"width:100%; margin-bottom:50px;\" />\n".
												"</td>\n".
											"</tr>\n".	
											"<tr>\n".
												"<td style=\"width:600px;\">\n".
													"<p class=\"century\" style=\"color:#000000;letter-spacing:1px;margin-bottom:5px;\">\n".
														__( 'Nous avons bien reçu votre vote, cliquez sur le lien suivant pour valider celui-ci :', 'deficl' ) ."\n".
													"</p>\n".
													"<p style=\"margin-bottom:30px;\">\n".
														"<a href=\"{$validate_link}\" class=\"century\" style=\"font-size:13px;line-height:1.1;\">{$validate_link}</a>\n".
													"</p>\n".
													"<br>\n".
												"</td>\n".
											"</tr>\n".
											"<tr>\n".
									 			"<td style=\"width:600px;\">\n".
													"<p class=\"century\" style=\"color:#000000;letter-spacing:1px;margin-bottom:5px;\">\n".
														__( 'Si toutefois vous n\'étiez pas à l\'origine de ce vote, cliquez sur le lien suivant pour l\'annuler :', 'deficl' ) ."\n".
													"</p>\n".
													"<p style=\"margin-bottom:30px;\">\n".
														"<a href=\"{$invalidate_link}\" class=\"century\" style=\"font-size:13px;line-height:1.1;\">{$invalidate_link}</a>\n".
													"</p>\n".
												"</td>\n".
					 						"</tr>\n".	
										"</tbody>\n".
									"</table>\n",
				'custom_css'	=> 	"<style type=\"text/css\">\n".
        								".century {\n".
        									"font-family:'Century Gothic',CenturyGothic,AppleGothic,sans-serif;\n".
											"font-size:14px;\n".
											"font-style:normal;\n".
											"font-variant:normal;\n".
											"font-weight:500;\n".
											"line-height:1.2;\n".
        								"}\n".  
									"</style>\n"
			) 
		);
	}
}