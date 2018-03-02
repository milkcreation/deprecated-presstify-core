<?php
/* SOL 1 - CONSTANT */
//https://codex.wordpress.org/Configuring_Automatic_Background_Updates


/* SOL 2 - FILTERS */
/// ALL
//add_filter( 'automatic_updater_disabled', '__return_true' );

/// CORE
/// Enable all
//add_filter( 'auto_update_core', '__return_true' );
//// Enable development updates 
add_filter( 'allow_dev_auto_core_updates', '__return_true' ); 
//// Enable minor updates
add_filter( 'allow_minor_auto_core_updates', '__return_true' );   
//// Enable major updates
add_filter( 'allow_major_auto_core_updates', '__return_true' ); 


//add_filter( 'automatic_updates_is_vcs_checkout', '__return_false', 1 );  

/// PLUGINS
add_filter( 'auto_update_plugin', '__return_false' );

/// THEME
add_filter( 'auto_update_theme', '__return_false' );

/// TRANSLATION
add_filter( 'auto_update_translation', '__return_false' );


add_filter( 'auto_core_update_send_email', '__return_false' );

/* SOL 3 */
// https://www.wpoptimus.com/626/7-ways-disable-update-wordpress-notifications/
/// To Disable Update WordPress nag 
add_action('after_setup_theme','remove_core_updates');
function remove_core_updates()
{
if(! current_user_can('update_core')){return;}
add_action('init', create_function('$a',"remove_action( 'init', 'wp_version_check' );"),2);
add_filter('pre_option_update_core','__return_null');
add_filter('pre_site_transient_update_core','__return_null');
}

/// To Disable Plugin Update Notifications
remove_action('load-update-core.php','wp_update_plugins');
add_filter('pre_site_transient_update_plugins','__return_null');

/// To Disable all the Nags & Notifications
function remove_core_updates(){
global $wp_version;return(object) array('last_checked'=> time(),'version_checked'=> $wp_version,);
}
add_filter('pre_site_transient_update_core','remove_core_updates');
add_filter('pre_site_transient_update_plugins','remove_core_updates');
add_filter('pre_site_transient_update_themes','remove_core_updates');
