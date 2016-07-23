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
	
	/** == Vérifie l'existance d'une campagne dans la file == **/
	function has_campaign( $campaign_id ){
		global $wpdb;
		
		return $wpdb->query( $wpdb->prepare( "SELECT queue_campaign_id FROM ". $this->Name ." WHERE queue_campaign_id = %d", $campaign_id ) );		
	}
}