<?php
namespace tiFy\Plugins\Wistify\Db;

use tiFy\Entity\Db\Table\Wistify_List;

class MailingList extends Wistify_List
{
	/* = CONSTRUCTEUR = */	
	public function __construct( $id ){
		parent::__construct( $id );
	}
	
	/** == Vérifie si un intitulé est déjà utilisé pour une liste de diffusion == **/
	function title_exists( $title, $list_id = null ){
		global $wpdb;
		
		$query = "SELECT COUNT(list_id) FROM ". $this->Name ." WHERE 1 AND list_title = %s";
		if( $list_id )
			$query .= " AND list_id != %d";
		
		return $wpdb->get_var( $wpdb->prepare( $query, $title, $list_id ) );
	}
	
	/* = REQUETES PERSONNALISÉES = */
	/** == Récupére les listes de diffusion d'un abonné == **/
	function get_subscriber_list_ids( $subscriber_id, $active = 1 ){
		global $wpdb;
		
		return $wpdb->get_col( $wpdb->prepare( "SELECT rel_list_id FROM {$this->rel_db->wpdb_table} INNER JOIN ". $this->Name ." ON ( {$this->rel_db->wpdb_table}.rel_list_id = ". $this->Name .".list_id ) WHERE {$this->rel_db->wpdb_table}.rel_subscriber_id = %d AND {$this->rel_db->wpdb_table}.rel_active = %d", $subscriber_id, $active ) );
	}
}	