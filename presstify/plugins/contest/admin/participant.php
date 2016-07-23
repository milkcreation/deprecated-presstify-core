<?php
class tiFy_Contest_AdminParticipant{
	/* = ARGUMENTS = */
	public	// Configuration
			$menu_slug,
			$hook_suffix,
			$list_link,
			$edit_link,
			
			// Paramètres
			$list_table,
			$edit_form;	
			
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Contest_Master $master ){
		// Instanciation de la classe de référence
		$this->master = $master;
						
		// Actions et Filtres Wordpress
		add_action( 'admin_menu', array( $this, 'wp_admin_menu' ) );
		add_action( 'current_screen', array( $this, 'wp_current_screen' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Menu d'administration == **/
	public function wp_admin_menu(){
		// Configuration
		$this->menu_slug 		= $this->master->admin->menu_slug['participant'];
		$this->hook_suffix 		= $this->master->admin->hookname['participant'];
		$this->list_link		= add_query_arg( array( 'page' => $this->menu_slug ), admin_url( 'admin.php' ) );
		$this->edit_link		= add_query_arg( array( 'page' => $this->menu_slug, 'action' => 'edit' ), admin_url( 'admin.php' ) );
	}
	
	/** == Définition de la page courante == **/
	public function wp_current_screen(){
		// Bypass
		if( get_current_screen()->id !== $this->master->admin->hookname['participant'] ) 
			return;		
		
		get_current_screen()->set_parentage( preg_replace( '/\/wp-admin\//', '', $_SERVER['REQUEST_URI'] ) );
		parse_str(get_current_screen()->parent_file, $request);	

		switch( @$request['action'] ) :
			default :
			case 'list' :
				require_once( $this->master->admin->dir .'/inc/participant-table.php' );
				$this->list_table = new tiFy_Contest_Participant_AdminListTable( $this->master );
				break;
			case 'edit' :
				require_once( $this->master->admin->dir .'/inc/participant-edit.php' );
				$this->edit_form = new tiFy_Contest_Participant_AdminEditForm( $this->master );		
				break;
		endswitch;
	}

	/* = VUE = */
	/** == Redirection == **/
	public function admin_render(){
		$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'list';
		switch( $action ) :
			default :
			case 'list' :
				$this->view_admin_list();
				break;
			case 'edit' :
				$this->view_admin_edit();
				break;
		endswitch;
	}
	
	/** == Liste des participants == **/
	private function view_admin_list(){
		$this->list_table->prepare_items(); 
	?>
		<div class="wrap">
			<h2>
				<?php _e( 'Listes des contributeurs', 'tify' );?>
				<a class="add-new-h2" href="<?php echo $this->edit_link;?>"><?php _e( 'Ajouter un participant', 'tify' );?></a>
			</h2>
			
			<?php $this->list_table->views(); ?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo $this->menu_slug;?>">
				<input type="hidden" name="status" value="<?php echo ( ! empty( $_REQUEST['status'] ) ? esc_attr( $_REQUEST['status'] ) : '' );?>">
				
				<?php $this->list_table->search_box( __( 'Recherche de contributeur', 'tify' ), 'tify_contest_participants' );?>
				<?php $this->list_table->display();?>
	        </form>
		</div>
	<?php
	}
	
	/*** === Edition d'un participant === ***/
	private function view_admin_edit(){
		$this->edit_form->prepare_item();
	?>
		<div class="wrap">
			<h2>
				<?php _e( 'Editer un client', 'tify' );?>
				<a class="add-new-h2" href="<?php echo $this->edit_link;?>"><?php _e( 'Ajouter un client', 'tify' );?></a>
			</h2>
			<?php $this->edit_form->notifications();?>
						
			<form <?php $this->edit_form->form_attrs();?>>
				<?php $this->edit_form->display();?>
			</form>
		</div>
	<?php
	}
}