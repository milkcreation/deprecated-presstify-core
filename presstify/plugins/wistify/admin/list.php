<?php
class tiFy_Wistify_AdminList{
	/* = ARGUMENTS = */
	public	// Configuration
			$current_view,
			$list_table_link,
			$edit_form_link,
			
			// Paramètres
			$list_table,
			$edit_form;		
			
	private	// Référence
			$master;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Wistify_Master $master ){
		// Définition de la classe de référence
		$this->master = $master;
		
		// Configuration
		$this->current_view 	= isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'list_table';
		$this->list_table_link	= add_query_arg( array(  'page' => $this->master->menu_slug['list'] ), admin_url( 'admin.php' ) );
		$this->edit_form_link	= add_query_arg( array(  'page' => $this->master->menu_slug['list'], 'view' => 'edit_form', 'action' => 'edit' ), admin_url( 'admin.php' ) );
				
		// Actions et Filtres Wordpress
		add_action( 'current_screen', array( $this, 'wp_current_screen' ) );		
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Définition de l'écran courant == **/
	public function wp_current_screen( $current_screen ){
		// Bypass
		if( $current_screen->id !== $this->master->hookname['list'] ) 
			return;		

		switch( $this->current_view ) :
			case 'list_table' :
				require_once( $this->master->admin->dir .'/inc/list-table.php' );
				$this->list_table = new tiFy_Wistify_List_AdminTable( $this->master );
				break;
			case 'edit_form' :
				require_once( $this->master->admin->dir .'/inc/list-edit.php' );
				$this->edit_form = new tiFy_Wistify_List_AdminEdit( $this->master );
				tify_control_enqueue( 'switch' );
				tify_control_enqueue( 'text_remaining' );
				wp_enqueue_style( 'tify_wistify_mailing_list', $this->master->admin->uri .'/css/list-edit.css', array( ), '150406' );			
				break;
		endswitch;
	}
	
	/* = VUES = */
	/** == Redirection == **/
	public function admin_render(){
		switch( $this->current_view  ) :
			default :
			case 'list_table' :
				$this->view_admin_list();
				break;
			case 'edit_form' :
				$this->view_admin_edit();
				break;
		endswitch;
	}
	
	/** == Liste == **/
	private function view_admin_list(){
		$this->list_table->prepare_items(); 
	?>
		<div class="wrap">
			<h2>
				<?php _e( 'Listes de diffusion', 'tify' );?>
				<a class="add-new-h2" href="<?php echo $this->edit_form_link;?>"><?php _e( 'Ajouter une liste de diffusion', 'tify' );?></a>
			</h2>
			<?php $this->list_table->notifications();?>
			<?php $this->list_table->views(); ?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo $this->master->menu_slug['list'];?>">
				
				<?php $this->list_table->search_box( __( 'Recherche de liste de diffusion' ), 'wistify_mailing_list' );?>
				<?php $this->list_table->display();?>
	        </form>
		</div>
	<?php
	}

	/** == Edition == **/
	private function view_admin_edit(){
		$this->edit_form->prepare_item();		
	?>
		<div id="wistify_mailing_list-edit" class="wrap">
			<h2>
				<?php _e( 'Éditer la liste diffusion', 'tify' );?>
				<a class="add-new-h2" href="<?php echo $this->edit_form_link;?>"><?php _e( 'Ajouter une liste de diffusion', 'tify' );?></a>
			</h2>
			<?php $this->edit_form->notifications();?>
			
			<?php $this->edit_form->display();?>
		</div>
	<?php
	}
}