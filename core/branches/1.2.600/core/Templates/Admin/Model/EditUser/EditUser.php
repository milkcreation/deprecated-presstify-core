<?php
namespace tiFy\Core\Templates\Admin\Model\EditUser;

class EditUser extends \tiFy\Core\Templates\Admin\Model\Form
{					
	/* = ARGUMENTS = */
	/// Roles des utilisateurs
	protected $Roles 				= array();
	
	/// Habilitations
	protected $Cap					= 'edit_users';
	
	/// Cartographie des paramètres
	protected $ParamsMap			= array( 
		'BaseUri', 'ListBaseUri', 'Plural', 'Singular', 'Notices', 'Fields', 'QueryArgs', 'NewItem', 'PageTitle',
		'Roles'
	);
			
	/* = PARAMETRES = */
	/** == Définition des rôles des utilisateurs de la table == **/
	public function set_roles()
	{
		return array();
	}
	
	/** == Définition des champs de formulaire == **/
	public function set_fields()
	{
		return array(
			'user_login' 	=> __( 'Identifiant  (obligatoire)', 'tify' ),
			'first_name'	=> __( 'Prénom', 'tify' ),
			'last_name'		=> __( 'Nom', 'tify' ),
			'nickname'		=> __( 'Pseudonyme (obligatoire)', 'tify' ),
			'email'			=> __( 'Adresse de messagerie (obligatoire)', 'tify' ),
			'url'			=> __( 'Site web', 'tify' ),
			'password'		=> __( 'Nouveau mot de passe', 'tify' ),
			'confirm'		=> __( 'Répétez le nouveau mot de passe', 'tify' )
		);
	}
	
	/* = PARAMETRAGE = */
	/** == Initialisation des rôles des utilisateurs de la table == **/
	public function initParamRoles()
	{		
		if( $editable_roles = array_reverse( get_editable_roles() ) )
			$editable_roles = array_keys( $editable_roles );
		
		$roles = array();
		if( $this->set_roles() ) :			
			foreach( (array) $this->set_roles() as $role ) :
				if( ! in_array( $role, $editable_roles ) ) 
					continue;
				array_push(  $roles, $role );
			endforeach;
		else :
			$roles = $editable_roles;
		endif;
		
		$this->Roles = $roles;
	}
	
	/* = TRAITEMENT = */
	/** == Préparation de l'object à éditer == **/
	public function prepare_item()
	{		
		$this->item = get_userdata( $this->current_item() );
	}
	
	/** == Éxecution des actions == **/
	protected function process_bulk_actions()
	{		
		// Vérification des habilitations
		$editable_user = false;
		if( $this->current_item() ) :
			foreach( (array) $this->Roles as $role ) :
				if( user_can( $this->current_item(), $role ) ) :
					$editable_user = true;
					break;
				endif;
			endforeach;
		else :
			$editable_user = true;
		endif;

		if( ! $editable_user || ! current_user_can( $this->Cap ) ) :
			$edit_link = $this->current_item() ? esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $this->current_item() ) ) ) : admin_url( '/user-new.php' );

			wp_die(
				'<h1>'. __( 'Habilitations insuffisantes' )	.'</h1>'.
				'<p><b>'. __( 'Désolé, mais vous n\'êtes pas autorisé a éditer l\'utilisateur depuis cette interface.', 'tify' ) .'</b></p>'.
				'<p>'. 
					__( 'Vous devriez plutôt essayer directement depuis', 'tify' ) .
					'&nbsp;'. '<a href="'. $edit_link .'" title="'. __( 'Éditer l\'utilisateur depuis l\'interface de Wordpress', 'tify' ) .'">'. __( ' l\'interface utilisateurs Wordpress.', 'tify' ) .'</a>'.
				'</p>'
			);
		endif;
		
		// Traitement des actions	
		if( method_exists( $this, 'process_bulk_action_'. $this->current_action() ) ) :
			call_user_func( array( $this, 'process_bulk_action_'. $this->current_action() ) );
		elseif( ! empty( $_REQUEST['_wp_http_referer'] ) ) :
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), $_REQUEST['_wp_http_referer'] ) );
			exit;
		endif;	
	}
	
	/** == Éxecution de l'action - creation == **/
	protected function process_bulk_action_create()
	{		
		check_admin_referer( $this->get_item_nonce_action( 'create' ) );		
		
		$data = edit_user( 0 );
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		
		if( is_wp_error( $data ) ) :
			add_action( 'admin_notices', function() use($data){
				foreach( $data->get_error_messages() as $message )
					printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', $message );
			});	
		else :
			$sendback = add_query_arg( array( $this->db()->Primary => $data ), $sendback );
			$sendback = add_query_arg( array( 'message' => 'created' ), $sendback );
			wp_redirect( $sendback );
			exit;
		endif;	
	}
	
	/** == Éxecution de l'action - mise à jour == **/
	protected function process_bulk_action_update()
	{		
		check_admin_referer( $this->get_item_nonce_action( 'update', $this->current_item() ) );			
		
		$data = edit_user( $this->current_item() );
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		
		if( is_wp_error( $data ) ) :
			add_action( 'admin_notices', function() use($data){
				foreach( $data->get_error_messages() as $message )
					printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', $message );
			});		
		else :
			$sendback = add_query_arg( array( $this->db()->Primary => $data ), $sendback );
			$sendback = add_query_arg( array( 'message' => 'updated' ), $sendback );
			wp_redirect( $sendback );
			exit;
		endif;	
	}
	
	/* = AFFICHAGE = */
	/** == Interface de selection des rôles == **/
	public function role_dropdown()
	{
		global $wp_roles;

		$roles 		= $this->Roles; 
		$selected 	= isset( $_POST['role'] ) ? wp_unslash( $_POST['role'] ) : ( $this->item ? current( array_intersect( array_values( $this->item->roles ), array_keys( get_editable_roles() ) ) ) : reset( $roles ) );
		
		$output  = "";		
		$output .= "<div id=\"role-selector\" style=\"padding:10px 0\">";
		$output .= "<label style=\"display:block;font-weight:600;font-size:14px;margin-bottom:5px;\">". __( 'Rôle', 'tify' ) ."</label>";
		$output .= "<select name=\"role\" style=\"width:100%;\">";
		foreach ( (array) $roles as $role ) :
			$name = isset( $wp_roles->role_names[$role] ) ? translate_user_role( $wp_roles->role_names[$role] ) : $role;
			$output .= "\n\t<option ". selected( $selected === $role, true, false ) ." value=\"" . esc_attr( $role ) . "\">{$name}</option>";
		endforeach;
		$output .= "</select>";
		$output .= "</div>";	
		
		return $output;
	}
		
	/** == Champ - Identifiant == **/
	public function field_user_login( $item )
	{
		$user_login = isset( $_POST['user_login'] ) ? wp_unslash( $_POST['user_login'] ) : ( $item ? $item->user_login : '' );
	?>
		<input type="text" name="user_login" id="user_login" value="<?php echo $user_login;?>" <?php if( $this->item ) : ?> disabled="disabled" <?php endif;?> class="regular-text">
	<?php
	}
	
	/** == Champ - Email == **/
	public function field_email( $item )
	{
		$user_email = isset( $_POST['email'] ) ? wp_unslash( $_POST['email'] ) : ( $item ? $item->user_email : '' );		
	?>
		<input type="email" name="email" id="email" value="<?php echo $user_email;?>" class="regular-text ltr">		
	<?php
	}
	
	/** == Champ - Prénom == **/
	public function field_first_name( $item )
	{
		$user_firstname = isset( $_POST['first_name'] ) ? wp_unslash( $_POST['first_name'] ) : ( $item ? $item->first_name : '' );
	?>
		<input type="text" name="first_name" id="first_name" value="<?php echo $user_firstname;?>" class="regular-text ltr">
	<?php
	}
	
	/** == Champ - Nom de famille == **/
	public function field_last_name( $item )
	{
		$user_lastname 	= isset( $_POST['last_name'] ) ? wp_unslash( $_POST['last_name'] ) : ( $item ? $item->last_name : '' );
	?>
		<input type="text" name="last_name" id="last_name" value="<?php echo $user_lastname;?>" class="regular-text ltr">	
	<?php
	}
	
	/** == Champ - Pseudonyme == **/
	public function field_nickname( $item )
	{
		$user_nickname 	= isset( $_POST['nickname'] ) ? wp_unslash( $_POST['nickname'] ) : ( $item ? $item->nickname : '' );
	?>	
		<input type="text" name="nickname" id="nickname" value="<?php echo $user_nickname;?>" class="regular-text ltr">
	<?php
	}
	
	/** == Champ - Url == **/
	public function field_url( $item )
	{
		$user_uri = isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : ( $item ? $item->user_url : '' );		
	?>
		<input type="text" name="url" id="url" value="<?php echo $user_uri;?>" class="regular-text ltr">		
	<?php
	}
	
	/** == Champ - Mot de passe == **/
	public function field_password( $item )
	{	
	?>
		<input type="password" name="pass1" id="pass1" value="" class="regular-text" autocomplete="off">		
	<?php
	}
	
	/** == Champ - Confirmation de mot de passe == **/
	public function field_confirm( $item )
	{		
	?>
		<input type="password" name="pass2" id="pass2" value="" class="regular-text" autocomplete="off">		
	<?php
	}
	
	/** == Affichage de la boîte de soumission du formulaire == **/
	public function submitdiv()
	{
	?>
		<div id="submitdiv" class="tify_submitdiv">
			<?php if( ! $this->item ) :?>
				<?php wp_nonce_field( $this->get_item_nonce_action( 'create' ) ); ?>
				<input type="hidden" id="<?php echo $this->db()->Primary;?>" name="<?php echo $this->db()->Primary;?>" value="0" />
				<input type="hidden" id="hiddenaction" name="action" value="create" />				
			<?php else :?>
				<?php wp_nonce_field( $this->get_item_nonce_action( 'update', $this->item->{$this->db()->Primary} ) ); ?>
				<input type="hidden" id="<?php echo $this->db()->Primary;?>" name="<?php echo $this->db()->Primary;?>" value="<?php echo $this->item->{$this->db()->Primary};?>" />
				<input type="hidden" id="hiddenaction" name="action" value="update" />
			<?php endif;?>
			<input type="hidden" id="user-id" name="user_ID" value="<?php echo get_current_user_id();?>" />
			<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url( wp_get_referer() ); ?>" />		
			
			<h3 class="hndle">
				<span><?php _e( 'Enregistrer', 'tify' );?></span>
			</h3>
			<div class="inside">
				<div class="minor_actions">
					<?php $this->minor_actions();?>
				</div>	
				<div class="major_actions">
					<?php $this->major_actions();?>
				</div>	
			</div>
		</div>			
	<?php
	}
	
	/** == Affichage des actions secondaire de la boîte de soumission du formulaire == **/
	public function minor_actions()
	{
		echo $this->role_dropdown();
	}
}