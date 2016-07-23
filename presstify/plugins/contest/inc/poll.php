<?php
abstract class tiFy_Contest_Poll {
	/* = CONTROLEURS = */
	/** == Compte du nombre de vote pour un jeu concours == **/
	final public static function contest_count( $contest_id = null ){
		if( ! $contest_id )
			return 0;
		
		global $wpdb;
		
		$query = 	"SELECT COUNT( contest_poll.poll_id )".
				 	" FROM {$wpdb->tify_contest_poll} as contest_poll".
				 	" INNER JOIN {$wpdb->tify_contest_part} as contest_part ON ( contest_part.part_id = contest_poll.poll_part_id )".
				 	" WHERE 1".
					" 	AND contest_part.part_contest_id = %s";
		
		return (int) $wpdb->get_var( $wpdb->prepare( $query, $contest_id ) );	
	}
	
	/** == Compte du nombre de vote pour une participation == **/
	final public static function part_count( $part_id = 0, $active = 1 ){
		if( ! $part_id )
			return 0;
		global $tify_contest;
		
		$db = new tiFy_Contest_PollDb( $tify_contest );
		return $db->count_items( array( 'part_id' => $part_id, 'active' => $active ) );
	}
	
	/** == Compte le nombre de vote d'un utilisateur  == **/
	final public static function user_count_by( $field = 'id', $value, $part_id = 0 ){
		$allowed_fields = array( 'id', 'remote_addr', 'email' );
		if( ! in_array( $field, $allowed_fields ) )
			return 0;
		
		global $tify_contest;
		
		$query_args[ 'user_'. $field ] = ( $field === 'user_id' ) ? (int) $value : (string) $value;
		if( $part_id )
			$query_args[ 'part_id' ] = (int) $part_id;

		$db = new tiFy_Contest_PollDb( $tify_contest );
		return $db->count_items( $query_args );
	}
}