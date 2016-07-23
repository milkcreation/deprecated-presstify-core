<?php
class tiFy_Wistify_AdminReport{
	/* = ARGUMENTS = */
	public	// Configuration
			$current_view,
			$list_table_link,
			
			// Paramètres
			$list_table;				
			
	private	// Référence
			$master;
			
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Wistify_Master $master ){
		// Définition de la classe de référence
		$this->master = $master;
		
		// Configuration
		$this->current_view 	= isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'list_table';
		$this->list_table_link	= add_query_arg( array(  'page' => $this->master->menu_slug['report'] ), admin_url( 'admin.php' ) );
		
		// Actions et Filtres Wordpress
		add_action( 'current_screen', array( $this, 'wp_current_screen' ) );		
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Définition de l'écran courant == **/
	public function wp_current_screen( $current_screen ){
		// Bypass
		if( $current_screen->id !== $this->master->hookname['report'] ) 
			return;		
				
		require_once( $this->master->admin->dir .'/inc/report-table.php' );
		$this->list_table = new tiFy_Wistify_Report_AdminTable( $this->master );
		
		// Mise en file des scripts
		wp_enqueue_style( 'bootstrap-popovers' );
		wp_enqueue_script( 'tify_wistify_report-table', $this->master->admin->uri .'/js/report-table.js', array( 'bootstrap-popovers' ), '150915' );		
		wp_enqueue_style( 'tify_wistify_report-table', $this->master->admin->uri .'/css/report-table.css', array( ), '150926' );
	}
	
	/* = PAGE D'ADMINISTRATION = */
	public function admin_render(){
		$this->list_table->prepare_items(); 
	?>
		<div class="wrap">
			<h2>
				<?php _e( 'Rapport d\'expédition', 'tify' );?>
			</h2>
			<?php $this->list_table->views(); ?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo $this->master->menu_slug['report'];?>">
				
				<?php $this->list_table->search_box( __( 'Recherche de destinataire', 'tify' ), 'wistify_reports' );?>
				<?php $this->list_table->display();?>
	        </form>
		</div>
	<?php
	}	
}