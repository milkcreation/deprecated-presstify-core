<?php
namespace tiFy\Plugins\Wistify\Db;

use tiFy\Entity\Db\Table\Wistify_Campaign;

class Campaign extends Wistify_Campaign
{
	/* = CONSTRUCTEUR = */	
	public function __construct( $id ){
		parent::__construct( $id );
	}	
	
	/* == METHODES GLOBAL == **/
	/** == Mise à jour du status == 
	 * @param int 		$campaign_id	ID de la campagne
	 * @param string 	$status		
	**/
	public function update_status( $campaign_id, $status = '' ){
		if( ! in_array( $status, array( 'edit', 'preparing', 'ready', 'send', 'forwarded', 'trash' ) ) )
			return;
		
		return $this->update_item( $campaign_id, array( 'status' => $status ) );
	}
		
	/* = JOURNALISATION DE LA PREPARATION = */
	/** == == **/
	public function defaults_prepare_log(){
		return array( 
			'total' 		=> 0, 
			'enqueue' 		=> 0,
			'invalid'		=> array(), 
			'duplicate' 	=> array(), 
			'hard-bounce' 	=> array(), 
			'soft-bounce' 	=> array(), 
			'rejected' 		=> array() 
		);
	}
	
	/** == Création des logs de préparation == **/
	public function set_prepare_log( $campaign_id ){
		$this->add_item_meta( $campaign_id, 'prepare_log', maybe_serialize( $this->defaults_prepare_log() ), true );
	}

	/** == Récupération des logs de préparation == **/
	public function get_prepare_log( $campaign_id ){
		return $this->get_item_meta( $campaign_id, 'prepare_log', true );
	}
	
	/** == Mise à jours des logs de préparation == **/
	public function update_prepare_log( $campaign_id, $datas = array(), $combine = false ){
		$current		= ( $log = $this->get_prepare_log( $campaign_id ) ) ? $log :  $this->defaults_prepare_log();
		$keys 			= array_keys( $this->defaults_prepare_log() );
		$updated_datas	= array();
		
		foreach( $datas as $key => $value ) :
			if( ! in_array( $key, $keys ) )
				continue;
			if( $combine && is_array( $value ) && isset( $current[$key] ) && is_array( $current[$key] ) ) :
				$updated_datas[$key] = array_merge_recursive( $current[$key], $value );
			else :
				$updated_datas[$key] = $value;
			endif;
		endforeach;
		
		$meta_value = wp_parse_args( $updated_datas, $current );
		
		return $this->update_item_meta( $campaign_id, 'prepare_log', $meta_value );
	}
	
	/** == Récupération des logs de préparation == **/
	public function delete_prepare_log( $campaign_id ){
		return $this->delete_item_meta( $campaign_id, 'prepare_log' );
	}
}