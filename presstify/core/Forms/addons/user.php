<?php
/*
Addon Name: User
Addon ID: user
Callback: tiFy_Forms_Addon_User
Version: 1.160611
Author: Jordy Manner
Author URI: http://profile.milkcreation.fr/jordy.manner
*/

class tiFy_Forms_Addon_User extends tiFy_Forms_Addon{
	/* = ARGUMENTS = */
	//
	private	$Roles			= array();
	
	//
	private $UserID 		= 0;
	
	//
	private $ProfileEdit	= false;
	
	/* = CONTROLEUR = */
	public function __construct( tiFy_Forms $master )
	{
		// Définition des options de formulaire par défaut
		$this->default_form_options = array(
			'roles'			=> array()
		);
		
		// Définition des options de champ de formulaire par défaut
		$this->default_field_options = array(					 
			'userdata'		=> false, // Champs natif : user_login (requis) | role | first_name | last_name | nickname | display_name | user_email (requis) | user_url | description | user_pass  									
		);
		
		// Définition des fonctions de callback
		$this->callbacks = array(
			'handle_check_request'	=> array( $this, 'cb_handle_check_request' ),
			'handle_submit_request'	=> array( $this, 'cb_handle_submit_request' ),
			'field_set'				=> array( $this, 'cb_field_set' )			
		);
		
		parent::__construct( $master );

		// Actions et Filtres Wordpress
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		
		// Définition de l'id de l'utilisateur à éditer
		$this->UserID 			= get_current_user_id();
		$this->ProfileEdit 		= $this->UserID ? true : false;
	}

	/* = DECLENCHEURS = */	
	/** == Initialisation de l'interface d'administration == **/
	final public function admin_menu()
	{
		foreach( (array) $this->master->addons->get_forms_active( 'user' ) as $form ) :
			$this->master->forms->set_current( $form );
			// Création des rôles et des habilitations
			if( $this->Roles = $this->setRoles( $this->master->addons->get_form_option( 'roles', 'user' ) ) ) :			
				foreach( (array) $this->Roles as $role => $args ) :
					// Bypass
					if( empty( $role) ) continue;
					if( ! isset( $args['name'] ) ) continue;
						
					// Création du rôle
					if( ! $_role =  get_role( $role ) )
						$_role = add_role( $role, $args['name'] );
		
					// Création des habilitations
					if( isset( $args['capabilities'] ) )
						foreach( (array) $args['capabilities'] as $cap => $grant )
							if( ! isset( $_role->capabilities[$cap] ) ||  ( $_role->capabilities[$cap] != $grant ) )
								$_role->add_cap( $cap, $grant );
				endforeach;
			endif;								
			$this->master->forms->reset_current();
		endforeach;
	}
				
	/* = METHODES DE RAPPEL = */	 
	/** == Vérification de la requête == **/
	final public function cb_handle_check_request( &$errors, $field )
	{
		if( ! $userdata = $this->master->addons->get_field_option( 'userdata', 'user', $field ) ) :
			return;
		elseif( ! $this->is_main_userdata( $userdata ) ) :
			return;
		elseif( ! in_array( $userdata, array( 'user_login', 'user_email', 'role' ) ) ) :
			return;
		endif;
		
		$_errors = array();

		// Vérification d'identifiant de connexion
		if( ( $userdata == 'user_login' ) && ( $user = get_user_by( 'login', $field['value'] ) ) && ! $this->ProfileEdit ) :
			$_errors[] = __( 'Cet identifiant est déjà utilisé par un autre utilisateur', 'tify' );
			
		// Vérification d'identifiant de connexion
		elseif( ( $userdata == 'role' ) ) :
			$roles = $this->master->addons->get_form_option( 'roles', 'user' );
			if( ! in_array( $field['value'], array_keys( $roles ) ) ) :
				$_errors[] = __( 'L\'attribution de ce rôle n\'est pas autorisée.', 'tify' );
			endif;
		
		// Vérification d'email
		elseif( ( $userdata == 'user_email' ) && ( $user = get_user_by( 'email', $field['value'] ) ) && ! $this->ProfileEdit ) :
			$_errors[] = __( 'Cet email est déjà utilisé par un autre utilisateur', 'tify' );
		
		// Vérification d'identifiant de connexion relative aux multisite
		elseif( ( $userdata == 'user_login' ) && is_multisite() ) :
			// Lettres et/ou chiffres uniquement
			$user_name = $field['value'];
			$orig_username = $user_name;
			$user_name = preg_replace( '/\s+/', '', sanitize_user( $user_name, true ) );		
			if ( $user_name != $orig_username || preg_match( '/[^a-z0-9]/', $user_name ) ) :
				$_errors[] =  __( 'L\'identifiant de connexion ne devrait contenir que des lettres minuscules (a-z) et des chiffres', 'tify' );
			endif;
			
			// Identifiant réservés
			$illegal_names = get_site_option( 'illegal_names' );
			if ( ! is_array( $illegal_names ) ) :
				$illegal_names = array(  'www', 'web', 'root', 'admin', 'main', 'invite', 'administrator' );
				add_site_option( 'illegal_names', $illegal_names );
			endif;
			if ( in_array( $user_name, $illegal_names ) ) :
				$_errors[] =  __( 'Désolé, cet identifiant de connexion n\'est pas permis', 'tify' );
			endif;
			
			// Identifiant réservés personnalisés
			$illegal_logins = (array) apply_filters( 'illegal_user_logins', array() );			
			if ( in_array( strtolower( $user_name ), array_map( 'strtolower', $illegal_logins ) ) ) :
				$_errors[] =  __( 'Désolé, cet identifiant de connexion n\'est pas permis', 'tify' );
			endif;
			
			// Longueur minimale
			if ( strlen( $user_name ) < 4 )
				$_errors[] =  __( 'L\'identifiant de connexion doit contenir au moins 4 caractères', 'tify' );
			
			// Longueur maximale
			if ( strlen( $user_name ) > 60 )
				$_errors[] =  __( 'L\'identifiant de connexion ne doit pas contenir plus de 60 caractères', 'tify' );
			
			// Lettres obligatoire
			if ( preg_match( '/^[0-9]*$/', $user_name ) )
				$_errors[] = __( 'L\'identifiant de connexion doit aussi contenir des lettres', 'tify' );
		endif;
		
		if( ! empty( $_errors ) )
			$errors += $_errors;
	}
	
	/** == Traitement du formulaire == **/
	final public function cb_handle_submit_request( $parsed_request, $original_request )
	{
		$this->Roles = $this->setRoles( $this->master->addons->get_form_option( 'roles', 'user' ) );
		// Bypass
		if( ! $userdata = $this->parse_user_datas( $parsed_request ) )
			return;

		if( $current_user = get_userdata( $this->UserID ) ) :
			if( empty( $userdata['user_pass'] ) )
				unset( $userdata['user_pass'] );				
			if( empty( $userdata['role'] ) )
				unset( $userdata['role'] );

			$data = (array) get_userdata( $current_user->ID )->data;
			unset( $data['user_pass'] );			
			$userdata 	= wp_parse_args( $userdata, $data );
			$user_id 	= wp_update_user( $userdata );
		else :
			if( is_multisite() ) :
				$user_details = wpmu_validate_user_signup( $userdata['user_login'], $userdata['user_email'] );
				if ( is_wp_error( $user_details[ 'errors' ] ) && ! empty( $user_details[ 'errors' ]->errors ) )
					return $this->master->errors->errors[ $parsed_request['form_id'] ] = array( array( $user_details[ 'errors' ]->get_error_message() ) );	
			endif;
			$user_id = wp_insert_user( $userdata );
		endif;
			
		if( is_wp_error( $user_id ) ) :
			$this->master->errors->errors[ $parsed_request['form_id'] ] = array( array( $user_id->get_error_message() ) );
		else :
			$this->UserID = $user_id;

			// Création ou modification des informations personnelles
			foreach( $parsed_request['fields'] as $slug => $field ) :
				if( ! $this->master->addons->get_field_option( 'userdata', 'user', $field ) )
					continue;
				if(  $this->master->addons->get_field_option( 'userdata', 'user', $field ) === 'meta' ) :
					update_user_meta( $this->UserID, $slug,  $field['value'] );
				elseif( $this->master->addons->get_field_option( 'userdata', 'user', $field ) === 'option' ) :
					update_user_option( $this->UserID, $slug,  $field['value'] );
				endif;
			endforeach;
		endif;	
	}
		
	/** == Définition des champs éditables == **/
	final public function cb_field_set( &$field )
	{
		// Bypass
		if( ! $userdata = $this->master->addons->get_field_option( 'userdata', 'user', $field ) )
			return $field;
		
		if( is_string( $userdata ) && in_array( $userdata, array( 'user_pass' ) ) ) :
			$field['onpaste'] 		= false;
			$field['autocomplete'] 	= 'off';
		endif;
				
		return $field;
	}	
		
	/* = CONTRÔLEURS = */
	/** == Traitement des rôles == **/
	private function setRoles( $roles = array() )
	{
		$defaults = array(
			'name' 					=> '',
			'capabilities'			=> array(),
			'show_admin_bar_front' 	=> false
		);
		foreach( $roles as $role => &$args ) :
			if( empty( $role ) )
				continue;
			$args = wp_parse_args( $args, $defaults );
			if( empty( $args['name'] ) )
				$args['name'] = $role;
		endforeach;
		
		return $roles;	
	}
	
	/** == Vérifie s'il s'agit d'une des données utilisateur principale == **/
	private function is_main_userdata( $userdata )
	{
		return in_array( 
			$userdata,
			array( 
				'user_login', 
				'role', 
				'first_name', 
				'last_name', 
				'nickname', 
				'display_name', 
				'user_email', 
				'user_url', 
				'description', 					
				'user_pass' 
			)
		);
	}
		
	/** == Traitement des données utilisateurs == **/
	private function parse_user_datas( $parsed_request )
	{			
		$userdata = array( 'user_login' => '', 'role' => '', 'first_name' => '', 'last_name' => '', 'nickname' => '', 'display_name' => '', 'user_email' => '', 'user_url' => '',  'description' => '',  'user_pass' => '' );
		extract( $userdata );
		
		foreach( $parsed_request['fields'] as $field ) :
			if( ( $field_userdata = $this->master->addons->get_field_option( 'userdata', 'user', $field ) ) && $this->is_main_userdata( $field_userdata ) ) :
				${$field_userdata} = $field['value'];
			endif;
		endforeach;	
			
		if( ! $role ) :
			if( is_user_logged_in() ) :
				$role = current( wp_get_current_user()->roles );
			elseif( is_array( $this->Roles ) ) :
				$role = current( array_keys( $this->Roles ) );
			else :
				$role = get_option( 'default_role', 'subscriber' );
			endif;
		endif;
		
		if( in_array( $role, array_keys( $this->Roles ) ) )
			$show_admin_bar_front =  ! $this->Roles[$role]['show_admin_bar_front'] ? 'off' : '';
		
		$userdata = array( 'user_login', 'role', 'first_name', 'last_name', 'nickname', 'display_name', 'user_email', 'user_url',  'description', 'user_pass', 'show_admin_bar_front' );
		$_userdata = compact( $userdata );
		
		return $_userdata;
	}
}