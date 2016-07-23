<?php
class tiFy_Forum_AdminTopic{
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
	function __construct( tiFy_Forum_Master $master ){
		// Définition de la classe de référence
		$this->master = $master;		
		
		// Actions et Filtres Wordpress
		add_action( 'admin_menu', array( $this, 'wp_admin_menu' ) );
		add_action( 'current_screen', array( $this, 'wp_current_screen' ) );		
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Menu de l'interface d'administration == **/
	public function wp_admin_menu(){
		// Configuration				
		$this->menu_slug 		= $this->master->menu_slug['topic'];
		$this->hook_suffix 		= $this->master->hookname['topic'];
		$this->list_link		= add_query_arg( array(  'page' => $this->menu_slug ), admin_url( 'admin.php' ) );
		$this->edit_link		= add_query_arg( array(  'page' => $this->menu_slug, 'action' => 'edit' ), admin_url( 'admin.php' ) );
	}
	
	/** == Définition de l'écran courant == **/
	public function wp_current_screen(){
		// Bypass
		if( get_current_screen()->id !== $this->master->hookname['topic'] ) 
			return;		
		get_current_screen()->set_parentage( preg_replace( '/\/wp-admin\//', '', $_SERVER['REQUEST_URI'] ) );
		parse_str(get_current_screen()->parent_file, $request);	

		switch( @$request['action'] ) :
			default :
			case 'list' :
				require_once( $this->master->admin->dir .'/inc/topic-table.php' );
				$this->list_table = new tiFy_Forum_AdminListTopic( $this->master );
				break;
			case 'edit' :
				require_once( $this->master->admin->dir .'/inc/topic-edit.php' );
				$this->edit_form = new tiFy_Forum_AdminEditTopic( $this->master );
				
				// Mise en file des scripts
				wp_enqueue_script( 'tify_controls-text_remaining' );
				wp_enqueue_style( 'topic-edit', $this->master->admin->uri .'/css/topic-edit.css', array( 'tify_controls-text_remaining' ), '151007' );					
				break;
		endswitch;
	}
		
	/* = VUES = */
	/** == Redirection == **/
	function admin_render(){
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
	
	/** == Liste == **/
	function view_admin_list(){
		$this->list_table->prepare_items(); 
	?>
		<div class="wrap">
			<h2>
				<?php _e( 'Sujets', 'tify' );?>
				<a class="add-new-h2" href="<?php echo $this->edit_link;?>"><?php _e( 'Ajouter un Sujet', 'tify' );?></a>
			</h2>
			<?php $this->list_table->notifications();?>
			<?php $this->list_table->views(); ?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo $this->menu_slug;?>">
				<input type="hidden" name="status" value="<?php echo ( ! empty( $_REQUEST['status'] ) ? esc_attr( $_REQUEST['status'] ) : 'registred' );?>">
				
				<?php $this->list_table->search_box( __( 'Recherche de sujet' ), 'tify_forum_topic' );?>
				<?php $this->list_table->display();?>
	        </form>
		</div>
	<?php
	}
	
	/** == Edition == **/
	function view_admin_edit(){
		$this->edit_form->prepare_item();
	?>
		<div class="wrap">
			<h2>
				<?php _e( 'Éditer un sujet', 'tify' );?>
				<a class="add-new-h2" href="<?php echo $this->edit_link;?>"><?php _e( 'Ajouter un autre sujet', 'tify' );?></a>
			</h2>
			<?php $this->edit_form->notifications();?>
			<?php $this->edit_form->display();?>
		</div>
	<?php
	}	
}