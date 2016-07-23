<?php
namespace tiFy\Core\View\Admin\EditUser;

use tiFy\Environment\App;

class EditUser extends App
{			
	/* = ARGUMENTS = */
	// Classe de la vue
	protected 	$View;
	
	// Identifiant d'accroche de la page d'administration 
	protected 	$Hookname;
	
	/// Habilitation d'édition de l'utilisateur
	protected	$Cap 	= 'edit_users';
	
	/// Élement à éditer
	protected 	$item;	
		
	/* = CONSTRUCTEUR = */
	public function __construct( \tiFy\Core\View\Factory $viewObj )
	{
		parent::__construct();
		
		if( is_null( $this->View ) )
			$this->View = $viewObj;

		// Actions et Filtres PressTiFy		
		add_action( 'tify_taboox_register_box', array( $this, '_tify_taboox_register_box' ) );
		add_action( 'tify_taboox_register_node', array( $this, '_tify_taboox_register_node' ) );
	}
		
	/* = DECLENCHEURS = */
	/** == Affichage de l'écran courant == **/
	final public function _current_screen( $current_screen )
	{
		$this->process_bulk_actions();
	}
	
	/** == Déclaration de la boîte à onglets == **/
	final public function _tify_taboox_register_box()
	{
		$menu_slug 		= $this->View->getAdminViewAttrs( 'menu_slug', 'EditUser' );
		$parent_slug 	= $this->View->getAdminViewAttrs( 'parent_slug', 'EditUser' );
		$this->Hookname = \get_plugin_page_hookname( $menu_slug, $parent_slug );
	
		tify_taboox_register_box( 
			$this->Hookname,
			'user',
			array(
				'title'	=> __( 'Réglages des données utilisateur', 'tify' )
			)
		);
	}
	
	/** == Déclaration des sections de boîte à onglets == **/
	final public function _tify_taboox_register_node()
	{
		foreach( (array) $this->get_sections() as $id => $title ) :
			$args = array(
				'id' 			=> $id,
				'title' 		=> __( 'Informations générales', 'tify' )
			);
			
			if( method_exists( $this, "section_{$id}" ) )
				$args['cb'] = array( $this, 'section_'. $id );
			
			tify_taboox_register_node( $this->Hookname, $args );
		endforeach;
	}
	
	/* = TRAITEMENT = */
	/** == Récupération de l'élément à éditer == **/
	public function current_item() 
	{
		if ( ! empty( $_REQUEST[$this->View->getDb()->Primary] ) )
			return (int) $_REQUEST[$this->View->getDb()->Primary];

		return 0;
	}
	
	/** == Récupération de l'action courante == **/
	public function current_action()
	{		
		if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
			return $_REQUEST['action'];
		if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
			return $_REQUEST['action2'];

		return false;
	}
	
	/** == Éxecution des actions == **/
	protected function process_bulk_actions()
	{		
		// Vérification des habilitations
		$editable_user = false;
		if( $this->current_item() ) :
			foreach( (array) $this->get_roles() as $role ) :
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
		check_admin_referer( $this->View->getDb()->ID . $this->current_action() );		
		
		$data = edit_user( 0 );
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		
		if( is_wp_error( $data ) ) :
			add_action( 'admin_notices', function() use($data){
				foreach( $data->get_error_messages() as $message )
					printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', $message );
			});	
		else :
			$sendback = add_query_arg( array( $this->View->getDb()->Primary => $data ), $sendback );
			$sendback = add_query_arg( array( 'message' => 'created' ), $sendback );
			wp_redirect( $sendback );
			exit;
		endif;	
	}
	
	/** == Éxecution de l'action - mise à jour == **/
	protected function process_bulk_action_update()
	{		
		check_admin_referer( $this->View->getDb()->ID . $this->current_action() . $this->current_item() );		
		
		$data = edit_user( $this->current_item() );
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		
		if( is_wp_error( $data ) ) :
			add_action( 'admin_notices', function() use($data){
				foreach( $data->get_error_messages() as $message )
					printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', $message );
			});		
		else :
			$sendback = add_query_arg( array( $this->View->getDb()->Primary => $data ), $sendback );
			$sendback = add_query_arg( array( 'message' => 'updated' ), $sendback );
			wp_redirect( $sendback );
			exit;
		endif;	
	}
	
	/* = PARAMETRAGE = */
	/** == Récupération de la liste des rôles concernés par la vue == **/
	public function get_roles()
	{
		if( $editable_roles = array_reverse( get_editable_roles() ) )
			return array_keys( $editable_roles );
	}
	
	/** == Liste des sections d'édition == **/
	public function get_sections() 
	{
		$s = array();
		
		if( $sections = $this->View->getAdminViewAttrs( 'sections' ) ) :
			foreach( (array)  $sections as $index => $name )
				$s[$index] = $name;
		else :
			$s['general'] = __( 'Informations générales', 'tify' );
		endif;
				
		return $s;
	}
	
	/** == Préparation de l'object à éditer == **/
	public function prepare_item()
	{
		if( $this->current_item() ) :
			$this->item = get_userdata( $this->current_item() );
		else :
			$this->UserCan = true;
		endif;
	}
	
	/* = VUES = */
	/** == Affichage des champs de saisie par défaut == **/
	public function section_general()
	{
		$user_login 	= isset( $_POST['user_login'] ) 	? wp_unslash( $_POST['user_login'] ) 	: ( $this->item ? $this->item->user_login : '' );
		$user_firstname = isset( $_POST['first_name'] ) 	? wp_unslash( $_POST['first_name'] ) 	: ( $this->item ? $this->item->first_name : '' );
		$user_lastname 	= isset( $_POST['last_name'] ) 		? wp_unslash( $_POST['last_name'] ) 	: ( $this->item ? $this->item->last_name : '' );
		$user_nickname 	= isset( $_POST['nickname'] ) 		? wp_unslash( $_POST['nickname'] ) 		: ( $this->item ? $this->item->nickname : '' );
		$user_email 	= isset( $_POST['email'] ) 			? wp_unslash( $_POST['email'] ) 		: ( $this->item ? $this->item->user_email : '' );
		$user_uri 		= isset( $_POST['url'] ) 			? wp_unslash( $_POST['url'] ) 			: ( $this->item ? $this->item->user_url : '' );
	?>
		<h3><?php _e( 'Nom', 'tify' );?></h3>
		<table class="form-table">
			<tbody>
				<tr scope="row">
					<th>
						<label><?php _e( 'Identifiant  (obligatoire)', 'tify' );?></label>
					</th>
					<td>
						<input type="text" name="user_login" id="user_login" value="<?php echo $user_login;?>" <?php if( $this->item ) : ?> disabled="disabled" <?php endif;?> class="regular-text">					
					</td>
				</tr>				
				<tr scope="row">
					<th>
						<label><?php _e( 'Prénom', 'tify' );?></label>
					</th>
					<td>
						<input type="text" name="first_name" id="first_name" value="<?php echo $user_firstname;?>" class="regular-text ltr">					
					</td>
				</tr>
				<tr scope="row">
					<th>
						<label><?php _e( 'Nom', 'tify' );?></label>
					</th>
					<td>
						<input type="text" name="last_name" id="last_name" value="<?php echo $user_lastname;?>" class="regular-text ltr">					
					</td>
				</tr>
				<tr scope="row">
					<th>
						<label><?php _e( 'Pseudonyme', 'tify' );?></label>
					</th>
					<td>
						<input type="text" name="nickname" id="nickname" value="<?php echo $user_nickname;?>" class="regular-text ltr">					
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
						<input type="email" name="email" id="email" value="<?php echo $user_email;?>" class="regular-text ltr">					
					</td>
				</tr>
				<tr scope="row">
					<th>
						<label><?php _e( 'Site web', 'tify' );?></label>
					</th>
					<td>
						<input type="text" name="url" id="url" value="<?php echo $user_uri;?>" class="regular-text ltr">					
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
	
	/** == Interface de selection des rôles == **/
	public function role_selector()
	{
		global $wp_roles;

		$roles 		= $this->get_roles(); 
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
	
	/** == Affichage de la boîte de soumission du formulaire == **/
	public function submitdiv()
	{
	?>
		<div id="submitdiv" class="tify_submitdiv">
			<?php if( $this->item ) :?>
				<?php wp_nonce_field( $this->View->getDb()->ID  .'update'. $this->item->{$this->View->getDb()->Primary} ); ?>
				<input type="hidden" id="<?php echo $this->View->getDb()->Primary;?>" name="<?php echo $this->View->getDb()->Primary;?>" value="<?php echo $this->item->{$this->View->getDb()->Primary};?>" />
				<input type="hidden" id="hiddenaction" name="action" value="update" />
			<?php else :?>
				<?php wp_nonce_field( $this->View->getDb()->ID  .'create' ); ?>
				<input type="hidden" id="<?php echo $this->View->getDb()->Primary;?>" name="<?php echo $this->View->getDb()->Primary;?>" value="0" />
				<input type="hidden" id="hiddenaction" name="action" value="create" />
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
		echo $this->role_selector();
	}
	
	/** == Affichage des actions principale de la boîte de soumission du formulaire == **/
	public function major_actions()
	{
	?><div class="updating"><?php submit_button( __( 'Enregistrer', 'tify' ), 'primary', 'submit', false );?></div><?php
	}
	
	/** == Rendu == **/
	public function Render()
	{
		$this->prepare_item();
	?>
		<div class="wrap">
			<h2><?php echo $this->View->getLabel( 'edit_item' );?></h2>
		
			<form method="post" action="">
				<div style="margin-right:300px; margin-top:20px;">
					<div style="float:left; width: 100%;">
						<?php tify_taboox_display( $this->item );?>
					</div>					
					<div style="margin-right:-300px; width: 280px; float:right;">
						<?php $this->submitdiv();?>
					</div>
				</div>
			</form>		
		</div>
	<?php 
	}
}