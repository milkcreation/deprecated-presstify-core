<?php
namespace tiFy\Core\Db;

final class Parse
{
	/* = ARGUMENTS =*/
	
	//
	protected	$Db;
	
	//
	protected	$MetaQuery		= null;
	
	// 
	protected	$MetaClauses 	= array();
	
	/* = CONSTRUCTEUR = */	
	public function __construct( Factory $Db )
	{
		$this->Db = $Db;			
	}
	
	/** == Traitements des arguments de requête == **/
	final public function query_vars( $vars, $defaults = null )
	{
		if( is_null( $defaults ) )
			$defaults = array(
				'item__in'		=> '',
				'item__not_in'	=> '',
				's'				=> '',
				'meta_query'	=> array(),
				'per_page' 		=> -1,
				'paged' 		=> 1,
				'order' 		=> 'DESC',
				'orderby' 		=> $this->Db->Primary
			);
		$vars =  wp_parse_args( $vars, $defaults );	
		
		// Gestion des requêtes de métadonnées
		if( ! empty( $vars['meta_query'] ) && $this->Db->hasMeta() ) :	
			$this->MetaQuery = new Meta_Query( $this->Db, $vars['meta_query'] );			

			$this->MetaClauses = $this->MetaQuery->get_sql(
				$this->Db->MetaType,
				$this->Db->Name,
				$this->Db->Primary,
				null
			);
		endif;

		// Retro-Compatibilité
		if( ! empty( $vars['include'] ) ) :
			$vars['item__in'] = $vars['include'];
			unset( $vars['include'] );
		endif;
		if( ! empty( $vars['exclude'] ) ) :
			$vars['item__not_in'] = $vars['exclude'];
			unset( $vars['exclude'] );
		endif;	
			
		return $vars;	
	}
	
	/** == Traitement de la clause JOIN == **/
	final public function clause_join()
	{
		$join = array();
		
		// Traitement des conditions relatives au metadonnées
		if( ! empty( $this->MetaClauses['join'] ) ) :
			$join[] = trim( $this->MetaClauses['join'] );	
		endif;
		
		if( ! empty( $join ) )
			return " ". implode( ' ', $join );
	}
	
	/** == Traitement de la clause WHERE == **/
	final public function clause_where( $vars )
	{
		$where = array();
		$clause = "WHERE 1";
		
		// Traitement des conditions relatives aux colonnes de la table principale
		if( $cols = $this->validate( $vars ) ) :	
			foreach( (array) $cols as $col_name => $value ) :			
				/* Gestion des alias
				 if( ( $value === 'any' ) && isset( $this->col_{$col}['any'] ) )
					$value = $this->col_{$col}['any'];*/
				
				if( is_string( $value ) ) :
					$where[] = "AND {$this->Db->Name}.{$col_name} = '{$value}'";
				elseif( is_bool( $value ) &&  $value ) :
					$where[] = "AND {$this->Db->Name}.{$col_name}";
				elseif( is_bool( $value ) &&  ! $value ) :
					$where[] = "AND ! {$this->Db->Name}.{$col_name}";
				elseif( is_numeric( $value ) ) :
					$where[] = "AND {$this->Db->Name}.{$col_name} = {$value}";
				elseif( is_array( $value ) ) :
					$where[] = "AND {$this->Db->Name}.{$col_name} IN ('". implode( "', '", $value ) ."')";	
				elseif( is_null( $value ) ) :
					$where[] = "AND {$this->Db->Name}.{$col_name} IS NULL";	
				endif;			
			endforeach;
		endif;
		
		// Traitement des conditions relatives au metadonnées
		if( ! empty( $this->MetaClauses['where'] ) ) :
			$where[] = trim( $this->MetaClauses['where'] );	
		endif;		
		
		return $clause ." ". implode( ' ', $where );
	}
	
	/** == Traitement de la recherche de term == **/
	final public function clause_search( $terms = '' )
	{		
		if( empty( $terms ) || ! $this->Db->hasSearch() )
			return;
		
		$like = '%' . $this->Db->sql()->esc_like( $terms ) . '%';
		$search_query = array();
		foreach( (array) $this->Db->SearchColNames as $col_name )
			$search_query[] = $this->Db->Name .".{$col_name} LIKE '{$like}'";

		if( $search_query )
			return " AND (". join( " OR ", $search_query ) .")";
	}
	
	/** == Traitement de la clause ITEM__IN == **/
	public function clause__in( $ids )
	{
		// Bypass
		if( ! $ids )
			return;
		
		if( ! is_array( $ids ) )
			$ids = array( $ids );
		
		$__in =  implode( ',', array_map( 'absint', $ids ) );
		
		return " AND ". $this->Db->Name .".". $this->Db->Primary ." IN ({$__in})";
	}
	
	/** == Traitement de la clause ITEM__NOT_IN == **/
	public function clause__not_in( $ids )
	{
		// Bypass
		if( ! $ids )
			return;
		
		if( ! is_array( $ids ) )
			$ids = array( $ids );
		
		$__not_in = implode(',',  array_map( 'absint', $ids ) );
		
		return " AND ". $this->Db->Name .".". $this->Db->Primary ." NOT IN ({$__not_in})";
	}
	
	/** == Traitement de la clause ORDER == **/
	public function clause_order( $orderby, $order = 'DESC' )
	{
		if( ( $orderby === 'meta_value' ) &&  $this->MetaQuery ) :		
			$clauses = $this->MetaQuery->get_clauses();
			$primary_meta_query = reset( $clauses );
			$clause = "CAST({$primary_meta_query['alias']}.meta_value AS {$primary_meta_query['cast']}) {$order}";		
		elseif( $orderby = $this->Db->isCol( $orderby ) ) :
			$clause = $this->Db->Name .".{$orderby} {$order}";
		else :
			$clause = $this->Db->Name . $this->Db->Primary ." {$order}";
		endif;
		
		return " ORDER BY ". $clause;
	}
	
	/** == Traitement de la clause GROUPBY == **/
	public function clause_group_by()
	{
		if( $this->MetaClauses )
			return "GROUP BY {$this->Db->Name}.{$this->Db->Primary}";
	}
	
	
	/** == Vérification des arguments de requête == **/
	final public function validate( $vars ){
		$_vars = array();
		foreach( $vars as $col_name => $value ) :
			if( ! $col_name = $this->Db->isCol( $col_name )  )
				continue;
			/** @todo : Typage des valeurs  ! any cf parse_conditions **/
			$_vars[$col_name] = $value;			
		endforeach;
				
		return $_vars;		
	}
}
