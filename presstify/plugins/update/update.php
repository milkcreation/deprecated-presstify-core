<?php
/*
Plugin Name: Update
Plugin URI: http://presstify.com/core/addons/update
Description: Désactivation des mise à jour de Wordpress
Version: 1.150410
Author: Milkcreation
Author URI: http://milkcreation.fr
*/
add_filter( 'automatic_updater_disabled', '__return_true' );
//add_filter( 'auto_update_core', '__return_false' );

add_filter( 'auto_update_plugin', '__return_false' );
add_filter( 'auto_update_theme', '__return_false' );

add_filter( 'auto_update_translation', '__return_false' );

add_filter( 'auto_core_update_send_email', '__return_false' );