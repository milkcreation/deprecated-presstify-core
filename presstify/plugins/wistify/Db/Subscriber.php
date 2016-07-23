<?php
namespace tiFy\Plugins\Wistify\Db;

use tiFy\Entity\Db\Table\Wistify_Subscriber;

class Subscriber extends Wistify_Subscriber
{
	/* = CONSTRUCTEUR = */	
	public function __construct( $id ){
		parent::__construct( $id );
	}
	
	/* = REQUÊTES = */	
	/** == Compte le nombre d'éléments == **/
	function count_items( $args = array() ){
		global $wpdb;
		
		// Traitement des arguments
		$defaults = array(
			'exclude'	=> '',
			'search'	=> '',
			'limit' 	=> -1,
			// Relation
			'list_id'	=> -1,
			'active'	=> null
		);
		$args = $this->_parse_query_vars( $args, $defaults );
		extract( $args, EXTR_SKIP );	
		// Requête
		$query  = "SELECT COUNT( DISTINCT ". $this->Name .".{$this->primary_key} ) FROM ". $this->Name;
		// Jointure
		$query .= " INNER JOIN {$this->rel_db->wpdb_table} ON ". $this->Name .".subscriber_id = {$this->rel_db->wpdb_table}.rel_subscriber_id";
		/// Conditions
		$query .= " WHERE 1";		
		//// Relation
		if( $list_id > -1 ) 
			$query .= " AND {$this->rel_db->wpdb_table}.rel_list_id = {$list_id}";				
		if( ! is_null( $active ) )
				$query .= " AND {$this->rel_db->wpdb_table}.rel_active = {$active}";
		
		//// Conditions prédéfinies
		$query .= " ". $this->_parse_conditions( $args, $defaults );
		/// Recherche
		if( $this->search_cols && ! empty( $search ) ) :
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$query .= " AND (";
			foreach( $this->search_cols as $search_col ) :
				$search_query[] = $this->Name. ".{$search_col} LIKE '{$like}'";
			endforeach;
			$query .= join( " OR ", $search_query );
			$query .= ")";	
		endif;
		/// Exclusions
		if( $exclude )
			$query .= $this->_parse_exclude( $exclude );
	
		//// Limite
		if( $limit > -1 )
			$query .= " LIMIT $limit";
		
		// Résultat		
		return (int) $wpdb->get_var( $query );
	}

	/** == Récupération de la valeur de plusieurs éléments == **/
	function get_items_col( $col = null, $args = array() ){
		global $wpdb;
		
		// Traitement des arguments
		$defaults = array(
			'item__in'	=> '',
			'exclude'	=> '',
			'search'	=> '',				
			'per_page' 	=> -1,
			'paged' 	=> 1,
			'order' 	=> 'DESC',
			'orderby' 	=> $this->primary_col,
			// Relation
			'list_id'	=> -1,
			'active'	=> null
		);
		$args = $this->_parse_query_vars( $args, $defaults );
		extract( $args, EXTR_SKIP );
		
		$col = ! $col ?  $this->primary_col : $col;
		if( $this->col_prefix_auto )
			$col = $this->col_prefix . $col;
		// Requête
		$query  = "SELECT DISTINCT ". $this->Name .".{$col} FROM ". $this->Name;	
		// Jointure
		$query .= " INNER JOIN {$this->rel_db->wpdb_table} ON ". $this->Name .".subscriber_id = {$this->rel_db->wpdb_table}.rel_subscriber_id";
				
		/// Conditions
		$query .= " WHERE 1";
		//// Relation
		if( $list_id > -1 ) 
			$query .= " AND {$this->rel_db->wpdb_table}.rel_list_id = {$list_id}";		
		if( ! is_null( $active ) )
			$query .= " AND {$this->rel_db->wpdb_table}.rel_active = {$active}";
		
		//// Conditions prédéfinies
		$query .= " ". $this->_parse_conditions( $args, $defaults );
		/// Recherche
		if( $this->search_cols && ! empty( $search ) ) :
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$query .= " AND (";
			foreach( $this->search_cols as $search_col ) :
				$search_query[] = $this->Name .".{$search_col} LIKE '{$like}'";
			endforeach;
			$query .= join( " OR ", $search_query );
			$query .= ")";	
		endif;
		/// Exclusions
		if( $exclude )
			$query .= $this->_parse_exclude( $exclude );
		/// Inclusions
		if( $item__in = $this->_parse_item__in( $item__in ) )
			$query .= $this->_parse_query_item__in( $item__in );
		/// Ordre
		if( $item__in && ( $orderby === 'item__in' ) )
			$query .= " ORDER BY FIELD( ". $this->Name .".{$this->primary_key}, $item__in )";
		else
			$query .= $this->_parse_order( $orderby, $order );
		/// Limite
		if( $per_page > 0 ) :
			$offset = ($paged-1)*$per_page;
			$query .= " LIMIT {$offset}, {$per_page}";
		endif;

		// Resultats				
		if( $res = $wpdb->get_col( $query ) )
			return array_map( 'maybe_unserialize', $res );
	}
		
	/* = REQUETES PERSONNALISÉES = */	
	/** == Vérifie l'existance d'un email pour un abonné == **/
	function email_exists( $email, $exclude_id = null ){
		global $wpdb;
		
		$query = "SELECT COUNT(subscriber_id) FROM ". $this->Name ." WHERE 1 AND subscriber_email = %s";
		if( $exclude_id )
			$query .= " AND subscriber_id != %d";
		
		return $wpdb->get_var( $wpdb->prepare( $query, $email, $exclude_id ) );
	}
}