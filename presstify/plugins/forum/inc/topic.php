<?php
Class tiFy_Forum_TopicMain{
	/* = ARGUMENTS = */
	public	// Contrôleurs
			$master;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Forum_Master $master ){
		$this->master = $master;
	}
	
	/* = CONTROLEUR = */
	/** == Récupération d'un sujet de forum == **/
	public function get( $topic_id ){
		return $this->master->db->topic->get_item_by_id( $topic_id );
	}
	
	/** == Mise à jour du calcul des contributions, avec gestion de sursit == **/
	public function update_contrib_count( $post_id, $do_deferred = false ) {
		static $_deferred = array();
	
		if ( $do_deferred ) {
			$_deferred = array_unique($_deferred);
			foreach ( $_deferred as $i => $_post_id ) {
				wp_update_comment_count_now($_post_id);
				unset( $_deferred[$i] ); /** @todo Move this outside of the foreach and reset $_deferred to an array instead */
			}
		}
	
		if ( wp_defer_comment_counting() ) {
			$_deferred[] = $post_id;
			return true;
		}
		elseif ( $post_id ) {
			return wp_update_comment_count_now($post_id);
		}	
	}
	
	/** == Mise à jour du calcul des contributions == **/
	public function update_contrib_count_now( $topic_id ) {
		global $wpdb;
		
		$topic_id = (int)  $topic_id;
		if ( !  $topic_id )
			return false;
		
		if ( !  $topic = $this->get( $topic_id ) )
			return false;
	
		$old = (int) $topic->topic_contrib_count;
		$new = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->tify_forum_contribution WHERE contrib_topic_id = %d AND contrib_approved = '1'", $topic_id ) );
		$this->master->db->topic->update_item( $topic_id, array( 'topic_contrib_count' => $new ) );

		do_action( 'tify_forum_update_contrib_count', $topic_id, $new, $old );
	
		return true;
	}
}