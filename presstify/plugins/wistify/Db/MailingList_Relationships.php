<?php
namespace tiFy\Plugins\Wistify\Db;

use tiFy\Entity\Db\Table\Wistify_List_Relationships;

class MailingList_Relationships extends Wistify_List_Relationships
{
	/* = CONSTRUCTEUR = */	
	public function __construct( $id ){
		parent::__construct( $id );
	}
	
	/**
	 * Suppression de toutes les relation liste de diffusion/abonnés
	 */
	 function delete_list_subscribers( $list_id ){
		global $wpdb;
	
		return $wpdb->delete( $this->Name, array( 'rel_list_id' => $list_id ) );
	}
	 
	/** == Ajout d'une relation abonné/liste de diffusion == **/
	function insert_subscriber_for_list( $subscriber_id, $list_id, $active = 0 ){
		global $wpdb;
		
		if( ! $rel = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ". $this->Name ." WHERE ". $this->Name .".rel_subscriber_id = %d AND ". $this->Name .".rel_list_id = %d", $subscriber_id, $list_id ) ) )
			return $wpdb->insert( $this->Name, array( 'rel_list_id' => $list_id, 'rel_subscriber_id' => $subscriber_id, 'rel_created' => current_time( 'mysql' ), 'rel_active' => $active ) );
		elseif( $rel->rel_active != $active )
			return $this->update_item( $rel->rel_id, array( 'rel_active' => $active, 'rel_modified' => current_time( 'mysql' ) ) );
	}
		
	/** == Suppression d'une relation abonné/liste de diffusion == **/
	function delete_subscriber_for_list( $subscriber_id, $list_id ){
		global $wpdb;
			
		return $wpdb->delete( $this->Name, array( 'rel_list_id' => $list_id, 'rel_subscriber_id' => $subscriber_id ) );
	}
	
	/** == Suppression de toutes les relation abonné/listes de diffusion == **/
	function delete_subscriber_lists( $subscriber_id ){
		global $wpdb;
	
		return $wpdb->delete( $this->Name, array( 'rel_subscriber_id' => $subscriber_id ) );
	}
	 
	/** == Vérifie si un abonné est affilié à la liste des orphelins == **/
	function is_orphan( $subscriber_id, $active = null ){
		global $wpdb;
		
		$query = "SELECT * FROM ". $this->Name ." WHERE rel_subscriber_id = %d AND rel_list_id = 0";
		if( ! is_null( $active ) )
			 $query .= " AND rel_active = %d";
		return $wpdb->query( $wpdb->prepare( $query, $subscriber_id, $active ) );
	} 
}