<?php
class tiFy_Wistify_AdminSubscriber{
		/* = ARGUMENTS = */
	public	// Configuration
			$current_view,
			$list_table_link,
			$edit_form_link,
			$import_link,
			
			// Paramètres
			$list_table,
			$edit_form,
			$import;			
			
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Wistify_Master $master ){
		// Définition de la classe de référence
		$this->master = $master;		
		
		// Configuration
		$this->current_view 		= isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'list_table';
		$this->list_table_link		= add_query_arg( array(  'page' => $this->master->menu_slug['subscriber'] ), admin_url( 'admin.php' ) );
		$this->edit_form_link		= add_query_arg( array(  'page' => $this->master->menu_slug['subscriber'], 'view' => 'edit_form', 'action' => 'edit' ), admin_url( 'admin.php' ) );
		$this->import_link			= add_query_arg( array(  'page' => $this->master->menu_slug['subscriber'], 'view' => 'import' ), admin_url( 'admin.php' ) );
		
		// Actions et Filtres Wordpress
		add_action( 'admin_init', array( $this, 'wp_admin_init' ) );
		add_action( 'current_screen', array( $this, 'wp_current_screen' ) );		
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation de l'interface d'administration == **/
	public function wp_admin_init(){
		require_once( $this->master->admin->dir .'/inc/subscriber-import.php' );
		$this->import = new tiFy_Wistify_Subscriber_AdminImport( $this->master );
	}
	
	/** == Définition de l'écran courant == **/
	public function wp_current_screen( $current_screen ){
		// Bypass
		if( $current_screen->id !== $this->master->hookname['subscriber'] ) 
			return;		
		
		switch( $this->current_view ) :
			default :
			case 'list_table' :
				require_once( $this->master->admin->dir .'/inc/subscriber-table.php' );
				$this->list_table = new tiFy_Wistify_Subscriber_AdminTable( $this->master );
				break;
			case 'edit_form' :
				require_once( $this->master->admin->dir .'/inc/subscriber-edit.php' );
				$this->edit_form = new tiFy_Wistify_Subscriber_AdminEdit( $this->master );
				
				// Initialisation des scripts
				wp_enqueue_style( 'tify_wistify_subscriber', $this->master->admin->uri .'/css/subscriber-edit.css', array( ), '150406' );			
				break;
			case 'import' :
				$this->import->table_init();
				break;
		endswitch;
	}
		
	/* = VUES = */
	/** == Redirection == **/
	public function admin_render(){
		switch( $this->current_view ) :
			default :
			case 'list_table' :
				$this->view_admin_list();
				break;
			case 'edit_form' :
				$this->view_admin_edit();
				break;
			case 'import' :
				$this->import->admin_render();
				break;
		endswitch;
	}
	
	/** == Liste == **/
	private function view_admin_list(){
		$this->list_table->prepare_items(); 
	?>
		<div class="wrap">
			<h2>
				<?php _e( 'Abonnés', 'tify' );?>
				<a class="add-new-h2" href="<?php echo $this->edit_form_link;?>"><?php _e( 'Ajouter un abonné', 'tify' );?></a>
				<a class="add-new-h2" href="<?php echo $this->import_link;?>"><?php _e( 'Import d\'abonnés', 'tify' );?></a>
			</h2>
			<?php $this->list_table->notifications();?>
			<?php $this->list_table->views(); ?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo $this->master->menu_slug['subscriber'];?>">
				
				<?php $this->list_table->search_box( __( 'Recherche d\'abonné' ), 'wistify_subscriber' );?>
				<?php $this->list_table->display();?>
	        </form>
		</div>
	<?php
	}
	
	/** == Edition == **/
	private function view_admin_edit(){
		$this->edit_form->prepare_item();
	?>
		<div class="wrap">
			<h2>
				<?php _e( 'Éditer un abonné', 'tify' );?>
				<a class="add-new-h2" href="<?php echo $this->edit_form_link;?>"><?php _e( 'Ajouter un autre abonné', 'tify' );?></a>
			</h2>
			<?php $this->edit_form->notifications();?>
			<?php $this->edit_form->display();?>
		</div>
	<?php
	}	
}