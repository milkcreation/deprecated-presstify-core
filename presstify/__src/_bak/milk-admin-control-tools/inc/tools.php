<?php
/**
 * 
 */
function mkact_tools_tabs(){
	global $submenu, $mkact_tools_tabs;
	
	if( empty( $submenu['tools.php'] ) )
		return;
	$mkact_tools_tabs = array();
	
	foreach(  $submenu['tools.php']  as $key => $tool_menu ) :
		foreach( mkact_get_activated_addons() as $slug => $datas ) :
			if( !empty( $datas['tools_menu_slug']) && ( $tool_menu[2] == $datas['tools_menu_slug'] )   ) :
				if( current_user_can( $tool_menu[1] ) ):
					$mkact_tools_tabs[$slug] = $tool_menu;
					if( isset( $datas['tools_menu_function'] ) )
						add_action( 'mkact_tool_tab_'.$slug, $datas['tools_menu_function'] );
					unset( $submenu['tools.php'][$key] );
				endif;	
			endif;
		endforeach;	
	endforeach;			
	
	return $mkact_tools_tabs;	
}
add_action( 'admin_menu',  'mkact_tools_tabs', 999 );
 
/**
 * 
 */
function mkact_tools_admin_menu(){
	add_management_page(
		__( 'Admin Control Tools Settings', 'milk-admin-control-tools' ), 
		__( 'Admin Control Tools', 'milk-admin-control-tools' ), 
		'manage_options', 
		'mkact_tools', 
		'mkact_tools_render_page'
	);
}
add_action('admin_menu', 'mkact_tools_admin_menu');

/**
 * 
 */
function mkact_tools_render_page(){
	global $mkact_tools_tabs;
	
	// Bypass
	if( !$mkact_tools_tabs)
		return;
	
	$current_tab = ( ! empty( $_REQUEST['tab'] ) )? $_REQUEST['tab'] : array_shift( array_keys( array_slice( $mkact_tools_tabs, 0,1 ) ) ); 
?>
	<div class="wrap">
		<?php screen_icon(); ?>
        <h2><?php _e( 'Admin Control Tools Settings',  'milk-admin-control-tools' ); ?></h2>
		<h2 class="nav-tab-wrapper">
		<?php foreach( $mkact_tools_tabs as $tool_tab_slug => $tool_tab ) : ?>
			<a class="nav-tab <?php if( $tool_tab_slug == $current_tab ) echo 'nav-tab-active'?>" href="<?php echo add_query_arg( 'tab', $tool_tab_slug, mkpack_get_current_page_url() );?>"><?php echo $tool_tab[0];?></a>
		<?php endforeach; ?>	
		</h2>
		<div id="mkact-tools-content" style="margin-top:10px;">
			<?php do_action( 'mkact_tool_tab_'.$current_tab );?>
		</div>	
				
	</div>
<?php			
}	
