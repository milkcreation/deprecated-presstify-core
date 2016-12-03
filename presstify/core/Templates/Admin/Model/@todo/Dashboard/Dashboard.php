<?php
class Dashboard
{	
	function Render()
	{
		$screen = get_current_screen();		
		add_meta_box( 'tiFy_info', __( 'A propos de PresstiFy', 'tify' ), array( $this, 'admin_dashboard_widget_about' ), $screen, 'normal' );
		
		$columns = absint( $screen->get_columns() );
		$columns_css = '';
		if ( $columns )
			$columns_css = " columns-$columns";
	?>
		<h2><?php _e( 'Tableau de bord Presstify', 'tify' );?></h2>
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
	
	/**
	 * Widget "A Propos"
	 */
	function admin_dashboard_widget_about(){
		?>
		<div>
			<?php echo __( 'Version', 'tify' ) ." ". $this->tiFy->plugin_data['Version'];?>
		</div>
		<?php
	}
}
