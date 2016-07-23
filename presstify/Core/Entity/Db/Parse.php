<?php
namespace tiFy\Core\Entity\Db;

final class Parse
{
	/* = ARGUMENTS =*/
	protected	$Db;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( Db $Db )
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
				'per_page' 		=> -1,
				'paged' 		=> 1,
				'order' 		=> 'DESC',
				'orderby' 		=> $this->Db->Primary
			);
		$vars =  wp_parse_args( $vars, $defaults );	
		
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
	
	/** == Traitement de la clause WHERE == **/
	final public function clause_where( $vars )
	{
		if( ! $vars = $this->validate( $vars ) )
			return "WHERE 1";
		
		$name = $this->Db->Name;
		
		$where = array();
		foreach( (array) $vars as $col_name => $value ) :			
			/* Gestion des alias
			 if( ( $value === 'any' ) && isset( $this->col_{$col}['any'] ) )
				$value = $this->col_{$col}['any'];*/
			
			if( is_string( $value ) ) :
				$where[] = "AND {$name}.{$col_name} = '{$value}'";
			elseif( is_bool( $value ) &&  $value ) :
				$where[] = "AND {$name}.{$col_name}";
			elseif( is_bool( $value ) &&  ! $value ) :
				$where[] = "AND ! {$name}.{$col_name}";
			elseif( is_numeric( $value ) ) :
				$where[] = "AND {$name}.{$col_name} = {$value}";
			elseif( is_array( $value ) ) :
				$where[] = "AND {$name}.{$col_name} IN ('". implode( "', '", $value ) ."')";	
			elseif( is_null( $value ) ) :
				$where[] = "AND {$name}.{$col_name} IS NULL";	
			endif;			
		endforeach;
		
		return "WHERE 1 ". implode( ' ', $where );
	}
	
	/** == Traitement de la recherche de term == **/
	final public function clause_search( $terms = '' )
	{		
		if( empty( $terms ) || ! $this->Db->hasSearch() )
			return;
		
		global $wpdb;
		
		$like = '%' . $wpdb->esc_like( $terms ) . '%';
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
		if( $orderby = $this->Db->isCol( $orderby ) )
			return " ORDER BY ". $this->Db->Name .".{$orderby} {$order}";
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
