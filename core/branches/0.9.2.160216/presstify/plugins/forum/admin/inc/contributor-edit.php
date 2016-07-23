<?php
class tiFy_Forum_AdminEditContributor extends tiFy_AdminView_Edit_Form{
	/* = ARGUMENTS = */
	public	// Configuration
			$roles,
			
			// Paramètres
			$item,
			$is_profile_page,
			$current_user;
			
	private	// Référence
			$master,
			$main;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Forum_Master $master ){
		// Définition des classes de référence
		$this->master 	= $master;
		$this->main 	= $this->master->admin->contributor;
		
		// Configuration
		$this->roles 	= $this->master->roles;
		
		// Actions et filtres Wordpress
		add_action( 'load-'. $this->main->hook_suffix, array( $this, 'wp_load' ) );
	}
	
	/* = ACTION ET FILTRE WORDPRESS = */
	/** == Au chargement complet de Wordpress == **/
	public function wp_load(){
		tify_form_set_current( $this->master->forms->subscribe_form_id );
	}
		
	/* = PREPARATION DE L'OBJECT A ÉDITER = */
	public function prepare_item(){
		$user_id 	= ( isset( $_REQUEST['user_id'] ) ) ? (int) $_REQUEST['user_id'] : 0;
		$this->item = get_user_to_edit( $user_id );
		$this->current_user = wp_get_current_user();
		if ( ! $this->is_profile_page )
			$this->is_profile_page =  ( $this->item->ID == $this->current_user->ID );
	}
	
	/* = VUES = */
	/** == Attribututs de la balise formulaire == **/
	function form_attrs(){		
		echo 	"id=\"your-profile\"".
				" action=\"\"".
				" method=\"post\"".
				" novalidate=\"novalidate\"".
				do_action( 'user_edit_form_tag' );
	}
	
	/** == Champs cachés == **/
	function hidden_fields(){
		wp_nonce_field( 'update-user_' . $this->item->ID );
	?>
		<input type="hidden" name="from" value="profile" />
		<input type="hidden" name="checkuser_id" value="<?php echo get_current_user_id(); ?>" />
		<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $this->item->ID ); ?>" />
	<?php
	}
	
	/** == Formulaire de saisie == **/
	function form(){
		global $tify_tabooxes_master;
		$tify_tabooxes_master->boxes[get_current_screen()->id]->box_render();
	}
	
	/* = NOTIFICATIONS =*/
	/** == Affichage == **/
	function notifications(){
		if ( $notice = $this->get_notices() ) : ?>
			<div id="notice" class="notice notice-warning">
				<p><?php echo $notice ?></p>
			</div>
		<?php endif; ?>
		<?php if ( $error = $this->get_errors() ) : ?>
			<div id="error" class="notice notice-error">
				<p><?php echo $error ?></p>
			</div>
		<?php endif; ?>
		<?php if ( $message = $this->get_messages() ) : ?>
			<div id="message" class="updated notice notice-success is-dismissible">
				<p><?php echo $message; ?></p>
			</div>
		<?php endif;
	}
	
	/** == Récupération des notices == **/
	function get_notices(){
		if( ! isset( $_GET['notice'] ) )
			return;
		switch( $_GET['notice'] ) :
			default :
				break;	
		endswitch;
	}
	
	/** == Récupération des erreurs == **/
	function get_errors(){
		/*if( $this->main->master->tify_forms->mkcf->errors->has() )
			return $this->main->master->tify_forms->mkcf->errors->display();
		elseif( ! isset( $_GET['error'] ) )
			return;
		switch( $_GET['error'] ) :
			default :
				break;		
		endswitch;*/
	}
	
	/** == Récupération des messages == **/
	function get_messages(){
		if( ! isset( $_GET['message'] ) )
			return;
		switch( $_GET['message'] ) :
			default :
				break;
			case 1 :
				return __( 'L\'utilisateur a été enregistrée avec succès', 'tify' );
				break;		
		endswitch;	
	}	
}