<?php
/*
Addon Name: Dashboard
Addon ID: dashboard
Callback: tiFy_Forms_Addon_Dashboard
Version: 1.150815
Author: Jordy Manner
Author URI: http://profile.milkcreation.fr/jordy.manner
*/

class tiFy_Forms_Addon_Dashboard extends tiFy_Forms_Addon{
	/* = ARGUMENTS = */
	
	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Forms $master ) {
        parent::__construct( $master );
		
		// Actions et Filtres Wordpress
		add_action( 'admin_menu', array( $this, 'wp_admin_menu' ), 9 );			
    }
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Menu d'administration de Wordpress == **/
	function wp_admin_menu(){
		add_menu_page( __( 'Formulaires dynamiques', 'tify' ), __( 'Formulaires', 'tify' ), 'manage_options', 'tify_forms', null, 'dashicons-feedback' );
		add_submenu_page( 'tify_forms', __( 'Tableau de bord', 'tify' ), __( 'Tableau de bord', 'tify' ), 'manage_options', 'tify_forms', array( $this, 'render' ) );
	}
	
	/** == Affichage du Tableau de Bord == **/
	function render(){
		$screen = get_current_screen();		
		add_meta_box( 'tiFy_info', __( 'A propos des formulaires', 'tify' ), array( $this, 'admin_dashboard_widget_about' ), $screen, 'normal' );
		
		$columns = absint( $screen->get_columns() );
		$columns_css = '';
		if ( $columns )
			$columns_css = " columns-$columns";
	?>
		<h2><?php _e( 'Tableau de bord des formulaires', 'tify' );?></h2>
		<div id="dashboard-widgets" class="metabox-holder<?php echo $columns_css; ?>">
			<div id="postbox-container-1" class="postbox-container">
			<?php do_meta_boxes( $screen->id, 'normal', '' ); ?>
			</div>
			<div id="postbox-container-2" class="postbox-container">
			<?php do_meta_boxes( $screen->id, 'side', '' ); ?>
			</div>
			<div id="postbox-container-3" class="postbox-container">
			<?php do_meta_boxes( $screen->id, 'column3', '' ); ?>
			</div>
			<div id="postbox-container-4" class="postbox-container">
			<?php do_meta_boxes( $screen->id, 'column4', '' ); ?>
			</div>
		</div>	
	<?php	
	}
	
	/** == Widget "A Propos" == **/
	function admin_dashboard_widget_about(){
	?><div></div><?php
	}
}