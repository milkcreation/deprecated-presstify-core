<?php
/**
 * 
 */ 
function mkact_db_add_management_menu() {
	add_management_page(
		__( 'Database Options', 'mkact-addon-db' ),
		__( 'Database', 'mkact-addon-db' ),
		apply_filters( 'milk_database_capability', 'manage_options' ),
		'mkact_db_tools',
		'mkact_db_management_render_page'
	);
}
add_action( 'admin_menu', 'mkact_db_add_management_menu' );

/**
 * Entête de la page de rendu des options
 */
function mkact_db_management_render_page() {
?>
	<div class="wrap">
		<?php screen_icon(); ?>
        <h2><?php _e( 'Database Admin',  'mkact-addon-db' ); ?></h2>
        <?php settings_errors(); ?>
  		<?php mkact_db_tools_tab_render();?>
	</div>	
<?php
}

/**
 * 
 */
function mkact_db_tools_tab_render(){
	$url =  WP_PLUGIN_URL.'/'.plugin_basename( dirname(dirname(__FILE__) ) ) ;
?><iframe src='<?php echo $url;?>/adminer/adminer/loader.php?username=<?php echo DB_USER; ?>&amp;db=<?php echo DB_NAME; ?>' width="100%" height="800" name="adminer"></iframe><?php
}