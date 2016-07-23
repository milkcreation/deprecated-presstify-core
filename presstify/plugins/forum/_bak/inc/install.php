<?php
/**
 * -------------------------------------------------------------------------------
 *	Forums
 * -------------------------------------------------------------------------------
 *
 * @name 		Milkcreation Forums
 * @package    	G!PRESS SNCF PROXIMITES - Espaces Collaboratifs
 * @copyright 	Milkcreation 2012
 * @link 		http://g-press.com/plugins/mocca
 * @author 		Jordy Manner
 * @version 	1.1
 */

global $mkforums_db_version;
$mkforums_db_version = '20120925.1055';
  
/**
 * 
 */ 
function mkforums_install() {
	global $wpdb, $mkforums_db_version;
	
	$current_version = get_option('mkforums_db_version', '0');
	$upgraded = false;
	
	// Mise à jour des données de fichier de carte
	if ( version_compare($current_version, '20120925.1055', '<') ) {
		$topics = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE $wpdb->posts.post_type = 'mocca-topics';");

		foreach( $topics as $topic_id )
			$wpdb->update( $wpdb->posts, array('post_type'=>'mktopics'), array( 'ID'=> $topic_id ) );
			
		$upgraded = '20120925.1055';
	}
	if ( $upgraded )
		update_option( 'mkforums_db_version', $mkforums_db_version );
}

add_action('init', 'mkforums_install');
register_activation_hook( __FILE__, 'mkforums_install' ); 