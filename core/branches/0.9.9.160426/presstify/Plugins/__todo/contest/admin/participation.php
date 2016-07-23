<?php
class tiFy_Contest_AdminParticipation{
	/* = ARGUMENTS = */
	public	// Configuration
			$menu_slug,
			$hook_suffix,
			$list_link,
			$edit_link,
			
			// Paramètres
			$list_table;	
			
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Contest_Master $master ){
		// Déclaration des Références
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
	
	/** === === ***/
	public function wp_current_screen(){		
		// Bypass
		if( get_current_screen()->id !== $this->master->admin->hookname['participation'] ) 
			return;		
		
		get_current_screen()->set_parentage( preg_replace( '/\/wp-admin\//', '', $_SERVER['REQUEST_URI'] ) );
		parse_str(get_current_screen()->parent_file, $request);	

		switch( @$request['action'] ) :
			default :
			case 'list' :
				require_once( $this->master->admin->dir .'/inc/participation-table.php' );
				$this->list_table = new tiFy_Contest_Participation_AdminListTable( $this->master );
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
		endswitch;
	}
	
	/** == Liste des participations == **/
	public function view_admin_list(){		
		$this->list_table->prepare_items(); 
	?>
		<div class="wrap">
			<h2><?php _e( 'Listes des participations', 'tify' );?></h2>			
			<p>
				<?php tify_contest_tpl( 'ranking_updated' );?>&nbsp;
				<a class="page-title-action" href="<?php echo wp_nonce_url( add_query_arg( array( 'page' => $_REQUEST['page'], 'action' => 'update_ranking', '_wp_http_referer' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), admin_url( 'admin.php' ) ), 'tify_contest_update_ranking_'. get_current_user_id() );?>">
					<?php _e( 'Mettre à jour' );?>
				</a>
			</p>
			<?php $this->list_table->views(); ?>	
			<form method="get">
				<input type="hidden" name="status" value="<?php echo ( ! empty( $_REQUEST['status'] ) ? esc_attr( $_REQUEST['status'] ) : '' );?>">
				
				<?php $this->list_table->search_box( __( 'Recherche de recette' ), 'tify_contest_part' );?>
				<?php $this->list_table->display();?>
	        </form>
		</div>
	<?php
	}	
}