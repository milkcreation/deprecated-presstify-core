<?php
class tiFy_EventsQuery{
	/* = ARGUMENTS = */
	private $master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Events $master ){
		$this->master = $master;
		
		add_filter( 'query_vars', array( $this, 'wp_add_query_vars' ), 1 );
		add_filter( 'posts_clauses', array( $this, 'wp_posts_clauses' ), 99, 2 );
	}
	
	/** == Définition des variables de requête == **/
	public function wp_add_query_vars($aVars) {
		$aVars[] = 'tyevquery'; // all | uniq | none
		$aVars[] = 'tyevfrom'; 
		$aVars[] = 'tyevto';
	  
		return $aVars;
	}
		
	/** == Modification des condition de requête == **/	
	public function wp_posts_clauses( $pieces, $query ){	
		//Bypass	
		if( is_admin() && ! defined( 'DOING_AJAX' ) )
			return $pieces;		

		if( ! $post_types = $query->get( 'post_type' ) )
			return $pieces;
		
		// Traitement des types de post		
		if( ! is_array( $post_types ) )
			$post_types = array( $post_types );
		/// La requête ne doit contenir des types de post déclarés		
		if( in_array( 'any', $post_types ) )
			return $pieces;		
		if( array_diff( $post_types, $this->master->get_post_types() ) )
			return $pieces;
		if( $query->get( 'tyevquery' ) === 'none' )
			return $pieces;

		global $wpdb;
		extract( $pieces );	

		// Récupération des arguments de contruction de la requête
		$show 	= ( ( $_show = $query->get( 'tyevquery' ) ) && in_array( $_show, array( 'all', 'uniq' ) ) ) ? $_show : 'uniq';
		$from 	= ( $_from = $query->get( 'tyevfrom' ) ) ? $_from : current_time( 'mysql' );
		$to 	= ( $_to = $query->get( 'tyevto' ) ) ? $_to : false;

		if( $query->is_singular() )
			$fields .= ", tify_events.event_id, MIN(tify_events.event_start_datetime) as event_start_datetime, MAX(tify_events.event_end_datetime) as event_end_datetime, GROUP_CONCAT(tify_events.event_start_datetime, '|', tify_events.event_end_datetime ORDER BY tify_events.event_start_datetime ASC) as event_date_range";
		else
			$fields .= ", tify_events.event_id, tify_events.event_start_datetime, tify_events.event_end_datetime";
		
		$join .= " INNER JOIN {$this->master->db->wpdb_table} as tify_events ON ( $wpdb->posts.ID = tify_events.event_post_id )";  	
		
		if( ! $query->is_singular() ) :
			if( $show === 'uniq' ) :
				$inner_where  = "SELECT MIN( event_start_datetime ) FROM {$this->master->db->wpdb_table} WHERE 1";
				$inner_where .= " AND event_post_id = {$wpdb->posts}.ID";
				$inner_where .= " AND event_end_datetime >= '{$from}'";
				if( $to )
					$inner_where .= " AND event_start_datetime <= '{$to}'";			
				$where .= " AND tify_events.event_start_datetime IN ( {$inner_where} )";			
				// Éviter les doublons lorsqu'un événement à deux dates de démarrage identiques
				$groupby = "tify_events.event_post_id";
			else :		
				$where .= " AND tify_events.event_end_datetime >= '{$from}'";
				if( $to )
					$where .= " AND event_start_datetime <= '{$to}'";			
				// Autoriser les doublons
				$groupby = false;
			endif;
			$orderby = "tify_events.event_start_datetime ASC";
		endif;
		
		$_pieces = array( 'where', 'groupby', 'join', 'orderby', 'distinct', 'fields', 'limits' );

		return compact ( $_pieces );
	}

	/* = CONTROLEUR = */
	/** == Calcul le nombre d'événements d'une plage == **/
	public function range_count( $from, $to ){
		global $wpdb;
		
		$from 		= new DateTime( $from, new DateTimeZone( $this->master->timezone_string ) );
		$to			= new DateTime( $to, new DateTimeZone( $this->master->timezone_string ) );
	
		return $wpdb->get_var( 
			$wpdb->prepare(
				"SELECT COUNT(event_id)".
				" FROM {$wpdb->tify_events}".
				" WHERE (".
				" ( UNIX_TIMESTAMP(event_start_datetime) BETWEEN %d AND %d )".
				" OR ( UNIX_TIMESTAMP(event_end_datetime) BETWEEN %d AND %d )".
				")",
				$from->getTimestamp(),
				$to->getTimestamp(),
				$from->getTimestamp(),
				$to->getTimestamp()
			)
		);
	}
}