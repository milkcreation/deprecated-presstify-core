<?php
namespace tiFy\Core\Db;

class Select
{
	/* = ARGUMENTS =*/
	protected	$Db;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( Factory $Db )
	{
		$this->Db = $Db;
	}	
		
	/* = COMPTE = */
	/** == Compte le nombre d'éléments selon une liste de critère == **/
	public function count( $args = array() )
	{
		$name 			= $this->Db->Name;
		$primary_key 	= $this->Db->Primary;		
				
		// Traitement des arguments de requête
		$defaults = array(
			'item__not_in'	=> '',
			's'				=> '',
			'limit' 		=> -1
		);
		// Traitement des arguments
		$parse = $this->Db->parse();
		$args = $parse->query_vars( $args, $defaults );
		
		// Traitement de la requête					
		/// Selection de la table de base de données
		$query  = "SELECT COUNT( {$name}.{$primary_key} ) FROM {$name}";	
		
		// Conditions de jointure
		$query .= $parse->clause_join( $args );
		
		/// Conditions définies par les arguments de requête
		if( $clause_where = $parse->clause_where( $args ) )
			$query .= " ". $clause_where;		
		
		/// Recherche de terme
		if( $clause_search = $parse->clause_search( $args['s'] ) )
			$query .= " ". $clause_search;
		
		/// Exclusions
		if( $clause__not_in = $parse->clause__not_in( $args['item__not_in'] ) )
			$query .= " ". $clause__not_in;
		
		/// Groupe
		/*if( $clause_group_by = $parse->clause_group_by() )
			$query .= " ". $clause_group_by;*/
						
		//// Limite
		if( $args['limit'] > -1 )
			$query .= " LIMIT {$args['limit']}";
			
		// Résultat		
		return (int) $this->Db->sql()->get_var( $query );
	}

	/* = VERIFICATION D'EXISTANCE = */
	/** == Vérification de l'existance de la valeur d'un cellule selon des critères == **/
	public function has( $col_name = null, $value = '', $args = array() )
	{
		$name 			= $this->Db->Name;
		$primary_key 	= $this->Db->Primary;
		
		// Traitement de l'intitulé de la colonne
		if( is_null( $col_name ) )
			$col_name = $primary_key;
		elseif( ! $col_name = $this->Db->isCol( $col_name ) )
			return null;

		$args[$col_name] = $value;
		
		return $this->count( $args );
	}	
	
	/* = CELLULE = */
	/** == Récupération de l'id d'un élément selon des critères == **/
	public function id( $args = array() )
	{
		return $this->cell( null, $args );	
	}
		
	/** == Récupération de la valeur d'un cellule selon des critères == **/
	public function cell( $col_name = null, $args = array() )
	{
		$name 			= $this->Db->Name;
		$primary_key 	= $this->Db->Primary;
		
		// Traitement de l'intitulé de la colonne
		if( is_null( $col_name ) )
			$col_name = $primary_key;
		elseif( ! $col_name = $this->Db->isCol( $col_name ) )
			return null;
		
		// Traitement des arguments
		$defaults = array(
			'item__in'		=> '',
			'item__not_in'	=> '',
			's'				=> '',
			'order' 		=> 'DESC',
			'orderby' 		=> $primary_key
		);
		// Traitement des arguments
		$parse = $this->Db->parse();
		$args = $parse->query_vars( $args, $defaults );
			
		// Traitement de la requête	
		/// Selection de la table de base de données
		$query  = "SELECT {$name}.{$col_name} FROM {$name}";			
	
		/// Conditions de jointure
		$query .= $parse->clause_join( $args );
		
		/// Conditions des arguments de requête
		if( $clause_where = $parse->clause_where( $args ) )
			$query .= " ". $clause_where;
		
		/// Recherche de terme
		if( $clause_search = $parse->clause_search( $args['s'] ) )
			$query .= " ". $clause_search;
		
		/// Inclusions
		if( $clause__in = $parse->clause__in( $args['item__in'] ) )
			$query .= " ". $clause__in;
		
		/// Exclusions
		if( $clause__not_in = $parse->clause__not_in( $args['item__not_in'] ) )
			$query .= " ". $clause__not_in;
		
		/// Groupe
		if( $clause_group_by = $parse->clause_group_by() )
			$query .= " ". $clause_group_by;	
			
		/*
		if( $item__in && ( $orderby === 'item__in' ) )
			$query .= " ORDER BY FIELD( {$this->wpdb_table}.{$this->primary_key}, $item__in )";
		else */
		if( $clause_order = $parse->clause_order( $args['orderby'], $args['order'] ) )
			$query .= $clause_order;
			
		if( $var = $this->Db->sql()->get_var( $query ) )
			return maybe_unserialize( $var );
	}

	/** == Récupération de la valeur d'un cellule selon son l'id de l'élément == **/
	public function cell_by_id( $id, $col_name )
	{
		if( ! $col_name = $this->Db->isCol( $col_name ) )
			return null;
			
		if( ( $item = wp_cache_get( $id, $this->Db->Name ) ) && isset( $item->{$col_name} ) )
			return $item->{$col_name};
		else
			return $this->cell( $col_name, array( $this->Db->Primary => $id ) );
	}
	
	/* = COLONNE = */
	/** == Récupération des valeurs d'une colonne de plusieurs éléments selon des critères == **/
	public function col( $col_name = null, $args = array() )
	{
		$name 			= $this->Db->Name;
		$primary_key 	= $this->Db->Primary;
		
		// Traitement de l'intitulé de la colonne
		if( is_null( $col_name ) )
			$col_name = $primary_key;
		elseif( ! $col_name = $this->Db->isCol( $col_name ) )
			return null;
			
		// Traitement des arguments
		$parse = $this->Db->parse();
		$args = $parse->query_vars( $args );
		
		// Traitement de la requête		
		/// Selection de la table de base de données
		$query  = "SELECT {$name}.{$col_name} FROM {$name}";
		
		// Condition de jointure
		$query .= $parse->clause_join( $args );

		/// Conditions des arguments de requête
		if( $clause_where = $parse->clause_where( $args ) )
			$query .= " ". $clause_where;
		
		/// Recherche de termes
		if( $clause_search = $parse->clause_search( $args['s'] ) )
			$query .= " ". $clause_search;
		
		/// Inclusions
		if( $clause__in = $parse->clause__in( $args['item__in'] ) )
			$query .= " ". $clause__in;
		
		/// Exclusions
		if( $clause__not_in = $parse->clause__not_in( $args['item__not_in'] ) )
			$query .= " ". $clause__not_in;
		
		/// Groupe
		if( $clause_group_by = $parse->clause_group_by() )
			$query .= " ". $clause_group_by;
		
		/* 	
		/// Ordre
		if( $item__in && ( $orderby === 'item__in' ) )
			$query .= " ORDER BY FIELD( {$this->wpdb_table}.{$this->primary_key}, $item__in )";
		else */
		if( $clause_order = $parse->clause_order( $args['orderby'], $args['order'] ) )
			$query .= $clause_order;
		
		/// Limite
		if( $args['per_page'] > 0 ) :
			if( ! $args['paged'] )
				$args['paged'] = 1;		
			$offset = ($args['paged']-1)*$args['per_page'];
			$query .= " LIMIT {$offset}, {$args['per_page']}";
		endif;

		// Resultats				
		if( $res = $this->Db->sql()->get_col( $query ) )
			return array_map( 'maybe_unserialize', $res );
	}

	/** == Récupération des valeurs de la colonne id de plusieurs éléments selon des critères == **/
	public function col_ids( $args = array() )
	{
		return $this->col( null, $args );	
	}
	
	/* = LIGNE = */
	/** == Récupération des arguments d'un élément selon des critères == **/
	public function row( $args = array(), $output = OBJECT )
	{
		// Traitement des arguments
		$args['per_page'] 	= 1;
		
		// Bypass	
		if( ! $ids = $this->col_ids( $args ) )
			return null;
		$id = current( $ids );
						
		return $this->row_by_id( $id, $output );
	}
	
	/** == Récupération d'un élément selon un champ et sa valeur == **/
	public function row_by( $col_name = null, $value, $output = OBJECT )
	{
		$name 			= $this->Db->Name;
		$primary_key 	= $this->Db->Primary;
		
		// Traitement de l'intitulé de la colonne
		if( is_null( $col_name ) )
			$col_name = $primary_key;
		elseif( ! $col_name = $this->Db->isCol( $col_name ) )
			return null;
		
		$type = $this->Db->getColAttr( $col_name, 'type' );

		if( in_array( $type, array( 'INT', 'BIGINT' ) ) )
			$query = "SELECT * FROM {$name} WHERE {$name}.{$col_name} = %d";
		else
			$query = "SELECT * FROM {$name} WHERE {$name}.{$col_name} = %s";
		
		if( ! $item =  $this->Db->sql()->get_row( $this->Db->sql()->prepare( $query, $value ) ) )
			return;
		
		// Délinéarisation des tableaux
		$item = (object) array_map( 'maybe_unserialize', get_object_vars( $item ) );

		// Mise en cache
		wp_cache_add( $item->{$primary_key}, $item, $name );

		if ( $output == OBJECT ) :
			return ! empty( $item ) ? $item : null;
		elseif ( $output == ARRAY_A ) :
			return ! empty( $item ) ? get_object_vars( $item ) : null;
		elseif ( $output == ARRAY_N ) :
			return ! empty( $item ) ? array_values( get_object_vars( $item ) ) : null;
		elseif ( strtoupper( $output ) === OBJECT ) :
			return ! empty( $item ) ? $item : null;
		endif;
	}
		
	/** == Récupération des arguments d'un élément selon son id == **/
	public function row_by_id( $id, $output = OBJECT )
	{
		return $this->row_by( null, $id, $output );
	}
	
	/* = LIGNES = */
	/** == Récupération des arguments de plusieurs éléments selon des critères == **/
	public function rows( $args = array(), $output = OBJECT )
	{		
		// Bypass	
		if( ! $ids = $this->col_ids( $args ) )
			return;
		
		$r = array();
		foreach( (array) $ids as $id )
			$r[] = $this->row_by_id( $id, $output );
		
		return $r;
	}
	
	/* = ELEMENT VOISIN = */
	/** == Récupération de l'élément voisin selon un critère == **/
	public function adjacent( $id, $previous = true, $args = array(),  $output = OBJECT )
	{
		$name 			= $this->Db->Name;
		$primary_key 	= $this->Db->Primary;
		
		// Traitement des arguments
		$defaults = array(
			'item__in'		=> '',
			'item__not_in'	=> '',
			's'				=> ''
		);
		// Traitement des arguments
		$parse = $this->Db->parse();
		$args = $parse->query_vars( $args, $defaults );
		unset( $args[$primary_key] );
		
		$op 				= $previous ? '<' : '>';
		$args['order'] 		= $previous ? 'DESC' : 'ASC';
		$args['$orderby'] 	= $this->Db->primary_key;			
		
		// Traitement de la requête					
		/// Selection de la table de base de données
		$query = "SELECT * FROM {$name}";
		
		// Condition de jointure
		$query .= $parse->clause_join( $args );
		
		/// Conditions definies par les arguments de requête
		if( $clause_where = $parse->clause_where( $args ) )
			$query .= " ". $clause_where;
		
		/// Conditions spécifiques
		$query .= " AND {$name}.{$primary_key} $op %d";
		
		/// Recherche de terme
		if( $clause_search = $parse->clause_search( $args['s'] ) )
			$query .= " ". $clause_search;

		/// Inclusions
		if( $clause__in = $parse->clause__in( $args['item__in'] ) )
			$query .= " ". $clause__in;
		
		/// Exclusions
		if( $clause__not_in = $parse->clause__not_in( $args['item__not_in'] ) )
			$query .= " ". $clause__not_in;
		
		/// Groupe
		if( $clause_group_by = $parse->clause_group_by() )
			$query .= " ". $clause_group_by;	
			
		/// Ordre
		if( $clause_order = $parse->clause_order( $args['orderby'], $args['order'] ) )
			$query .= $clause_order;

		if( ! $item =  $this->Db->sql()->get_row( $this->Db->sql()->prepare( $query, $id ) ) )
			return;
		
		// Délinéarisation des tableaux
		$item = (object) array_map( 'maybe_unserialize', get_object_vars( $item ) );

		// Mise en cache
		wp_cache_add( $item->{$primary_key}, $item, $name );
		
		if ( $output == OBJECT ) :
			return ! empty( $item ) ? $item : null;
		elseif ( $output == ARRAY_A ) :
			return ! empty( $item ) ? get_object_vars( $item ) : null;
		elseif ( $output == ARRAY_N ) :
			return ! empty( $item ) ? array_values( get_object_vars( $item ) ) : null;
		elseif ( strtoupper( $output ) === OBJECT ) :
			return ! empty( $item ) ? $item : null;
		endif;
	}

	/* == Récupération de l'élément précédent == */
	public function previous( $id, $args = array(), $output = OBJECT ){
		return $this->adjacent( $id, true, $args, $output );
	}
	
	/* == Récupération de l'élément suivant == */
	public function next( $id, $args = array(), $output = OBJECT ){
		return $this->adjacent( $id, false, $args, $output );
	}
}
