<?php
// search and include wp-load.php
function get_wp_root( $directory ) {
	global $wp_root;
	
	foreach( glob( $directory . "/*" ) as $f ) {
		
		if ( 'wp-load.php' == basename($f) ) {
			$wp_root = str_replace( "\\", "/", dirname($f) );
			return TRUE;
		}
		
		if ( is_dir($f) )
			$newdir = dirname( dirname($f) );
	}
	
	if ( isset($newdir) && $newdir != $directory ) {
		if ( get_wp_root ( $newdir ) )
			return FALSE;
	}
	
	return FALSE;
} // end function to find wp-load.php

if ( ! function_exists('add_action') ) {
	
	get_wp_root( dirname( dirname(__FILE__) ) );
	if ( ! empty( $wp_siteurl ) ) {
		define( 'WP_USE_THEMES', FALSE );
		include_once ( $wp_siteurl . '/wp-load.php' );
	} else if ( $wp_root ) {
		define( 'WP_USE_THEMES', FALSE );
		include_once ( $wp_root . '/wp-load.php' );
	} else {
		die( 'Cheatin&#8217; uh?');
		exit;
	}
}

if ( ! current_user_can('unfiltered_html') )
	wp_die( __('Cheatin&#8217; uh?') );

function adminer_object() {
	// required to run any plugin
	include_once "../plugins/plugin.php";
	
	// autoloader
	foreach (glob("../plugins/*.php") as $filename) {
		include_once $filename;
	}
	
	$plugins = array(
		// specify enabled plugins here
		new AdminerDatabaseHide(array('information_schema')),
		new AdminerDumpZip,
		new AdminerDumpXml,
		new AdminerFrames,
		//~ new AdminerSqlLog("past-" . rtrim(`git describe --tags --abbrev=0`) . ".sql"),
		//~ new AdminerEditCalendar("<script type='text/javascript' src='../externals/jquery-ui/jquery-1.4.4.js'></script>\n<script type='text/javascript' src='../externals/jquery-ui/ui/jquery.ui.core.js'></script>\n<script type='text/javascript' src='../externals/jquery-ui/ui/jquery.ui.widget.js'></script>\n<script type='text/javascript' src='../externals/jquery-ui/ui/jquery.ui.datepicker.js'></script>\n<script type='text/javascript' src='../externals/jquery-ui/ui/jquery.ui.mouse.js'></script>\n<script type='text/javascript' src='../externals/jquery-ui/ui/jquery.ui.slider.js'></script>\n<script type='text/javascript' src='../externals/jquery-timepicker/jquery-ui-timepicker-addon.js'></script>\n<link rel='stylesheet' href='../externals/jquery-ui/themes/base/jquery.ui.all.css'>\n<style type='text/css'>\n.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }\n.ui-timepicker-div dl { text-align: left; }\n.ui-timepicker-div dl dt { height: 25px; }\n.ui-timepicker-div dl dd { margin: -25px 0 10px 65px; }\n.ui-timepicker-div td { font-size: 90%; }\n</style>\n", "../externals/jquery-ui/ui/i18n/jquery.ui.datepicker-%s.js"),
		//~ new AdminerTinymce("../externals/tinymce/jscripts/tiny_mce/tiny_mce_dev.js"),
		//~ new AdminerWymeditor(array("../externals/wymeditor/src/jquery/jquery.js", "../externals/wymeditor/src/wymeditor/jquery.wymeditor.js", "../externals/wymeditor/src/wymeditor/jquery.wymeditor.explorer.js", "../externals/wymeditor/src/wymeditor/jquery.wymeditor.mozilla.js", "../externals/wymeditor/src/wymeditor/jquery.wymeditor.opera.js", "../externals/wymeditor/src/wymeditor/jquery.wymeditor.safari.js")),
		new AdminerFileUpload(""),
		new AdminerSlugify,
		new AdminerTranslation,
		new AdminerForeignSystem,
		new AdminerEnumOption,
		new AdminerTablesFilter,
		new AdminerEditForeign,
	);
	class AdminerCustomization extends AdminerPlugin {
		function name() {
			
			return get_option('blogname');
		}
		
		function credentials() {
			global $wpdb;			
			return array(DB_HOST, DB_USER, DB_PASSWORD);
		}
		
		function database() {
			global $wpdb;			
			return DB_NAME;
		}
		
		function databases(){
			return false;
		}
		
		function login($login, $password) {
			global $wpdb;			
			return ($login == DB_USER);
		}
	}
	return new AdminerCustomization($plugins);
}
include_once ( 'index.php' );