<?php 
/**
 * 
 */
class tiFy_Membership_Form_Addon{
	/* = ARGUMENTS = */
	public	// Configuration
			$userdatas,
			$roles,
			// Référence
			$master,
			$mkcf;	
	
	/* = CONSTRUCTEUR = */
	function __construct( MKCF $mkcf, tiFy_Membership $master ){
		// Contrôleurs
		$this->mkcf 	= $mkcf;
		$this->master 	= $master;
		
		// Configuration
		$this->userdatas = array(
			'user_login', //(requis) 
			'role', 
			'first_name',
			'last_name',
			'nickname',
			'display_name',
			'user_email', //(requis)
			'user_url',
			'description',
			'user_pass' 
		);
		$this->roles = $this->master->roles;			
		
		// Actions et filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'current_screen', array( $this, 'wp_current_screen' ) );		
		
		// Fonctions de Callback MKCF
		$this->mkcf->callbacks->addons_set( 'handle_check_required', 'tify_membership', array( $this, 'cb_handle_check_required' ) );
		$this->mkcf->callbacks->addons_set( 'handle_check_request', 'tify_membership', array( $this, 'cb_handle_check_request' ) );
		$this->mkcf->callbacks->addons_set( 'handle_before_redirect', 'tify_membership', array( $this, 'cb_handle_before_redirect' ) );
		$this->mkcf->callbacks->addons_set( 'handle_redirect', 'tify_membership', array( $this, 'cb_handle_redirect' ) );
		$this->mkcf->callbacks->addons_set( 'field_value', 'tify_membership', array( $this, 'cb_field_value' ) );
		$this->mkcf->callbacks->addons_set( 'field_set', 'tify_membership', array( $this, 'cb_field_set' ) );		
	}
	
	/* = FILTRES ET ACTIONS WORPDRESS = */
	/** == Initialisation Globale == **/
	function wp_init(){
		// Déclaration des options par défaut de l'addon pour le formulaire
		$this->mkcf->addons->set_default_form_options( 'tify_membership',
			array(
				'sections'					=> array()				
			)
		);
		// Déclaration des options par défaut de l'addon pour les champs de formulaire
		$this->mkcf->addons->set_default_field_options( 'tify_membership',
			array(					 
				'userdata'		=> false, // Champs natif : user_login (requis) | role | first_name | last_name | nickname | display_name | user_email (requis) | user_url | description | user_pass  									
				'section' 		=> 'default',
				'updatable'		=> true
			)
		);
	}
		
	/** == Initialisation de l'interface d'administration == **/
	function wp_current_screen(){
		$this->mkcf->forms->set_current( $this->master->form_id );
			
		$boxtitle = "";		
		if( isset( $_REQUEST['user_id'] ) && ( $profileuser = get_userdata( $_REQUEST['user_id'] ) ) ) :
			$status = (int) $this->master->capabilities->get_status( $profileuser->ID );
			
			$boxtitle .= sprintf( __( 'Modification de l\'utilisateur : %s', 'tify' ), '<span class="user_display_name">'. $profileuser->display_name .'</span>' );
			if( $status < 0 )
				$boxtitle .= " <em style=\"color:#AAA; font-size:11px; font-weight:normal;\">(". __( 'En attente de validation', 'tify' ) .")</em> ";
			$boxtitle .= "<div class=\"activate_account\">"; 
			$boxtitle .= sprintf( 
							__( 'Actif : %s', 'tify' ), 
							tify_control_switch( 
								array( 
									'name' => 'tify_membership_status', 
									'checked' => (  $status > 0 ) ? 1 : 0, 
									'echo' => false 
								) 
							) 
						);
			$boxtitle .= "</div>";
		else :
			$boxtitle .= __( 'Création d\'un nouvel utilisateur', 'tify' );
		endif;
		
		// Définition de l'interface d'édition
		$this->master->taboox->set_box( $this->master->hooknames['member_edit'], array( 'title' => $boxtitle ) );		
		
		$this->master->taboox->add_node( 
			$this->master->hooknames['member_edit'],
			array(
				'id' 	=> 'native-infos',
				'title' => __( 'Informations générales', 'tify' ),
				'cb' 	=> array( $this, 'view_user_profile_native' )
			)
		);
		
		$this->master->taboox->add_node( 
			$this->master->hooknames['member_edit'],
			array(
				'id' 	=> 'default',
				'title' => __( 'Informations complémentaires', 'tify' ),
				'cb' 	=> array( $this, 'view_user_profile_meta' ),
				'args'	=> array( 'section_id' => 'default' )
			)
		);		
		
		foreach( (array) $this->get_option( 'sections', 'tify_membership' ) as $section_id => $section_title ) :
			$this->master->taboox->add_node( 
				$this->master->hooknames['member_edit'],
				array(
					'id' 	=> $section_id,
					'title' => $section_title,
					'cb' 	=> array( $this, 'view_user_profile_meta' ),
					'args'	=> array( 'section_id' => $section_id )
				)
			);
		endforeach;
			
		$this->mkcf->forms->reset_current();
	}

	/* = MKCF CALLBACKS = */
	/** == Modification des vérifications == **/
	function cb_handle_check_required( &$errors, $field ){
		if( is_admin() ) :
			if( ! $this->mkcf->addons->get_field_option( 'section', 'tify_membership', $field ) ) :
				unset( $errors[ 'required:'.$field['slug'] ] );
			endif;
		endif;
		
		// Désactivation du champ mot de passe requis
		if( is_user_logged_in() && ( $this->mkcf->addons->get_field_option( 'userdata', 'tify_membership', $field ) === 'user_pass' ) )
			unset( $errors['required:'.$field['slug'] ] );
	} 
	 
	/** == Vérification de la requête == **/
	function cb_handle_check_request( &$errors, $field ){		
		if( ! $userdata = $this->mkcf->addons->get_field_option( 'userdata', 'tify_membership', $field ) )
			return;
		if( ! $this->is_main_userdata( $userdata ) )
			return;
		elseif( ! in_array( $userdata, array( 'user_login', 'user_email' ) ) )
			return;
		if( ( $userdata == 'user_login' ) && ( $user = get_user_by( 'login', $field['value'] ) ) && ( $user->ID != $this->master->edituser_id ) ) 		
			$errors[] = __( 'Cet identifiant est déjà utilisé par un autre utilisateur', 'tify' );
		elseif( ( $userdata == 'user_email' ) && ( $user = get_user_by( 'email', $field['value'] ) ) && ( $user->ID != $this->master->edituser_id ) )
			$errors[] = __( 'Cet email est déjà utilisé par un autre utilisateur', 'tify' );
	}
	
	/** == Traitement du formulaire == **/
	function cb_handle_before_redirect( $parsed_request, $original_request ){
		// Bypass
		if( ! $userdata = $this->parse_user_datas( $parsed_request ) )
			return;
		
		if( $current_user = get_userdata( $this->master->edituser_id ) ) :
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
				if ( is_wp_error( $user_details[ 'errors' ] ) && !empty( $user_details[ 'errors' ]->errors ) )
					return $this->mkcf->errors->errors[ $parsed_request['form_id'] ] = array( array( $user_details[ 'errors' ]->get_error_message() ) );	
			endif;	
			$user_id = wp_insert_user( $userdata );
		endif;
			
		if( is_wp_error( $user_id ) ) :
			$this->mkcf->errors->errors[ $parsed_request['form_id'] ] = array( array( $user_id->get_error_message() ) );
		else :
			$this->master->edituser_id = $user_id;
			
			// Création ou modification du statut de membre
			update_user_option( $this->master->edituser_id, 'tify_membership_status', $this->get_member_status() );
				
			// Création ou modification des informations personnelles
			foreach( $parsed_request['fields'] as $slug => $field )
				if( ( $userdata = $this->mkcf->addons->get_field_option( 'userdata', 'tify_membership', $field ) ) && ( $userdata == 'meta' ) )
					update_user_meta( $this->master->edituser_id, $slug,  $field['value'] );
		endif;	
	}	
	
	/**
	 * Modification de l'url de redirection après soumission du formulaire
	 */
	function cb_handle_redirect( &$location ){
		if( ! is_admin() )
			return;
		$location = remove_query_arg( 'mktzr_forms_results-'. $this->master->form_id, $_REQUEST['_wp_http_referer'] );
		$location = add_query_arg( array( 'message' => 1, 'user_id' => $this->master->edituser_id ), $location );
	}
	
	/** == Définition des champs éditables == **/
	function cb_field_set( &$field ){
		// Bypass
		if( ! $userdata = $this->mkcf->addons->get_field_option( 'userdata', 'tify_membership', $field ) )
			return $field;
		
		if( in_array( $userdata, array( 'user_pass' ) ) ) :
			$field['onpaste'] 		= false;
			$field['autocomplete'] 	= 'off';
		endif;
		
		// Réservé à la modification de compte
		if( ! is_user_logged_in() )
			return $field;	

		if( $this->master->edituser_id && in_array( $userdata, array( 'user_login' ) ) )
			$field['readonly'] = true;
		
		if( is_admin() )
			return $field;
		
		if( ! $this->mkcf->addons->get_field_option( 'updatable', 'tify_membership', $field ) )
			$field['readonly'] = true;
		
		return $field;
	}	
	
	/** == Définition des valeurs de champ == **/
	function cb_field_value( &$field_value, $field ){
		// Bypass
		if( ! $current_user = get_userdata( $this->master->edituser_id ) )
			return;		
		if( empty( $current_user->roles ) )
			return;
		if( ! $userdata = $this->mkcf->addons->get_field_option( 'userdata', 'tify_membership', $field ) )
			return;
		if( ! empty( $this->mkcf->handle->parsed_request['fields'][ $field['slug'] ] ) )
			return;
		
		if( $userdata == 'meta' ) :
			$field_value = get_user_meta( $current_user->ID, $field['slug'], true );
		elseif( $userdata == 'role' ) :				
			$field_value = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : false;
		elseif( $userdata == 'user_pass' ) :
			$field_value = '';
		elseif( ! empty( $current_user->{$userdata} ) ):
			$field_value = $current_user->{$userdata};
		endif;
	}

	/* = VUES = */	
	/** == Interface d'administration des données de profil utilisateur == **/
	function view_user_profile_native( $profileuser, $args = array() ){
		$_form = $this->mkcf->forms->set_current( $this->master->form_id );
		
		// Initialisation des étapes de formulaire
		if( ! $this->mkcf->forms->get_step( ) )
			$this->mkcf->forms->init_step();
		
		$output = "";	
		$output .= "<table class=\"form-table\">\n";
		// Champs cachés
		$output .= $this->mkcf->forms->hidden_form_fields( );
		//$output .= $profileuser->ID ? wp_nonce_field( 'update-user_' . $profileuser->ID ) : wp_nonce_field( 'create-user', '_wpnonce_create-user' );
		
		foreach( $this->userdatas as $userdata ) :
			foreach( $this->mkcf->fields->get_list() as $field ) :
				if( $this->mkcf->addons->get_field_option( 'userdata', 'tify_membership', $field ) != $userdata )
					continue;
				$output .= $this->view_user_profile_field( $field, $profileuser );
			endforeach;			
		endforeach;
		$output .= "</table>";			
		
		echo $output;
		
		$this->mkcf->forms->reset_current();
	}
	
	/** == Interface d'administration des metadonnées de profil utilisateur == **/
	function view_user_profile_meta( $profileuser, $args = array() ){
		$_form = $this->mkcf->forms->set_current( $this->master->form_id );
			
		$sections = $this->get_option( 'sections', 'tify_membership' );
		$section_id =  $args['section_id'];
		
		$output = "";
		$output .= "<table class=\"form-table\">\n";
		foreach( $this->mkcf->fields->get_list() as $field ) :
			if( $this->mkcf->addons->get_field_option( 'userdata', 'tify_membership', $field ) != 'meta' )
				continue;
			if( $this->mkcf->addons->get_field_option( 'section', 'tify_membership', $field ) != $section_id ) 
				continue;
				$output .= $this->view_user_profile_field( $field, $profileuser );
		endforeach;
		$output .= "</table>";			
		
		echo $output;
		
		$this->mkcf->forms->reset_current();
	}
	
	/**
	 * 
	 */
	function view_user_profile_field( $field, $profileuser ){
		// Bypass
		if( ! $profileuser instanceof WP_User )
			$profileuser = false;

		$output  = "";
		$output .= "\t<tr>\n";
		$output .= "\t\t<th>\n";
		$output .= "\t\t\t<label>\n";
		$output .= $field['label'];
		$output .= "\t\t\t</label>\n";
		$output .= "\t\t</th>\n";
		$output .= "\t\t<td>\n";
		$field['echo'] = false; $field['label'] = false; 
		$output .=	$this->mkcf->fields->display( $field );
		$output .= "\t\t</td>\n";
		$output .= "\t</tr>\n";
		
		return $output;
	}

	/* = CONTRÔLEURS = */
	/** == Vérifie s'il s'agit d'une des données utilisateur principale == **/
	function is_main_userdata( $userdata ){		
		return in_array( $userdata, $this->userdatas );
	}
	
	/** == Récupération des options par rôle == **/
	function get_option( $option, $role ){
		$_option = $this->mkcf->addons->get_form_option( $option, 'tify_membership' );
		if( ! isset( $_option ) )
			return;
		elseif( isset( $_option[$role] ) )
			return $_option[$role];
		else
			return $_option;
	}
	
	/** == Traitement des données utilisateurs == **/
	function parse_user_datas( $parsed_request ){			
		$userdata = array( 'user_login' => '', 'role' => '', 'first_name' => '', 'last_name' => '', 'nickname' => '', 'display_name' => '', 'user_email' => '', 'user_url' => '',  'description' => '',  'user_pass' => '' );
		extract( $userdata );
		
		foreach( $parsed_request['fields'] as $field ) :
			if( ( $field_userdata = $this->mkcf->addons->get_field_option( 'userdata', 'tify_membership', $field ) ) && $this->is_main_userdata( $field_userdata ) )
				${$field_userdata} = $field['value'];
		endforeach;
		
		if( ! $role ) :
			if( is_user_logged_in() && ( $role = array_shift( wp_get_current_user()->roles ) ) ) :
			else :
				$roles =  $this->mkcf->addons->get_form_option( 'roles', 'tify_membership' );
	
				if( is_array( $roles ) ) :
					$roles = array_keys( $roles );
					$role = array_shift( $roles );
				else :
					$role = get_option( 'default_role', 'subscriber' );
				endif;
			endif;
		endif;
		
		$show_admin_bar_front = ( ! $_show_admin_bar_front = $this->roles[$role]['show_admin_bar_front'] ) ? 'false' : $_show_admin_bar_front;
		$userdata = array( 'user_login', 'role', 'first_name', 'last_name', 'nickname', 'display_name', 'user_email', 'user_url',  'description', 'user_pass', 'show_admin_bar_front' );
		
		$_userdata = compact( $userdata );

		return $_userdata;
	}

	/** == Status du membre == **/
	function get_member_status(){
		if( is_admin( ) && isset( $_REQUEST['tify_membership_status'] ) )
			return $_REQUEST['tify_membership_status'];
		else
			return -1;
	}
}