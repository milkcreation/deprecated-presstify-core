<?php
namespace tiFy\Plugins\Wistify\Core;

use tiFy\Plugins\Wistify\Wistify;

class Upgrade{
	/* = ARGUMENT = */
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( Wistify $master ){
		// Référence
		$this->master = $master;
		
		$this->master->installed = (int) get_option( 'wistify_installed_version', 0 );

		if( ! version_compare( $this->master->installed, $this->master->version, '>=' ) )
			add_action( 'muplugins_loaded', array( $this, 'wp_muplugins_loaded' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation de Wordpress == **/
	public function wp_muplugins_loaded(){
		wp_cache_flush();
		
		if( version_compare( $this->master->installed, 1507081800, '<' ) )
			$this->upgrade_1507081800( );	
		if( version_compare( $this->master->installed, 1507152015, '<' ) )
			$this->upgrade_1507152015( );
		if( version_compare( $this->master->installed, 1509141718, '<' ) )
			$this->upgrade_1509141718( );
		if( version_compare( $this->master->installed, 1509271926, '<' ) )
			$this->upgrade_1509271926( );
			
		$this->upgrade_version();		
	}
	
	/* = MISE A JOUR DE LA VERSION INSTALLEE = */
	private function upgrade_version( $version = null ){
		if( ! $version )
			$version = $this->master->version;
		update_option( 'wistify_installed_version', $version );
	} 
	
	/* = MISES A JOUR = */	
	/** ==  == **/
	private function upgrade_1507081800(){
		global $wpdb;
		require_once( ABSPATH .'wp-admin/install-helper.php' );
		
		// Modification de la table des campagnes (ajout des uids)
		maybe_add_column( 
			"$wpdb->wistify_campaign", 
			'campaign_uid', 
			"ALTER TABLE $wpdb->wistify_campaign ADD campaign_uid VARCHAR(32) NULL AFTER campaign_id;" 
		);
		if( $campaign_ids = $wpdb->get_col( "SELECT campaign_id FROM $wpdb->wistify_campaign WHERE 1 AND {$wpdb->wistify_campaign}.campaign_uid IS NULL" ) )
			foreach( $campaign_ids as $campaign_id )
				$wpdb->update( $wpdb->wistify_campaign, array( 'campaign_uid' => tify_generate_token( 32 ) ), array( 'campaign_id' => $campaign_id ) );
		
		// Modification de la table des abonnés (ajout des uids)				
		maybe_add_column( 
			"$wpdb->wistify_subscriber", 
			'subscriber_uid', 
			"ALTER TABLE $wpdb->wistify_subscriber ADD subscriber_uid VARCHAR(32) NULL AFTER subscriber_id;" 
		);
		if( $subscriber_ids = $wpdb->get_col( "SELECT subscriber_id FROM $wpdb->wistify_subscriber WHERE 1 AND {$wpdb->wistify_subscriber}.subscriber_uid IS NULL" ) )
			foreach( $subscriber_ids as $subscriber_id )
				$wpdb->update( $wpdb->wistify_subscriber, array( 'subscriber_uid' => tify_generate_token( 32 ) ), array( 'subscriber_id' => $subscriber_id ) );	
	} 
	
	/** == == **/
	private function upgrade_1507152015(){
		global $wpdb;
		require_once( ABSPATH .'wp-admin/install-helper.php' );
		
		// Modification de la table des campagnes
		$wpdb->query( "ALTER TABLE {$wpdb->wistify_campaign} CHANGE `campaign_id` `campaign_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;" );
		$wpdb->query( "ALTER TABLE {$wpdb->wistify_campaign} CHANGE `campaign_author` `campaign_author` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0';" );
		$wpdb->query( "ALTER TABLE {$wpdb->wistify_campaign} CHANGE `campaign_date` `campaign_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';" );
		$wpdb->query( "ALTER TABLE {$wpdb->wistify_campaign} CHANGE `campaign_status` `campaign_status` VARCHAR(25) NOT NULL DEFAULT 'draft';" );
		$wpdb->query( "ALTER TABLE {$wpdb->wistify_campaign} CHANGE `campaign_step` `campaign_step` INT(2) NOT NULL DEFAULT '0';" );
		maybe_drop_column(
			"$wpdb->wistify_campaign",
			"campaign_send_status",
			"ALTER TABLE $wpdb->wistify_campaign DROP `campaign_send_status`;"
		);
		
		// Modification de la table des listes de diffusion
		$wpdb->query( "ALTER TABLE {$wpdb->wistify_list} CHANGE `list_id` `list_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;" );
		$wpdb->query( "ALTER TABLE {$wpdb->wistify_list} CHANGE `list_description` `list_description` LONGTEXT  NULL;" );
		maybe_add_column( 
			"$wpdb->wistify_list",
			"list_menu_order",
			"ALTER TABLE {$wpdb->wistify_list} ADD `list_menu_order` BIGINT(20) NOT NULL DEFAULT '0' AFTER `list_status`;" 
		);
		maybe_add_column( 
			"$wpdb->wistify_list", 
			"list_public", 
			"ALTER TABLE{$wpdb->wistify_list} ADD `list_public` TINYINT(1) NOT NULL DEFAULT '1' AFTER `list_menu_order`;"
		); 
		
		// Modification de la table des relations listes de diffusion <> abonnés
		maybe_add_column(
			"$wpdb->wistify_list_relationships",
			"rel_subscriber_id",
			"ALTER TABLE {$wpdb->wistify_list_relationships} CHANGE `subscriber_id` `rel_subscriber_id` BIGINT(20) UNSIGNED NOT NULL" 
		);
		maybe_add_column(
			"$wpdb->wistify_list_relationships",
			"rel_list_id",
			"ALTER TABLE {$wpdb->wistify_list_relationships} CHANGE `list_id` `rel_list_id` BIGINT(20) UNSIGNED NOT NULL" 
		);
		$wpdb->query( "ALTER TABLE {$wpdb->wistify_list_relationships} MODIFY COLUMN `rel_list_id` BIGINT(20) UNSIGNED NOT NULL AFTER `rel_subscriber_id`" );
		maybe_add_column(
			"$wpdb->wistify_list_relationships",
			"rel_id",
			"ALTER TABLE {$wpdb->wistify_list_relationships} DROP PRIMARY KEY, ADD `rel_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`rel_id`);" 
		);
			
		// Modification de la table des abonnés
		$wpdb->query( "ALTER TABLE {$wpdb->wistify_subscriber} CHANGE `subscriber_id` `subscriber_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;" );
		$wpdb->query( "ALTER TABLE {$wpdb->wistify_subscriber} CHANGE `subscriber_date` `subscriber_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';" );
		$wpdb->query( "ALTER TABLE {$wpdb->wistify_subscriber} CHANGE `subscriber_modified` `subscriber_modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';" );
		$wpdb->query( "ALTER TABLE {$wpdb->wistify_subscriber} CHANGE `subscriber_status` `subscriber_status` VARCHAR(25) NOT NULL DEFAULT 'registred';" );
	}
	
	/** == == **/
	private function upgrade_1509141718(){
		global $wpdb;
		require_once( ABSPATH .'wp-admin/install-helper.php' );
		
		// Translation de la table message_info vers report
		if( $wpdb->query( "SHOW TABLES LIKE '{$wpdb->prefix}wistify_message_info'" ) ) :
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_md__id",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_id` `report_md__id` VARCHAR(32) NOT NULL AFTER `report_md_ts`;" 
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_campaign_id",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_campaign_id` `report_campaign_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0';"
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_refreshed",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_refreshed` `report_refreshed` INT(13) NOT NULL DEFAULT '0';" 
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_md_ts",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_ts` `report_md_ts` INT(13) NOT NULL DEFAULT '0';" 
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_md_sender",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_sender` `report_md_sender` VARCHAR(255) NOT NULL;" 
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_md_template",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_template` `report_md_template` VARCHAR(255) NOT NULL;" 
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_md_subject",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_subject` `report_md_subject` VARCHAR(255) NOT NULL;" 
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_md_email",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_email` `report_md_email` VARCHAR(255) NOT NULL;" 
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_md_tags",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_tags` `report_md_tags` LONGTEXT NOT NULL;" 
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_md_opens",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_opens` `report_md_opens` INT(5) NOT NULL;" 
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_md_opens_detail",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_opens_detail` `report_md_opens_detail` LONGTEXT NOT NULL;" 
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_md_clicks",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_clicks` `report_md_clicks` INT(5) NOT NULL;" 
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_md_clicks_detail",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_clicks_detail` `report_md_clicks_detail` LONGTEXT NOT NULL;" 
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_md_state",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_state` `report_md_state` VARCHAR(25) NOT NULL;" 
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_md_metadata",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_metadata` `report_md_metadata` LONGTEXT NOT NULL;" 
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_md_smtp_events",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_smtp_events` `report_md_smtp_events` LONGTEXT NOT NULL;" 
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_md_resends",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_resends` `report_md_resends` LONGTEXT NOT NULL;" 
			);
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_md_reject_reason",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` CHANGE `info_reject_reason` `report_md_reject_reason` LONGTEXT NOT NULL;" 
			);
			
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}wistify_message_info` DROP PRIMARY KEY");
			maybe_add_column( 
				"{$wpdb->prefix}wistify_message_info", 
				"report_id",
				"ALTER TABLE `{$wpdb->prefix}wistify_message_info` ADD `report_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`report_id`);" 
			);
			
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}wistify_message_info` RENAME `{$wpdb->prefix}wistify_report`" );
		endif;
		
		// Modification de la table report
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}wistify_report` CHANGE `report_md_clicks_detail` `report_md_clicks_detail` LONGTEXT NOT NULL;" );
		maybe_add_column( 
			"{$wpdb->prefix}wistify_report", 
			"report_posted_ts",
			"ALTER TABLE `{$wpdb->prefix}wistify_report` ADD `report_posted_ts` INT(13) NOT NULL DEFAULT '0' AFTER report_campaign_id;" 
		);	
		
		// Modification de la table queue
		maybe_add_column( 
			"{$wpdb->prefix}wistify_queue", 
			"queue_message",
			"ALTER TABLE `{$wpdb->prefix}wistify_queue` ADD `queue_message` LONGTEXT NOT NULL;" 
		);
		
		// Modification de la table de relation liste <> abonné
		maybe_add_column( 
			"{$wpdb->prefix}wistify_list_relationships", 
			"rel_active",
			"ALTER TABLE `{$wpdb->prefix}wistify_list_relationships` ADD `rel_active` TINYINT(1) NOT NULL DEFAULT '0' AFTER `rel_list_id`;" 
		);
		$wpdb->update("{$wpdb->prefix}wistify_list_relationships", array( 'rel_active' => 1 ), array( 'rel_active' => 0 ) );
		
		maybe_add_column( 
			"{$wpdb->prefix}wistify_list_relationships", 
			"rel_id",
			"ALTER TABLE `{$wpdb->prefix}wistify_list_relationships` CHANGE `id` `rel_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;" 
		);
		
		// Modification de la table des listes de diffusion (création des uids)
		maybe_add_column( 
			"{$wpdb->prefix}wistify_list", 
			"list_uid",
			"ALTER TABLE `{$wpdb->prefix}wistify_list` ADD `list_uid` VARCHAR(32) NULL AFTER list_id;" 
		);
		if( $list_ids = $wpdb->get_col( "SELECT list_id FROM {$wpdb->prefix}wistify_list WHERE 1 AND list_uid IS NULL" ) )
			foreach( $list_ids as $list_id )
				$wpdb->update( $wpdb->prefix ."wistify_list", array( 'list_uid' => tify_generate_token( 32 ) ), array( 'list_id' => $list_id ) );
		
		// Translation des statuts de désinscription
		if( $unsubscriber_ids = $wpdb->get_col( "SELECT subscriber_id FROM {$wpdb->prefix}wistify_subscriber WHERE 1 AND subscriber_status = 'unsubscribed'" ) ) :
			foreach( $unsubscriber_ids as $unsubscriber_id ) :
				$wpdb->update( $wpdb->prefix ."wistify_list_relationships", array( 'rel_active' => 0 ), array( 'rel_subscriber_id' => $unsubscriber_id ) );
				$wpdb->update( $wpdb->prefix ."wistify_subscriber", array( 'subscriber_status' => 'registred' ), array( 'subscriber_id' => $unsubscriber_id ) );
			endforeach;
		endif;	
		
		// Dates d'enregistrement des relations listes <> abonnés
		maybe_add_column( 
			"{$wpdb->prefix}wistify_list_relationships", 
			"rel_created",
			"ALTER TABLE `{$wpdb->prefix}wistify_list_relationships` ADD `rel_created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';" 
		);
		maybe_add_column( 
			"{$wpdb->prefix}wistify_list_relationships", 
			"rel_modified",
			"ALTER TABLE `{$wpdb->prefix}wistify_list_relationships` ADD `rel_modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';" 
		);		
		if( $subscribers = $wpdb->get_results( "SELECT subscriber_id AS id, subscriber_date AS created, subscriber_modified AS modified FROM {$wpdb->prefix}wistify_subscriber WHERE 1" ) ) :
			foreach( $subscribers as $s ) :
				$date = ( $s->created !== '0000-00-00 00:00:00' ) ? $s->created : $s->modified;				
				$wpdb->update( $wpdb->prefix ."wistify_list_relationships", array( 'rel_created' => $date ), array( 'rel_subscriber_id' => $s->id ) );
			endforeach;	
		endif;
		
		// Date d'envoi des campagnes
		if( $campaigns = $wpdb->get_results( "SELECT campaign_id AS id, campaign_date AS created, campaign_modified AS modified FROM {$wpdb->prefix}wistify_campaign WHERE 1" ) ) :
			foreach( $campaigns as $c ) :
				$date = ( $c->modified !== '0000-00-00 00:00:00' ) ? $c->modified : $c->created;				
				$wpdb->update( $wpdb->prefix ."wistify_campaign", array( 'campaign_send_datetime' => $date ), array( 'campaign_id' => $c->id ) );
			endforeach;	
		endif;
		
		// Récupération des heures d'expédition de campagne et translation de la valeur dans les rapports d'acheminement
		$wpdb->query( "UPDATE {$wpdb->prefix}wistify_report r LEFT JOIN {$wpdb->prefix}wistify_campaign c ON c.campaign_id = r.report_campaign_id SET r.report_posted_ts = UNIX_TIMESTAMP(c.campaign_send_datetime) WHERE 1" );
	
		// Mise à jour des status de campagne
		$wpdb->query( "UPDATE {$wpdb->prefix}wistify_campaign SET campaign_status = 'edit' WHERE campaign_status = 'composing'" );
		$wpdb->query( "UPDATE {$wpdb->prefix}wistify_campaign SET campaign_status = 'send' WHERE campaign_status = 'in-progress'" );
		$wpdb->query( "UPDATE {$wpdb->prefix}wistify_campaign SET campaign_status = 'forwarded' WHERE campaign_status = 'distributed'" );
	}
		
	/** == == **/
	private function upgrade_1509271926(){
		global $wpdb;
		require_once( ABSPATH .'wp-admin/install-helper.php' );
		
		// Modification de la colonne de temporisation de mises à jours des rapports d'acheminement
		maybe_add_column( 
			"{$wpdb->prefix}wistify_report", 
			"report_updated_ts",
			"ALTER TABLE `{$wpdb->prefix}wistify_report` CHANGE `report_refreshed` `report_updated_ts` INT(13) NOT NULL DEFAULT '0';" 
		);
		
		// Création de la table des archives des rapports d'acheminement
		foreach( $wpdb->get_col( "SHOW TABLES", 0 ) as $table ) :
			if ( preg_match( '/wistify_report_archives/', $table ) ) 
				maybe_add_column( 
					"$table", 
					"report_updated_ts",
					"ALTER TABLE `$table` CHANGE `report_refreshed` `report_updated_ts` INT(13) NOT NULL DEFAULT '0';" 
				);			
		endforeach;	
		
		// Modification du type Message de la table des rapports d'acheminement
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}wistify_queue` CHANGE `queue_message` `queue_message` BLOB NOT NULL;" );
		
		// Ajout de la colonne de verrouillage de la table des rapports d'acheminement
		maybe_add_column( 
			"{$wpdb->prefix}wistify_queue", 
			"queue_locked", 
			"ALTER TABLE `{$wpdb->prefix}wistify_queue` ADD `queue_locked` TINYINT(1) NOT NULL DEFAULT '0';"
		); 
	}

	/** == Création des index == **/
	private function upgrade_AFAIRE(){
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}list_relationships` ADD INDEX `rel_subscriber_id` (`rel_subscriber_id`) USING BTREE" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}list_relationships` ADD INDEX `rel_list_id` (`rel_list_id`) USING BTREE" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}list_relationships` ADD INDEX `subscriber_list_ids` (`rel_subscriber_id`, `rel_list_id`, `rel_id`) USING BTREE" );
	}
}