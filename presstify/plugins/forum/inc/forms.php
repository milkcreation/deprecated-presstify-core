<?php
class tiFy_Forum_FormsMain{
	/* = ARGUMENTS = */
	public	// Paramètres
			$subscribe_form_id;
			
	private	// Références
			$master;
	
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Forum_Master $master ){
		// Déclaration des Références
		$this->master = $master;
		
		// Actions et Filtres PressTiFy
		add_action( 'tify_form_register', array( $this, 'tify_form_register' ) );	
	}
	
	/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == Déclaration des formulaires == **/	
	function tify_form_register(){		
		// Formulaire d'inscription
			$form = apply_filters( 'tify_forum_subscribe_form', array(
				'ID' 		=> 'tify_forum_subscribe',
				'title' 	=>  __( 'Formulaire d\'inscription aux forums', 'tify' ),
				'prefix' 	=> 'tify_forum_subscribe_form',
				'fields' => array(
					array(
						'slug'			=> 'login',
						'label' 		=> __( 'Identifiant (obligatoire)', 'tify' ),
						'placeholder' 	=> __( 'Identifiant (obligatoire)', 'tify' ),
						'type' 			=> 'input',
						'required'		=> true,
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
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => 'user_email' )
						)
					),
					array(
						'slug'			=> 'firstname',
						'label' 		=> __( 'Prénom', 'tify' ),
						'placeholder' 	=> __( 'Prénom', 'tify' ),
						'type' 			=> 'input',
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => 'first_name' )
						)
					),
					array(
						'slug'			=> 'lastname',
						'label' 		=> __( 'Nom', 'tify' ),
						'placeholder' 	=> __( 'Nom', 'tify' ),
						'type' 			=> 'input',
						'add-ons'		=> array(
							'user'	=> array( 'userdata' => 'last_name' )
						)
					),
					array(
						'slug'			=> 'company',
						'label' 		=> __( 'Nom de la société', 'tify' ),
						'placeholder' 	=> __( 'Nom de la société', 'tify' ),
						'type' 			=> 'input',
						'autocomplete'	=> 'off',
						'required'		=> true,
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
						)
					)
				), 
				'options' => array(
					'submit' => array( 'label' => ( ! is_user_logged_in() ? __( 'S\'inscrire', 'tify' ) : __( 'Mettre à jour', 'tify' ) ) ),
					'success' 	=> array(
						'message'	=> ! is_user_logged_in() ? __( 'Votre demande d\'inscription au forum a été enregistrée', 'tify' ) : __( 'Vos informations personnelles ont été mises à jour', 'tify' ),
						'display' 	=> ! is_user_logged_in() ? false : 'form', 
					)
				)
			)
		);			
		$form['add-ons']['user'] = array( 
			'roles' 		=> $this->master->roles, 
			'user_profile' 	=> false, 
			'edit_hookname' => ( is_admin() && isset( $this->master->hookname['contributor'] ) ) ? $this->master->hookname['contributor'] : false
		);
		
		$this->subscribe_form_id = tify_form_register( $form );	
	}
}
