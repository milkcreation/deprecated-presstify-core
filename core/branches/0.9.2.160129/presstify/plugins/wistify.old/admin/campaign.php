<?php
class tiFy_Wistify_AdminCampaign{
	/* = ARGUMENTS = */
	public	// Configuration
			/// Environnement
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
		/// Environnement
		$this->current_view 		= isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'list_table';
		$this->list_table_link		= add_query_arg( array( 'page' => $this->master->menu_slug['campaign'] ), admin_url( 'admin.php' ) );
		$this->edit_form_link		= add_query_arg( array( 'page' => $this->master->menu_slug['campaign'], 'view' => 'edit_form', 'action' => 'edit' ), admin_url( 'admin.php' ) );
		
		// Actions et Filtres Wordpress
		add_action( 'current_screen', array( $this, 'wp_current_screen' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Définition de l'écran courant == **/
	public function wp_current_screen( $current_screen ){
		// Bypass
		if( $current_screen->id !== $this->master->hookname['campaign'] ) 
			return;		
			
		switch( $this->current_view ) :
			default :
			case 'list_table' :
				require_once( $this->master->admin->dir .'/inc/campaign-table.php' );
				$this->list_table = new tiFy_Wistify_Campaign_AdminTable( $this->master );
				// Mise en file des scripts				
				wp_enqueue_style( 'tify_wistify_campaign-table', $this->master->admin->uri .'/css/campaign-table.css', array( ), '151019' );
				break;
			case 'edit_form' :
				require_once( $this->master->admin->dir .'/inc/campaign-edit.php' );
				$this->edit_form = new tiFy_Wistify_Campaign_AdminEdit( $this->master );
				
				// Initialisation des scripts
				wp_enqueue_style( 'tify_wistify_campaign', $this->master->admin->uri .'/css/campaign-edit.css', array( ), '150403' );
				$step = ! empty( $_REQUEST['step'] ) ? (int) $_REQUEST['step'] : 1;
				switch( $step ) :
					case 1 :
						tify_control_enqueue( 'text_remaining' );
						break;
					case 2 :
						wp_enqueue_script( 'tify_wistify_campaign-step2', $this->master->admin->uri .'/js/campaign-edit-step2.js', array( 'jquery' ), '150928', true );
						break;
					case 3 :
						wp_enqueue_style( 'tify_wistify_campaign-step3', $this->master->admin->uri .'/css/campaign-edit-step3.css', array( 'tify_suggest' ), '150918' );
						wp_enqueue_script( 'tify_wistify_campaign-step3', $this->master->admin->uri .'/js/campaign-edit-step3.js', array( 'jquery', 'tify_suggest' ), '150918', true );
						break;
					case 4 :						
						tify_control_enqueue( 'switch' );
					break;
					case 5 :
						wp_enqueue_style( 'tify_wistify_campaign-step5', $this->master->admin->uri .'/css/campaign-edit-step5.css', array( 'tify_controls-touch_time' ), '150918' );
						wp_enqueue_script( 'tify_wistify_campaign-step5', $this->master->admin->uri .'/js/campaign-edit-step5.js', array( 'jquery', 'tify_controls-touch_time' ), '150918', true );
						wp_localize_script( 'tify_wistify_campaign-step5', 'wistify_campaign', array( 
								'total_in' 			=> __( 'sur un total de', 'tify' ),
								'preparing' 		=> __( 'Préparation en cours ...', 'tify' ),
								'emails_ready' 		=> __( 'Emails prêts', 'tify' )				
							)
						);
					break;
				endswitch;							
				break;
		endswitch;
	}
	
	/* = VUE = */
	/** == Redirection == **/
	public function admin_render(){
		switch( $this->current_view ) :
			default :
			case 'list_table' :
				$this->view_admin_list_table();
				break;
			case 'edit_form' :
				$this->view_admin_edit_form();
				break;
		endswitch;
	}

	/** == Liste == **/
	private function view_admin_list_table(){
		$this->list_table->prepare_items();
	?>
		<div class="wrap">
			<h2>
				<?php _e( 'Campagnes d\'emailing', 'tify' );?>
				<a class="add-new-h2" href="<?php echo $this->edit_form_link;?>"><?php _e( 'Ajouter une campagne', 'tify' );?></a>
			</h2>
			<?php $this->list_table->notifications();?>
			<?php $this->list_table->views(); ?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo $this->master->menu_slug['campaign'];?>">
				
				<?php $this->list_table->search_box( __( 'Recherche de campagne' ), 'wistify_campaign' );?>
				<?php $this->list_table->display();?>
	        </form>
		</div>
		
		<div id="progress-infos" class="tifybox" style="display:none;">			
			<h3><?php _e( 'Préparation en cours ...', 'tify');?></h3>
			<div class="inside">
				<div class="progress">
					<div class="progress-bar"></div>
					<div class="indicator">
						<span class="processed">0</span>/<span class="total"></span>
					</div>					
				</div>
			</div>
		</div>
	<?php
	}
	
	/** == Edition == **/
	private function view_admin_edit_form(){
		$this->edit_form->prepare_item();
	?>
		<div id="wistify_campaign-edit" class="wrap">
			<h2>
				<?php _e( 'Éditer la campagne d\'emailing', 'tify' );?>
			</h2>
						
			<?php $this->edit_form->notifications();?>
			
			<?php $this->edit_form->top_nav();?>
			
			<?php $this->edit_form->display();?>
		</div>
	<?php
	}
}