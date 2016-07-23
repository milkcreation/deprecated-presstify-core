<?php
namespace tiFy\Plugins\Wistify\Db;

use tiFy\Entity\Db\Table\Wistify_Queue;

class MailQueue extends Wistify_Queue
{
	/* = CONSTRUCTEUR = */	
	public function __construct( $id ){
		parent::__construct( $id );
	}
	
	/* = REQUETES PERSONNALISÉES = */
	/** == Suppression de la file d'une campagne == **/
	function reset_campaign( $campaign_id ){
		global $wpdb;
		
		return $wpdb->delete( $this->Name, array( 'queue_campaign_id' => $campaign_id ), '%d' );		
	}
}