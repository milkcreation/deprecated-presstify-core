<?php
abstract class tiFy_Contest_Ranking{
	/* = CONTROLEURS = */
	/** == Retourne du nombre de vote pour une participation == **/
	final public static function get_contest_parts_ids( $contest_id = null, $per_page = 20, $offset = 0 ){
		if( ! $contest_id )
			return array();
		
		global $wpdb;
		
		$query = 	"SELECT ranking_part_id".
					" FROM $wpdb->tify_contest_ranking".
					" INNER JOIN {$wpdb->tify_contest_part} ON( part_id = ranking_part_id AND part_contest_id = %s )".
					" WHERE 1".
					" ORDER BY ranking_current_pos ASC".
					" LIMIT %d,%d".
					";";
		return $wpdb->get_col( $wpdb->prepare( $query, $contest_id, $offset, $per_page ) );
	}
	
	/** == Retourne du nombre de vote pour une participation == **/
	final public static function part_count( $part_id = 0 ){
		if( ! $part_id )
			return 0;
		
		global $wpdb;
		
		$query = 	"SELECT @count := IF(ranking_current_ts >= %d, ranking_current_count, ranking_previous_count) as count".
					" FROM $wpdb->tify_contest_ranking".
					" WHERE 1 AND ranking_part_id = %d".
					";";
			
		$count =  $wpdb->get_var( $wpdb->prepare( $query, ( ( $time = get_option( 'tify_contest_ranking_last_handle_ts' ) ) ? $time : time() ), $part_id ) );
		
		return $count;
	}
	
	/** == Retourne le nombre de participation == **/
	final public static function contest_parts_count( $contest_id = null ){
		if( ! $contest_id )
			return 0;
		
		global $wpdb;
		
		$query = 	"SELECT COUNT( ranking_id )".
					" FROM $wpdb->tify_contest_ranking".
					" INNER JOIN {$wpdb->tify_contest_part} ON( part_id = ranking_part_id AND part_contest_id = %s )". // AND IF(ranking_current_ts >= %d, ranking_current_ts, ranking_previous_ts) 
					" WHERE 1".
					";";
			
		return $wpdb->get_var( $wpdb->prepare( $query, $contest_id, ( ( $time = get_option( 'tify_contest_ranking_last_handle_ts' ) ) ? $time : time() ) ) );
	}
	
	/** == Retourne la position d'une participation dans le classement  == **/
	final public static function part_pos( $part_id = 0 ){
		if( ! $part_id )
			return 0;
		
		global $wpdb;
		
		$query = 	"SELECT @count := IF(ranking_current_ts >= %d, ranking_current_pos, ranking_previous_pos) as count".
					" FROM $wpdb->tify_contest_ranking".
					" WHERE 1 AND ranking_part_id = %d".
					";";
			
		return $wpdb->get_var( $wpdb->prepare( $query, ( ( $time = get_option( 'tify_contest_ranking_last_handle_ts' ) ) ? $time : time() ), $part_id ) );
	}
	
	/** == Récupére l'élement voisin == **/
	final public static function get_adjacent_part_id( $part_id = 0, $previous = true ){
		if( ! $part_id )
			return 0;
		
		global $wpdb;				
		
		$op 		= $previous ? '<' : '>';
		$order 		= $previous ? 'DESC' : 'ASC';
		$offset		= $previous ? 1 : 1;
	
	
		$query = 	"SELECT ranking_part_id".
					" FROM {$wpdb->tify_contest_ranking} as r".
					",( SELECT ranking_current_pos FROM {$wpdb->tify_contest_ranking} WHERE ranking_part_id = {$part_id} LIMIT 1 ) pos".
					//",( SELECT part_contest_id as contest_id FROM {$wpdb->tify_contest_part} WHERE part_id = {$part_id} LIMIT 1 ) p".
					" WHERE 1".
						" AND pos.ranking_current_pos {$op}= r.ranking_current_pos".
						//" AND r.ranking_current_pos {$op}= (pos.ranking_current_pos+1)".
						" AND r.ranking_part_id != {$part_id}".
					" ORDER BY r.ranking_part_id $order".
					" LIMIT 1 OFFSET 1".
					";";		
			
		return $wpdb->get_var( $query );
	}
	
	
	/** == Renseigne si la position d'une participation est unique (ou ex aequo) 
	 * @todo
	 * == **/
	final public static function is_part_pos_uniq( $part_id = 0 ){
		if( ! $part_id )
			return 0;
		
		global $wpdb;
		
		$query = 	"SELECT @count := IF(ranking_current_ts >= %d, ranking_current_pos, ranking_previous_pos) as count".
					" FROM $wpdb->tify_contest_ranking".
					" WHERE 1 AND ranking_part_id = %d".
					";";
			
		return $wpdb->get_var( $wpdb->prepare( $query, ( ( $time = get_option( 'tify_contest_ranking_last_handle_ts' ) ) ? $time : time() ), $part_id ) );
	}
}