<?php 
namespace tiFy\Core\Forms\Addons\Record;

class Upgrade extends \tiFy\Lib\Upgrade
{
	// Translation des données vers les nouvelles tables et suppression des anciennes tables
	protected function update_1611121215()
	{
		global $wpdb;
		
		$old 		= $wpdb->prefix .'mktzr_forms_records';
		$new 		= $wpdb->prefix .'tify_forms_record';
		$oldmeta 	= $wpdb->prefix .'mktzr_forms_recordmeta';
		$newmeta 	= $wpdb->prefix .'tify_forms_recordmeta';
		
		if( $wpdb->get_var("SHOW TABLES LIKE '$old'") !== $old )
			exit;
		if( $wpdb->get_var("SHOW TABLES LIKE '$new'") !== $new )
			exit;	
		if( $wpdb->get_var("SHOW TABLES LIKE '$oldmeta'") !== $oldmeta )
			exit;
		if( $wpdb->get_var("SHOW TABLES LIKE '$newmeta'") !== $newmeta )
			exit;	
					
		$wpdb->query( "INSERT INTO {$new} SELECT * from {$old}" );
		$wpdb->query( "INSERT INTO {$newmeta} SELECT * from {$oldmeta}" );		
		
		$wpdb->query( "DROP TABLE IF EXISTS {$old}, {$oldmeta};" );

		return __( 'Translation des données vers les nouvelles tables d\'enregistrement des données de formulaire -> OK', 'tify' );
	}
}