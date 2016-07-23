<?php
/*
Addon Name: User
Addon ID: user
Callback: tiFy_Forms_Addon_User
Version: 1.150815
Author: Jordy Manner
Author URI: http://profile.milkcreation.fr/jordy.manner
*/

class tiFy_Forms_Addon_User extends tiFy_Forms_Addon{
	/* = ARGUMENTS = */
	public	// Configuration
			$userdatas	= array( 'user_login', 'role', 'first_name', 'last_name', 'nickname', 'display_name', 'user_email', 'user_url', 'description', 'user_pass' ),			
			$roles,
			
			// Paramètres
			$edituser_id = 0,
			$current_user,
			$is_profile_page;	 
	
	/* = CONTROLEUR = */
	function __construct( tiFy_Forms $master ){
		// Définition des options de formulaire par défaut
		$this->default_form_options = array(
			'roles'			=> array(),
			'sections'		=> array(
				// ex : 'default' => __( 'Informations complémentaires', 'tify' )
			),
			'user_profile'			=> true,			// Modification des données utilisateurs depuis l'interface administration de gestion du profil
			'list_hookname' 		=> false,
			'edit_hookname' 		=> false
		);
		
		// Définition des options de champ de formulaire par défaut
		$this->default_field_options = array(					 
			'userdata'		=> false, // Champs natif : user_login (requis) | role | first_name | last_name | nickname | display_name | user_email (requis) | user_url | description | user_pass  									
			'updatable'		=> true,
			'section' 		=> 'default',
		);
		
		// Définition des fonctions de callback
		$this->callbacks = array(
			'handle_check_required' => array( $this, 'cb_handle_check_required' ),
			'handle_check_request'	=> array( $this, 'cb_handle_check_request' ),
			'handle_submit_request'	=> array( $this, 'cb_handle_submit_request' ),
			'handle_redirect'		=> array( $this, 'cb_handle_redirect' ),
			'field_value'			=> array( $this, 'cb_field_value' ),
			'field_set'				=> array( $this, 'cb_field_set' )			
		);
		
		parent::__construct( $master );

		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'admin_menu', array( $this, 'wp_admin_menu' ) );		
		add_action( 'personal_options_update', array( $this, 'wp_profile_update' ) );
		add_action( 'edit_user_profile_update', array( $this, 'wp_profile_update' ) );			
		
		// Actions et Filtres PressTiFy
		add_action( 'tify_taboox_register_box', array( $this, 'tify_taboox_register_box' ) );
		add_action( 'tify_taboox_register_node', array( $this, 'tify_taboox_register_node' ) );
		add_action( 'tify_taboox_register_form', array( $this, 'tify_taboox_register_form' ) );	        
	}

	/* = CONFIGURATION = */
	/** == Traitement des rôles == **/
	private function set_roles( $roles = array() ){
		$defaults = array(
			'name' 					=> '',
			'capabilities'			=> array(),
			'show_admin_bar_front' 	=> false,
			'activation'			=> false
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
	
	/* = ACTIONS ET FILTRES WORPDRESS = */
	/** == Initialisation globale == **/
	public function wp_init(){		
		// Définition de l'id de l'utilisateur à éditer
		$this->edituser_id 		= is_admin() ? ( ! empty( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : 0 ) : get_current_user_id();
		$this->current_user 	= wp_get_current_user();
		$this->is_profile_page 	= ( $this->edituser_id == $this->current_user->ID );			
	}
	
	/** == Initialisation de l'interface d'administration == **/
	public function wp_admin_menu(){
		foreach( (array) $this->master->addons->get_forms_active( 'user' ) as $form ) :
			$this->master->forms->set_current( $form );
			// Création des rôles et des habilitations
			if( $this->roles = $this->set_roles( $this->master->addons->get_form_option( 'roles', 'user' ) ) ) :			
				foreach( (array) $this->roles as $role => $args ) :
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
			
			// Modification des données utilisateurs depuis l'interface administration de gestion du profil 
			if( $this->master->addons->get_form_option( 'user_profile', 'user' ) ) :
				add_action( 'show_user_profile', array( $this, 'wp_user_profile' ) );
				add_action( 'edit_user_profile', array( $this, 'wp_user_profile' ) );
			endif;
								
			$this->master->forms->reset_current();
		endforeach;
	}

	/** == Mise à jour des données du client depuis l'interface d'administration de Wordpress == **/
	public function wp_profile_update( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) )
			return false;
		
		foreach( (array) $this->master->addons->get_forms_active( 'user' ) as $form ) :
			$this->master->forms->set_current( $form );
			
			foreach( $this->master->fields->get_list() as $field ) :
				if( ( ! $userdata = $this->master->addons->get_field_option( 'userdata', 'user', $field ) ) || $this->is_main_userdata( $userdata ) )
					continue;
				if( ! $value = $this->master->handle->get_request( $field ) ) 
					$value = $field['value'];
				update_user_meta( $user_id, $field['slug'], $value );
			endforeach;

			$this->master->forms->reset_current();
		endforeach;
	}

	/** == Interface d'administration du profil utilisateur == **/
	public function wp_user_profile( $profileuser, $args = array() ){
		foreach( (array) $this->master->addons->get_forms_active( 'user' ) as $form ) :
			$this->master->forms->set_current( $form );
			
			// Bypass
			if( ! $this->master->addons->get_form_option( 'user_profile', 'user' ) )
				return;
			if( ! in_array( get_user_role( $profileuser ), array_keys( $this->roles ) ) )
				continue;
			
			$output = "";
			$sections = $this->master->addons->get_form_option( 'sections', 'user' );
			if( empty( $sections ) ) :
				$output .= "<h3>". __( 'Informations complémentaires', 'tify' ) ."</h3>";
				$output .= $this->user_profile_section_display( 'default', $profileuser );
			else :
				foreach( $sections as $sect_slug => $sect_label ) :
					$output .= "<h3>". $sect_label ."</h3>";
					$output .= $this->user_profile_section_display( $sect_slug, $profileuser );
				endforeach;
			endif;			
			
			echo $output;
			
			$this->master->forms->reset_current();
		endforeach;
	}
	
		/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == == **/
	public function tify_taboox_register_box(){
		foreach( (array) $this->master->addons->get_forms_active( 'user' ) as $form ) :
			$this->master->forms->set_current( $form );
			
			$hookname = $this->master->addons->get_form_option( 'edit_hookname', 'user' );
			
			$boxtitle = "";		
			if( $profileuser = get_userdata( $this->edituser_id ) ) :					
				$boxtitle .= sprintf( __( 'Modification de l\'utilisateur : %s', 'tify' ), '<span class="user_display_name">'. $profileuser->display_name .'</span>' );
				if( get_user_option( 'tify_user_status', $this->edituser_id ) < 0 )
					$boxtitle .= " <em style=\"color:#AAA; font-size:11px; font-weight:normal;\">(". __( 'En attente de validation', 'tify' ) .")</em> ";
				$boxtitle .= "<div class=\"activate_account\">"; 
				$boxtitle .= sprintf( 
								__( 'Actif : %s', 'tify' ), 
								tify_control_switch( 
									array( 
										'name' => 'tify_membership_status', 
										'checked' => $this->get_user_status( $this->edituser_id ), 
										'echo' => false 
									) 
								) 
							);
				$boxtitle .= "</div>";
			else :
				$boxtitle .= __( 'Création d\'un nouvel utilisateur', 'tify' );
			endif;
			
			// Définition de l'interface d'édition
			tify_taboox_register_box( $hookname, 'user', array( 'title' => $boxtitle ) );
		
			$this->master->forms->reset_current();
		endforeach;
	}
	
	/** == == **/
	public function tify_taboox_register_node(){
		foreach( (array) $this->master->addons->get_forms_active( 'user' ) as $form ) :
			$this->master->forms->set_current( $form );
			
			$hookname = $this->master->addons->get_form_option( 'edit_hookname', 'user' );
			tify_taboox_register_node( 
				$hookname,
				array(
					'id' 	=> 'native-infos',
					'title' => __( 'Informations générales', 'tify' ),
					'cb' 	=> 'tiFy_Forms_AddonUser_NativeFields_Taboox',
					'order'	=> 1
				)
			);
			
			$sections = $this->master->addons->get_form_option( 'sections', 'user' );
			if( empty( $sections ) ) :
				tify_taboox_register_node(
					$hookname,
					array(
						'id' 	=> 'default',
						'title' => __( 'Informations complémentaires', 'tify' ),
						'cb' 	=> 'tiFy_Forms_AddonUser_SectionFields_Taboox',
						'args'	=> array( 'section' => 'default' )
					)
				);		
			else :
				foreach( (array) $sections as $sect_slug => $sect_label ) :
					tify_taboox_register_node( 
						$hookname,
						array(
							'id' 	=> $sect_slug,
							'title' => $sect_label,
							'cb' 	=> array( $this, 'user_profile_section_display' ),
							'args'	=> array( 'section' => sect_slug )
						)
					);
				endforeach;
			endif;
				
			$this->master->forms->reset_current();
		endforeach;
		
	}
	
	/** == == **/
	function tify_taboox_register_form(){
		tify_taboox_register_form( 'tiFy_Forms_AddonUser_NativeFields_Taboox', $this );
		tify_taboox_register_form( 'tiFy_Forms_AddonUser_SectionFields_Taboox', $this );
	}
	
	/** == Affichage d'une section de données utilisateur == **/
	private function user_profile_section_display( $section, $profileuser ){
		$output  = "";
		
		$output .= "<table class=\"form-table\">\n";
		foreach( $this->master->fields->get_list() as $field ) :
			if( $this->master->addons->get_field_option( 'section', 'user', $field ) !== $section )
				continue;
			if( is_bool( $this->master->addons->get_field_option( 'userdata', 'user', $field ) ) && ( (bool) $this->master->addons->get_field_option( 'userdata', 'user', $field ) === true ) ) :
				$output .= $this->user_profile_field_display( $field, $profileuser );
			endif;
		endforeach;
		$output .= "</table>";
		
		return $output;
	}
	
	/** == Affichage d'un champs de données utilisateur == **/
	private function user_profile_field_display( $field, $profileuser ){
		$output  = "";
		$output .= "\t<tr>\n";
		$output .= "\t\t<th>\n";
		$output .= "\t\t\t<label>\n";
		$output .= $field['label'];
		$output .= "\t\t\t</label>\n";
		$output .= "\t\t</th>\n";
		$output .= "\t\t<td>\n";
		$output .= $this->master->fields->display( 
						$field, 
						array( 
							'value' 		=> get_user_meta( $profileuser->ID, $field['slug'] , true ),
							'field_class'	=> 'regular-text ltr',
							'placeholder'	=> false,
							'echo' 			=> false,
							'label' 		=> false
						)							
					);
		$output .= "\t\t</td>\n";
		$output .= "\t</tr>\n";
		
		return $output;
	}
			
	/* = FONCTIONS DE CALLBACK TIFY_FORMS = */
	/** == Vérification des champs requis == **/
	function cb_handle_check_required( &$errors, $field ){
		// Désactivation du champ mot de passe requis
		if( is_user_logged_in() && ( $this->master->addons->get_field_option( 'userdata', 'tify_membership', $field ) === 'user_pass' ) )
			unset( $errors['required:'.$field['slug'] ] );
	} 
	 
	/** == Vérification de la requête == **/
	function cb_handle_check_request( &$errors, $field ){
		if( ! $userdata = $this->master->addons->get_field_option( 'userdata', 'user', $field ) )
			return;
		if( ! $this->is_main_userdata( $userdata ) )
			return;
		elseif( ! in_array( $userdata, array( 'user_login', 'user_email' ) ) )
			return;
		
		if( ( $userdata == 'user_login' ) && ( $user = get_user_by( 'login', $field['value'] ) ) && ( $user->ID != $this->edituser_id ) )
			$errors[] = __( 'Cet identifiant est déjà utilisé par un autre utilisateur', 'tify' );
		elseif( ( $userdata == 'user_email' ) && ( $user = get_user_by( 'email', $field['value'] ) ) && ( $user->ID != $this->edituser_id ) )
			$errors[] = __( 'Cet email est déjà utilisé par un autre utilisateur', 'tify' );
	}
	
	/** == Traitement du formulaire == **/
	function cb_handle_submit_request( $parsed_request, $original_request ){
		$this->roles = $this->set_roles( $this->master->addons->get_form_option( 'roles', 'user' ) );
		// Bypass
		if( ! $userdata = $this->parse_user_datas( $parsed_request ) )
			return;
		
		if( $current_user = get_userdata( $this->edituser_id ) ) :
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
					return $this->master->errors->errors[ $parsed_request['form_id'] ] = array( array( $user_details[ 'errors' ]->get_error_message() ) );	
			endif;
			$user_id = wp_insert_user( $userdata );
		endif;
			
		if( is_wp_error( $user_id ) ) :
			$this->master->errors->errors[ $parsed_request['form_id'] ] = array( array( $user_id->get_error_message() ) );
		else :
			$this->edituser_id = $user_id;
			
			// Création ou modification du statut de membre
			update_user_option( $this->edituser_id, 'tify_user_status', ( $this->get_user_status( $this->edituser_id ) === 'on' ? 1 : 0 ) );
				
			// Création ou modification des informations personnelles
			foreach( $parsed_request['fields'] as $slug => $field )
				if( is_bool( $this->master->addons->get_field_option( 'userdata', 'user', $field ) ) && ( (bool) $this->master->addons->get_field_option( 'userdata', 'user', $field ) === true ) )
					update_user_meta( $this->edituser_id, $slug,  $field['value'] );
		endif;	
	}
	
	/** == Interruption de la redirection == **/
	function cb_handle_redirect( &$location, $results_arg ){
		if( is_admin() )
			$location = false;
	}
	
	/** == Définition des champs éditables == **/
	function cb_field_set( &$field ){
		// Bypass
		if( ! $userdata = $this->master->addons->get_field_option( 'userdata', 'user', $field ) )
			return $field;
		
		if( is_string( $userdata ) && in_array( $userdata, array( 'user_pass' ) ) ) :
			$field['onpaste'] 		= false;
			$field['autocomplete'] 	= 'off';
		endif;
		
		// Réservé à la modification de compte
		if( ! is_user_logged_in() )
			return $field;
		if( is_admin() )
			return $field;
		
		if( $this->edituser_id && in_array( $userdata, array( 'user_login' ) ) )
			$field['readonly'] = true;		
		
		if( ! $this->master->addons->get_field_option( 'updatable', 'user', $field ) )
			$field['readonly'] = true;
		
		return $field;
	}	
	
	/** == Définition des valeurs de champ == **/
	function cb_field_value( &$field_value, $field ){
		// Bypass
		if( ! $current_user = get_userdata( $this->edituser_id ) )
			return;		
		if( empty( $current_user->roles ) )
			return;
		if( ! $userdata = $this->master->addons->get_field_option( 'userdata', 'user', $field ) )
			return;
		if( ! empty( $this->master->handle->parsed_request['fields'][ $field['slug'] ] ) )
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

	/* = CONTRÔLEURS = */
	/** == Vérifie s'il s'agit d'une des données utilisateur principale == **/
	function is_main_userdata( $userdata ){
		if( is_bool( $userdata ) )	
			return false;
		return in_array( $userdata, $this->userdatas );
	}
		
	/** == Traitement des données utilisateurs == **/
	function parse_user_datas( $parsed_request ){			
		$userdata = array( 'user_login' => '', 'role' => '', 'first_name' => '', 'last_name' => '', 'nickname' => '', 'display_name' => '', 'user_email' => '', 'url' => '',  'description' => '',  'user_pass' => '' );
		extract( $userdata );
		
		foreach( $parsed_request['fields'] as $field )
			if( ( $field_userdata = $this->master->addons->get_field_option( 'userdata', 'user', $field ) ) && $this->is_main_userdata( $field_userdata ) )
				${$field_userdata} = $field['value'];
		
		if( ! $role )
			if( is_user_logged_in() )
				$role = current( wp_get_current_user()->roles );
			elseif( is_array( $this->roles ) )
				$role = current( array_keys( $this->roles ) );
			else
				$role = get_option( 'default_role', 'subscriber' );
		
		if( in_array( $role, array_keys( $this->roles ) ) )
			$show_admin_bar_front =  ! $this->roles[$role]['show_admin_bar_front'] ? 'off' : '';
		
		$userdata = array( 'user_login', 'role', 'first_name', 'last_name', 'nickname', 'display_name', 'user_email', 'url',  'description', 'user_pass', 'show_admin_bar_front' );
		$_userdata = compact( $userdata );
		
		return $_userdata;
	}

	/** == Récupération du status d'activation d'un utilisateur == **/
	function get_user_status( $user_id ){
		// Récupération du rôle de l'utilisateur
		$profileuser = get_userdata( $user_id );
		$role = current( $profileuser->roles );		

		if( ! in_array( $role, $this->roles ) )
			return 'on';		
		if( ! $this->roles[$role]['activation'] )
			return 'on';
		
		if( get_user_option( 'tify_user_status', $user_id ) > 0 )
			return 'on';
		
		return 'off';
	}
}

/** == == **/
class tiFy_Forms_AddonUser_NativeFields_Taboox extends tiFy\Taboox\Admin{
	/* = ARGUMENTS = */
	public 	// Configuration
			$name = '',
			// Référence
			$master;
	
	/* = CONSTRUCTEUR =*/
	function __construct( tify_forms_addon_user $master ){
		// Instanciation de la classe de référence
		$this->master = $master;

		parent::__construct();
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	function current_screen( $screen ){
		tify_control_enqueue( 'switch' );
	}	
	
	/* = FORMULAIRE DE SAISIE = */
	function form( $profileuser ){
	?>
		<h3><?php _e( 'Nom', 'tify' );?></h3>
		<table class="form-table">
			<tbody>
				<tr scope="row">
					<th>
						<label><?php _e( 'Identifiant', 'tify' );?></label>
					</th>
					<td>
						<input type="text" name="user_login" id="user_login" value="<?php echo $profileuser->user_login;?>" disabled="disabled" class="regular-text">					
					</td>
				</tr>				
				<tr scope="row">
					<th>
						<label><?php _e( 'Prénom', 'tify' );?></label>
					</th>
					<td>
						<input type="text" name="first_name" id="first_name" value="<?php echo $profileuser->first_name;?>" class="regular-text ltr">					
					</td>
				</tr>
				<tr scope="row">
					<th>
						<label><?php _e( 'Nom', 'tify' );?></label>
					</th>
					<td>
						<input type="text" name="last_name" id="last_name" value="<?php echo $profileuser->last_name;?>" class="regular-text ltr">					
					</td>
				</tr>
				<tr scope="row">
					<th>
						<label><?php _e( 'Pseudonyme (obligatoire)', 'tify' );?></label>
					</th>
					<td>
						<input type="text" name="nickname" id="nickname" value="<?php echo $profileuser->nickname;?>" class="regular-text ltr">					
					</td>
				</tr>
			</tbody>
		</table>
		
		<h3><?php _e( 'Informations de contact', 'tify' );?></h3>
		<table class="form-table">
			<tbody>	
				<tr scope="row">
					<th>
						<label><?php _e( 'E-mail (obligatoire)', 'tify' );?></label>
					</th>
					<td>
						<input type="email" name="email" id="email" value="<?php echo $profileuser->user_email;?>" class="regular-text ltr">					
					</td>
				</tr>
				<tr scope="row">
					<th>
						<label><?php _e( 'Site web', 'tify' );?></label>
					</th>
					<td>
						<input type="text" name="url" id="url" value="<?php echo $profileuser->user_url;?>" class="regular-text ltr">					
					</td>
				</tr>
			</tbody>
		</table>
		<h3><?php _e( 'Informations de connexion', 'tify' );?></h3>
		<table class="form-table">
			<tbody>
				<tr scope="row">
					<th>
						<label><?php _e( 'Nouveau mot de passe', 'tify' );?></label>
					</th>
					<td>
						<input type="password" name="pass1" id="pass1" value="" class="regular-text" autocomplete="off">					
					</td>
				</tr>
				<tr scope="row">
					<th>
						<label><?php _e( 'Répétez le mot de passe', 'tify' );?></label>
					</th>
					<td>
						<input type="password" name="pass2" id="pass2" value="" class="regular-text" autocomplete="off">					
					</td>
				</tr>
			</tbody>
		</table>
	<?php
	}
}

class tiFy_Forms_AddonUser_SectionFields_Taboox extends tiFy\Taboox\Admin{
	/* = ARGUMENTS = */
	public 	// Configuration
			$name = '',
			// Référence
			$main;
	
	/* = CONSTRUCTEUR =*/
	function __construct( tiFy_Forms_Addon_User $main ){
		// Instanciation de la classe de référence
		$this->main = $main;
		
		// Instanciation de la classe parente
		parent::__construct();
	}
	
	/* = FORMULAIRE DE SAISIE = */
	function form( $profileuser ){
		$output  = "";

		$output .= "<table class=\"form-table\">\n";
		foreach( $this->main->master->fields->get_list() as $field ) :
			if( $this->main->master->addons->get_field_option( 'section', 'user', $field ) !== $this->args['section'] )
				continue;
			if( is_bool( $this->main->master->addons->get_field_option( 'userdata', 'user', $field ) ) && ( (bool) $this->main->master->addons->get_field_option( 'userdata', 'user', $field ) === true ) ) :
				$output .= "\t<tr>\n";
				$output .= "\t\t<th>\n";
				$output .= "\t\t\t<label>\n";
				$output .= $field['label'];
				$output .= "\t\t\t</label>\n";
				$output .= "\t\t</th>\n";
				$output .= "\t\t<td>\n";
				$output .= $this->main->master->fields->display( 
								$field, 
								array( 
									'value' 		=> get_user_meta( $profileuser->ID, $field['slug'] , true ),
									'field_class'	=> 'regular-text ltr',
									'placeholder'	=> false,
									'echo' 			=> false,
									'label' 		=> false
								)							
							);
				$output .= "\t\t</td>\n";
				$output .= "\t</tr>\n";
			endif;
		endforeach;
		$output .= "</table>";
		
		echo $output;
	}
}