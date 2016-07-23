<?php
namespace tiFy\Plugins\Wistify\Db;

use tiFy\Entity\Db\Table\Wistify_List;

class MailingList extends Wistify_List
{
	/* = CONSTRUCTEUR = */	
	public function __construct( $id ){
		parent::__construct( $id );
	}
		
	/* = REQUETES PERSONNALISÉES = */
	/** == Récupére les listes de diffusion d'un abonné == **/
	function get_subscriber_list_ids( $subscriber_id, $active = 1 ){
		global $wpdb;
		
		return $wpdb->get_col( 
			$wpdb->prepare( 
				"SELECT rel_list_id".
				" FROM {$wpdb->wistify_list_relationships}".
				" INNER JOIN {$this->Name} ON ( {$wpdb->wistify_list_relationships}.rel_list_id = {$this->Name}.list_id )".
				" WHERE {$wpdb->wistify_list_relationships}.rel_subscriber_id = %d".
					" AND {$wpdb->wistify_list_relationships}.rel_active = %d",
				$subscriber_id, $active 
			)
		);
	}
}	