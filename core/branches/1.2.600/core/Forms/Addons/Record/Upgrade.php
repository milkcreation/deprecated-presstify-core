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
			return;
		if( $wpdb->get_var("SHOW TABLES LIKE '$new'") !== $new )
			return;	
		if( $wpdb->get_var("SHOW TABLES LIKE '$oldmeta'") !== $oldmeta )
			return;
		if( $wpdb->get_var("SHOW TABLES LIKE '$newmeta'") !== $newmeta )
			return;	
					
		$wpdb->query( "INSERT INTO {$new} SELECT * from {$old}" );
		$wpdb->query( "INSERT INTO {$newmeta} SELECT * from {$oldmeta}" );		
		
		$wpdb->query( "DROP TABLE IF EXISTS {$old}, {$oldmeta};" );

		return __( 'Translation des données vers les nouvelles tables d\'enregistrement des données de formulaire -> OK', 'tify' );
	}
	
	// Translation des données vers les nouvelles tables et suppression des anciennes tables
	protected function update_1705151428()
	{
        global $wpdb;
        
        $wpdb->query( "UPDATE ". $wpdb->prefix ."tify_forms_record SET record_status='publish' WHERE ! record_status;" );
        
        return __( 'Mise à jour des statuts d\'enregistrement des données de formulaires enregistrés.', 'tify' );
	}
}