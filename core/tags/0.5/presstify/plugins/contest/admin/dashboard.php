<?php
class tiFy_Contest_AdminDashboard{
	/* = ARGUMENTS = */
	public	// Configuration
			$menu_slug,
			$hook_suffix,
			
			// Paramètres
			$list_table;
			
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Contest_Master $master ) {
        // Instanciation de la classe de référence
		$this->master = $master;
						
		// Actions et Filtres Wordpress
		add_action( 'admin_menu', array( $this, 'wp_admin_menu' ) );
		add_action( 'current_screen', array( $this, 'wp_current_screen' ) );			
    }
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Menu d'administration de Wordpress == **/
	public function wp_admin_menu(){
		// Configuration
		$this->menu_slug 		= $this->master->admin->menu_slug['parent'];
		$this->hook_suffix 		= $this->master->admin->hookname['parent'];
	}
	
	/** === === ***/
	function wp_current_screen(){
		// Bypass
		if( get_current_screen()->id !== $this->master->admin->hookname['parent'] ) 
			return;		
		
		get_current_screen()->set_parentage( preg_replace( '/\/wp-admin\//', '', $_SERVER['REQUEST_URI'] ) );
		parse_str(get_current_screen()->parent_file, $request);	

		switch( @$request['action'] ) :
			default :
			case 'list' :
				require_once( $this->master->admin->dir .'/inc/contest-table.php' );
				$this->list_table = new tiFy_Contest_AdminListTable( $this->master );
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
	
	/** == Liste des jeux concours == **/
	function view_admin_list(){			
		$this->list_table->prepare_items(); ?>
		
		<div class="wrap">
			<h2><?php _e( 'Jeux concours', 'tify' );?></h2>
			<p>				
				<?php 
					tify_facebook_sdk_login_button( 
						array( 
							'text' 				=> 	'<span class="dashicons dashicons-facebook-alt" style="vertical-align:middle;font-size:18px;"></span>'. 
													__( 'Associer mon compte à Facebook', 'deficl' ), 
							'class' 			=> 'button-primary', 
							'permissions' 		=> array( 'email', 'manage_pages', 'publish_pages', 'publish_actions', 'user_posts' ),
							'_wp_http_referer'	=> add_query_arg( array( 'page' => 'tify_contest' ), admin_url( '/admin.php' ) ),							
							'ajax_api'				=> false
						) 
					);?>
			</p>
			
			<?php $this->list_table->views(); ?>
			<form method="get">
				<?php $this->list_table->display();?>
	        </form>
		</div>
	<?php
	}
	
	/** == WIDGETS == **/
	/** == Widget "A Propos" == **/
	function widget_about(){
			if( $this->master->get_list() ) :?>
			<ul>
			<?php foreach( (array) $this->master->get_list() as $contest_id => $args ) : ?>
				<li>
					<h3><?php echo $args['title'];?></h3>
					<table class="form-table">
						<tbody>
							<tr>
								<th>Dates</th>
								<td></td>
							</tr>
						</tbody>
					</table>
				<li>
			<?php endforeach;?>
			</ul>
	<?php 	endif;
	}
}