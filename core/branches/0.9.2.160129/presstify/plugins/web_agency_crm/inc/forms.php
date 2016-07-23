<?php
class tiFy_WebAgencyCRM_MainForms{
	/* = ARGUMENTS = */
	/* = ARGUMENTS = */
	public	// Paramètres
			$subscribe_form_id;
			
	private	// Références
				$master;

	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_WebAgencyCRM_Master $master ){
		// Déclaration des Références
		$this->master = $master;
		
		// Actions et Filtres PressTiFy
		add_action( 'tify_form_register', array( $this, 'tify_form_register' ) );	
	}
	
	/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == Déclaration des formulaires == **/	
	function tify_form_register(){		
		// Formulaire d'inscription
			$form = apply_filters( 'tify_wacrm_subscribe_form', array(
				'ID' 			=> 'tify_wacrm_subscribe',
				'title' 		=>  __( 'Formulaire d\'inscription à l\'espace client', 'tify' ),
				'prefix' 		=> 'tify_wacrm_subscribe_form',
				'form_class'	=> 'form-horizontal',
				'fields' 		=> array(
					array(
						'slug'				=> 'login',
						'label' 			=> __( 'Identifiant (obligatoire)', 'tify' ),
						'placeholder' 		=> __( 'Identifiant (obligatoire)', 'tify' ),
						'type' 				=> 'input',
						'required'			=> true,
						
						'container_class' 	=> 'form-group',
						'label_class' 		=> 'col-sm-2 control-label',
						'field_class' 		=> 'col-sm-10',
						
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => 'user_login' )
						)
					),
					array(
						'slug'			=> 'email',
						'label' 		=> __( 'E-mail (obligatoire)', 'tify' ),
						'placeholder' 	=> __( 'E-mail (obligatoire)', 'tify' ),
						'type' 			=> 'input',
						'required'		=> true,
						'integrity_cb'	=> 'is_email',
						
						'container_class' 	=> 'form-group',
						'label_class' 		=> 'col-sm-2 control-label',
						'field_class' 		=> 'col-sm-10',
						
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => 'user_email' )
						)
					),
					array(
						'slug'			=> 'company',
						'label' 		=> __( 'Nom de la société', 'tify' ),
						'placeholder' 	=> __( 'Nom de la société', 'tify' ),
						'type' 			=> 'input',
						'autocomplete'	=> 'off',
						'required'		=> true,
						
						'container_class' 	=> 'form-group',
						'label_class' 		=> 'col-sm-2 control-label',
						'field_class' 		=> 'col-sm-10',
						
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => 'nickname', 'updatable' => true )
						)
					),
					array(
						'slug'			=> 'reference',
						'label' 		=> __( 'Référence Client', 'tify' ),
						'placeholder' 	=> __( 'Référence Client', 'tify' ),
						'type' 			=> 'input',
						'autocomplete'	=> 'off',
						'required'		=> false,
						
						'container_class' 	=> 'form-group',
						'label_class' 		=> 'col-sm-2 control-label',
						'field_class' 		=> 'col-sm-10',
						
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => true, 'updatable' => true )
						)
					),
					array(
						'slug'			=> 'password',
						'label' 		=> __( 'Mot de passe (obligatoire)', 'tify' ),
						'placeholder' 	=> __( 'Mot de passe (obligatoire)', 'tify' ),
						'type' 			=> 'password',
						'autocomplete'	=> 'off',
						'required'		=> true,
						
						'container_class' 	=> 'form-group',
						'label_class' 		=> 'col-sm-2 control-label',
						'field_class' 		=> 'col-sm-10',
						
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => 'user_pass' )
						)
					),
					array(
						'slug'			=> 'confirm',
						'label' 		=> __( 'Confirmation de mot de passe (obligatoire)', 'tify' ),
						'placeholder' 	=> __( 'Confirmation de mot de passe (obligatoire)', 'tify' ),
						'type' 			=> 'password',
						'autocomplete'	=> 'off',
						'required'		=> true,
						'integrity_cb'	=> array( 
							'function' => 'compare', 
							'args' => array( '%%password%%' ), 
							'error' => __( 'Les champs "Mot de passe" et "Confirmation de mot de passe" doivent correspondre', 'tify' ) 
						),
						
						'container_class' 	=> 'form-group',
						'label_class' 		=> 'col-sm-2 control-label',
						'field_class' 		=> 'col-sm-10',
						
					)
				),
				'buttons'		=> array(
					'submit'		=> array( 
						'label' => ( ! is_user_logged_in() ? __( 'S\'inscrire', 'tify' ) : __( 'Mettre à jour', 'tify' ) 
						) 
					)
				), 
				'options' => array(
					'success' 	=> array(
						'message'	=> ! is_user_logged_in() ? __( 'Votre demande d\'inscription à l\'espace client a été enregistrée', 'tify' ) : __( 'Vos informations personnelles ont été mises à jour', 'tify' ),
						'display' 	=> ! is_user_logged_in() ? false : 'form', 
					)
				)
			)
		);	
		
		$form['add-ons']['user'] = array( 
			'roles' 		=> $this->master->roles, 
			'user_profile' 	=> true, 
			'edit_hookname' => array( 'espace-clients_page_tify_wacrm_customer', 'espace-clients_page_tify_wacrm_team' )
		);
		
		$this->subscribe_form_id = tify_form_register( $form );	
	}
}