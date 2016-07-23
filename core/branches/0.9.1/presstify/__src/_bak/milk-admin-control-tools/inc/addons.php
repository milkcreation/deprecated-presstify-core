<?php
/**
 * 
 */
function mkact_registered_addons(){
	$addons = array(
		/*'admin-menu' => array(
			'path' => ''
		),*/
		'site-blocker' => array(
			'path' => 'site-blocker/site-blocker.php',
			'tools_menu_slug' => 'milk_site_blocker_tools',
			'tools_menu_function' => 'mkbkr_management_form_render'				
		),
		'browser' => array(
			'path' => 'browser/browser.php',			
			'tools_menu_slug' => 'mk_file_browser_tools',
			'tools_menu_function'=> 'mk_file_browser_elfinder_init'			
		),
		'database' => array(
			'path' => 'database/database.php',
			'tools_menu_slug' => 'mkact_db_tools',
			'tools_menu_function'=> 'mkact_db_tools_tab_render'
		),
		/*'files-export' => array(
			'path' => ''
		),*/
		
		'post-thumbnails' => array(
			'path' => 'thumbnails/thumbnails.php',
			'tools_menu_slug' => 'mk_post_thumbnails_tools',
			'tools_menu_function'=> 'mk_post_thumbnails_tools_form_render'
		),
		'relocate' => array(
			'path' => 'relocate/relocate.php',
			'tools_menu_slug' => 'mkreloc_tools',
			'tools_menu_function'=> 'mkreloc_tools_render_page'
		),
		/*'user-roles' => array(
			'path' => ''
		)*/ 
	);

	return $addons;
}

/**
 * 
 */
function mkact_get_addon( $slug ){
	$addons = mkact_registered_addons();
	if( isset( $addons[$slug]) )
		return $addons[$slug];
}

/**
 * 
 */
function mkact_get_activated_addons(){
	$activated_addons = array();	
	
	if( $addons = get_option( 'mkcat_activated_addons', array(  'site-blocker', 'post-thumbnails', 'database', 'browser', 'relocate' ) ) )
		foreach( $addons as $slug )
			$activated_addons[ $slug ] = mkact_get_addon( $slug );
		
	return $activated_addons;	
}

/**
 * 
 */
function mkact_get_activated_addons_tools_menu_slug(){
	if( ! mkact_get_activated_addons() )
		return;
	
	$tools_menu_slug = array();
	foreach( mkact_get_activated_addons() as $addon_datas )
		if( !empty( $addon_datas['tools_menu_slug']) )
		$tools_menu_slug[] = $addon_datas['tools_menu_slug'];
	
	return $tools_menu_slug;	
}

/**
 * 
 */
function mkact_load_addons(){
	$activated_addons = mkact_get_activated_addons();
	foreach( (array) $activated_addons as $activated_addon_slug => $addon_datas  )
		if( ! empty($addon_datas['path']) )	
			require_once MKACT_DIR.'/add-ons/'.$addon_datas['path'];
} 
