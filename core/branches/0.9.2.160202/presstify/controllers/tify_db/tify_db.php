<?php
require_once ( dirname( __FILE__). '/tify_db-wp.php' );

class tiFy_Db{
	/* = ARGUMENTS = */
	// Arguments de traitement reservé par le système
	private	$reserved_args 		= array( 'include' /** @todo deprecated alias item__in **/, 'item__in', 'exclude' /** @todo deprecated alias item__not_in **/, 'item__not_in', 'search', 'per_page', 'paged', 'order', 'orderby', 'item_meta' );	
			
	public	// Configuration
			$install			= true,
			$update				= false,
			$col_prefix_auto 	= true,
			$has_meta			= false,
			$lock_time			= 180,
			
			// Paramètres
			$table,					// Nom de la table hors prefixe
			$primary_col,			// Clé primaire hors préfixe
			$primary_key,			// Clé primaire préfixée
			$cols,					// Nom des colonnes hors préfixe			
			$col_names,				// Nom des colonnes préfixées
			$locks;					// Verrous d'édition
	
	/* = CONSTRUCTEUR = */
	function __construct(){
		$this->parse_cols();
		reset( $this->cols );
		$this->primary_col = key( $this->cols ); 
		$this->primary_key	= ( $this->cols[$this->primary_col]['prefix'] ) ? $this->col_prefix . $this->primary_col : $this->primary_col;

		// Définition des colonnes
		$this->col_names = array(); $this->search_cols = array();	
		foreach( (array) $this->cols as $name => $args ) :
			$col_name = $args['prefix'] ? $this->col_prefix . $name : $name;
			// Definition des noms de colonnes
			$this->col_names[] 	= $col_name;
			$this->col_{$col_name} = $args;
			// Définition des colonnes de recherche
			if( $args['search'] )
				$this->search_cols[] = $col_name;
		endforeach;

		$this->wpdb_table = $this->set_table();
		if( $this->has_meta )
			is_string( $this->has_meta )? $this->wpdb_metatable = $this->set_table( $this->has_meta .'meta' ) : $this->wpdb_metatable = $this->set_table( $this->table .'meta' );
		
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );		 
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == GLOBAL == **/
	/*** === Initialisation globale de Wordpress === ***/
	function wp_init(){
		// Installation des tables
		$this->install();		
	}
	
	/* = INITIALISATION = */
	/** == Définition des tables == **/
	function set_table( $table = null ){
		global $wpdb;
		
		if( ! $table )
			$table = $this->table;
		
		// Bypass
		if( in_array( $table, $wpdb->tables ) )
			return $wpdb->{$table};
		
		array_push( $wpdb->tables, $table );
				
		$wpdb->set_prefix( $wpdb->base_prefix );
		
		return $wpdb->{$table};
	}
	
	/** == Installation == **/
	function install(){
		global $wpdb;
		
		if( ! $this->install )
			return;
		 
		// Bypass
		if( $current_version = get_option( 'tify_db_'. $this->wpdb_table, 0 ) ) 
			return;
		
		// Création des tables
		require_once( ABSPATH .'wp-admin/install-helper.php' );	
		
		if( version_compare( $current_version, 1, '>=' ) )
			return;
		
		$charset_collate = $wpdb->get_charset_collate();
		
		// Création de la table principale.
		$create_ddl = "CREATE TABLE $this->wpdb_table ( ";
		$_create_ddl = array();
		foreach( $this->col_names as $col_name )
			$_create_ddl[] = $this->create_dll( $col_name );
		$create_ddl .= implode( ', ', $_create_ddl );
		$create_ddl .= ", PRIMARY KEY ( $this->primary_key )";
		$create_ddl .= $this->create_dll_keys();		
		$create_ddl .= " ) $charset_collate;";
		
		maybe_create_table( 
			$this->wpdb_table,
			$create_ddl 
		);
		
		// Création de la table des metadonnées
		if( $this->has_meta ) :
			$meta_create_ddl  = "CREATE TABLE $this->wpdb_metatable ( ";
			$meta_create_ddl .= "meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT, ";
  			$meta_create_ddl .= "{$this->table}_id bigint(20) unsigned NOT NULL DEFAULT '0', ";
  			$meta_create_ddl .= "meta_key varchar(255) DEFAULT NULL, ";
			$meta_create_ddl .= "meta_value longtext";
			$meta_create_ddl .= ", PRIMARY KEY ( meta_id )";
			$meta_create_ddl .= ", KEY {$this->table}_id ( {$this->table}_id )";
			$meta_create_ddl .= ", KEY meta_key ( meta_key )";
			$meta_create_ddl .= " ) $charset_collate;";
			
			maybe_create_table( 
				$this->wpdb_metatable,
				$meta_create_ddl 
			);			
		endif;
		
		update_option( 'tify_db_'. $this->wpdb_table, 1 );
	}
	
	/** == == **/
	function create_dll( $col_name ){
		$types_allowed = array( 
			// Numériques
			'tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double', 'real', 'bit', 'boolean', 'serial',
			// Dates
			'date', 'datetime', 'timestamp', 'time', 'year',
			//Textes
			'char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'binary', 'varbinary', 'tinyblob', 'mediumblob', 'blob', 'longblob', 'enum', 'set'
			// 
		);
		$defaults = array(
			'type'				=> false,
			'size'				=> false,
			'unsigned'			=> false,			
			'auto_increment'	=> false,
			'default'			=> false
		);		
		$args = wp_parse_args( $this->col_{$col_name}, $defaults );
		extract( $args );
		
		$type = strtolower( $type );
		if( ! in_array( $type, $types_allowed ) )
			return;
		
		$create_ddl  = "";
		$create_ddl .= "$col_name $type";
		
		if( $size )
			$create_ddl .= "($size)";
			
		if( $unsigned || ( $col_name === $this->primary_key ) )	
			$create_ddl .= " UNSIGNED";	
		
		if( $auto_increment || ( $col_name === $this->primary_key ) )	
			$create_ddl .= " AUTO_INCREMENT";
		
		if( ! is_null( $default ) ) :
			if( is_numeric( $default ) )
				$create_ddl .= " DEFAULT ". $default ." NOT NULL";
			elseif( is_string( $default ) )
				$create_ddl .= " DEFAULT '". $default ."' NOT NULL";
			else		
				$create_ddl .=  " NOT NULL";
		else :
			$create_ddl .=  " DEFAULT NULL";
		endif;	
			
		return $create_ddl;
	}
	
	/** == Création des clefs d'index == **/
	function create_dll_keys( ){
		$create_dll_keys = array();
		foreach( $this->col_names as $col_name ) :
			if( empty( $this->col_{$col_name}['key'] ) )
				continue;
			$_create_dll_keys = ( is_array( $this->col_{$col_name}['key'] ) ) ? implode( ', ', $this->col_{$col_name}['key'] ) : $this->col_{$col_name}['key'];
						
			$create_dll_keys[] = "KEY $col_name ( $_create_dll_keys )";
		endforeach;	
		
		if( ! empty( $create_dll_keys ) )
			return ", ". implode( ', ', $create_dll_keys );
	}
	
	/** == Traitement des arguments des colonne == **/
	function parse_cols(){
		foreach( $this->cols as &$col )
			$col = $this->parse_col( $col );
	}
	
	/** == Assignation des arguments par défaut d'une colonne == **/
	function parse_col( $col ){
		$defaults = array(
			'prefix'		=> true,
			'search' 		=> false
		);
		$col = wp_parse_args( $col, $defaults );
		
		return $col;
	}
	
	/** == Récupération de la liste des colonnes de la table == **/
	function get_col_names(){
		return $this->col_names;
	}
	
	/** == Récupération d'un attribut de colonne selon son nom == **/
	function get_col_attr( $col_name, $attr ){
		if( ( $_col_name = $this->_col_exists( $col_name ) ) && isset( $this->col_{$_col_name}[$attr] ) )
			return $this->col_{$_col_name}[$attr]; 
	}
		
	/* = REQUÊTES = */
	/** == Valeur de la prochaine clé primaire == **/
	public function next_primary_key(){
		global $wpdb;
		
		if( $last_insert_id = $wpdb->query( "SELECT LAST_INSERT_ID() FROM {$this->wpdb_table}" ) )
			return ++$last_insert_id;
	}
	
	/** == Compte le nombre d'éléments == **/
	public function count_items( $args = array() ){
		global $wpdb;
		
		// Traitement des arguments
		$defaults = array(
			'exclude'	=> '',
			'search'	=> '',
			'limit' 	=> -1
		);
		$args = $this->_parse_query_vars( $args, $defaults );
		extract( $args, EXTR_SKIP );	
		// Requête
		$query  = "SELECT COUNT( {$this->wpdb_table}.{$this->primary_key} ) FROM {$this->wpdb_table}";	
		/// Conditions
		$query .= " WHERE 1";
		//// Conditions prédéfinies
		$query .= " ". $this->_parse_conditions( $args, $defaults );
		/// Recherche
		if( $this->search_cols && ! empty( $search ) ) :
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$query .= " AND (";
			foreach( $this->search_cols as $search_col ) :
				$search_query[] = "{$this->wpdb_table}.{$search_col} LIKE '{$like}'";
			endforeach;
			$query .= join( " OR ", $search_query );
			$query .= ")";	
		endif;
		/// Exclusions
		if( $exclude )
			$query .= $this->_parse_exclude( $exclude );
		/// Inclusions
		/// Non puisque c'est la valeur de count( $item__in )
		/*if( $item__in = $this->_parse_item__in( $item__in ) )
			$query .= $this->_parse_query_item__in( $item__in );*/
		//// Limite
		if( $limit > -1 )
			$query .= " LIMIT $limit";
		
		// Résultat		
		return (int) $wpdb->get_var( $query );
	}

	/** == Récupération d'une valeur pour un élément selon des critères == **/
	public function get_item_var( $var, $args = array() ){
		global $wpdb;
		
		if( ! $col = $this->_col_exists( $var ) )
			return;

		// Traitement des arguments
		$defaults = array(
			'item__in'	=> '',
			'exclude'	=> '',
			'search'	=> '',
			'order' 	=> 'DESC',
			'orderby' 	=> $this->primary_col
		);
		$args = $this->_parse_query_vars( $args, $defaults );
		extract( $args, EXTR_SKIP );
		
		// Requête
		$query  = "SELECT {$this->wpdb_table}.{$col} FROM {$this->wpdb_table}";			
		/// Conditions
		$query .= " WHERE 1";
		//// Conditions prédéfinies
		$query .= " ". $this->_parse_conditions( $args, $defaults );
		/// Recherche
		if( $this->search_cols && ! empty( $search ) ) :
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$query .= " AND (";
			foreach( $this->search_cols as $search_col ) :
				$search_query[] = "{$this->wpdb_table}.{$search_col} LIKE '{$like}'";
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
			$query .= " ORDER BY FIELD( {$this->wpdb_table}.{$this->primary_key}, $item__in )";
		else
			$query .= $this->_parse_order( $orderby, $order );

		if( $var = $wpdb->get_var( $query ) )
			return maybe_unserialize( $var );
	}

	/** == Récupération de la valeur de plusieurs éléments == **/
	public function get_items_col( $col = null, $args = array() ){
		global $wpdb;
		
		// Traitement des arguments
		$defaults = array(
			'item__in'	=> '',
			'exclude'	=> '',
			'search'	=> '',				
			'per_page' 	=> -1,
			'paged' 	=> 1,
			'order' 	=> 'DESC',
			'orderby' 	=> $this->primary_col
		);
		$args = $this->_parse_query_vars( $args, $defaults );
		extract( $args, EXTR_SKIP );
		
		$col = ! $col ?  $this->primary_col : $col;
		if( $this->col_prefix_auto )
			$col = $this->col_prefix . $col;
		// Requête
		$query  = "SELECT {$this->wpdb_table}.{$col} FROM {$this->wpdb_table}";			
		/// Conditions
		$query .= " WHERE 1";
		//// Conditions prédéfinies
		$query .= " ". $this->_parse_conditions( $args, $defaults );
		/// Recherche
		if( $this->search_cols && ! empty( $search ) ) :
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$query .= " AND (";
			foreach( $this->search_cols as $search_col ) :
				$search_query[] = "{$this->wpdb_table}.{$search_col} LIKE '{$like}'";
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
			$query .= " ORDER BY FIELD( {$this->wpdb_table}.{$this->primary_key}, $item__in )";
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

	/** == Récupération de l'id d'un élément == **/
	public function get_item_id( $args = array() ){
		return $this->get_item_var( $this->primary_key, $args );	
	}
		
	/** == Récupération des ids d'élément == **/
	public function get_items_ids( $args = array() ){
		return $this->get_items_col( null, $args );	
	}
	
	/** == Récupération d'un élément selon son id == **/
	public function get_item_by_id( $id, $output = OBJECT ){
		global $wpdb;
		
		// Récupération du cache
		if( $db_cache = wp_cache_get( $id, $this->table ) )
			return $db_cache;

		if( in_array( $this->col_{$this->primary_key}['type'], array( 'INT', 'BIGINT' ) ) )
			$query = "SELECT * FROM {$this->wpdb_table} WHERE {$this->wpdb_table}.{$this->primary_key} = %d";
		else
			$query = "SELECT * FROM {$this->wpdb_table} WHERE {$this->wpdb_table}.{$this->primary_key} = %s";
		
		if( ! $item =  $wpdb->get_row( $wpdb->prepare( $query, $id ) ) )
			return;
		
		// Délinéarisation des tableaux
		$item = (object) array_map( 'maybe_unserialize', get_object_vars( $item ) );

		// Mise en cache
		wp_cache_add( $id, $item, $this->table );
		
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

	/** == Récupération d'un élément selon un champ et sa valeur == **/
	public function get_item_by( $field, $value, $output = OBJECT ){
		global $wpdb;
		
		if( $this->col_prefix_auto )
			$field = $this->col_prefix . $field;
		if( ! in_array( $field, $this->col_names ) )
			return;	

		if( in_array( $this->col_{$field}['type'], array( 'INT', 'BIGINT' ) ) )
			$query = "SELECT * FROM {$this->wpdb_table} WHERE {$this->wpdb_table}.{$field} = %d";
		else
			$query = "SELECT * FROM {$this->wpdb_table} WHERE {$this->wpdb_table}.{$field} = %s";
		
		if( ! $item =  $wpdb->get_row( $wpdb->prepare( $query, $value ) ) )
			return;
		
		// Délinéarisation des tableaux
		$item = (object) array_map( 'maybe_unserialize', get_object_vars( $item ) );

		// Mise en cache
		wp_cache_add( $item->{$this->primary_key}, $item, $this->table );
		
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

	/** == Récupération d'une liste d'éléments == **/
	public function get_items( $args = array(), $output = OBJECT ){		
		// Bypass	
		if( ! $ids = $this->get_items_ids( $args ) )
			return;		
		// Résultats
		$r = array();
		foreach( $ids as $id )
			$r[] = $this->get_item_by_id( $id, $output );				
		return $r;
	}

	/** == Récupération d'un élément == **/
	public function get_item( $args = array(), $output = OBJECT ){
		global $wpdb;
		// Traitement des arguments
		$args['per_page'] 	= 1;
		
		// Bypass	
		if( ! $ids = $this->get_items_ids( $args ) )
			return null;
		$id = current( $ids );
						
		return $this->get_item_by_id( $id, $output );
	}
	
	/** == Récupération d'une valeur pour un élément selon son id == **/
	public function get_item_var_by_id( $id, $var ){
		global $wpdb;
		
		if( in_array( $var, $this->col_names ) )
			$col = $var;
		elseif( in_array( $this->col_prefix.$var, $this->col_names ) )
			$col = $this->col_prefix.$var;
		else
			return;
			
		if( ( $item = wp_cache_get( $id, $this->table ) ) && isset( $item->{$col} ) )
			return $item->{$col};
		else
			return $this->get_item_var( $col, array( "{$this->primary_key}" => $id ) );
	}	
	
	/** == Création d'un élément == 
	 * @todo doit devenir save_item et insert_item ne doit gérer que l'insertion
	 **/
	public function insert_item( $data = array() ){
		global $wpdb;

		if( ! empty( $data[$this->primary_key] ) && ( $item_id = $this->get_item_var_by_id( $data[$this->primary_key],  ( $this->col_prefix_auto ? $this->primary_col : $this->primary_key ) ) ) ) :
			unset( $data[$this->primary_key] );
			return $this->update_item( $item_id, $data );
		else :
			$item_meta = false;				 
			if( $this->has_meta && isset( $data['item_meta'] ) && ( $item_meta = $data['item_meta'] ) ) 
				unset( $data['item_meta'] );
				
			$data = $this->_parse_fields( $data );			
			$data = array_map( 'maybe_serialize', $data );
			
			$wpdb->insert( $this->wpdb_table, $data );
			
			$id = $wpdb->insert_id;
			
			foreach( (array) $item_meta as $meta_key => $meta_value )
				update_metadata( $this->table, $id, $meta_key, $meta_value );			
			
			return $id;
		endif;
	}
	
	/** == Mise à jour d'un élément == **/
	public function update_item( $id, $data = array() ){
		global $wpdb;
		
		$item_meta = false;				 
		if( $this->has_meta && isset( $data['item_meta'] ) && ( $item_meta = $data['item_meta'] ) ) 
			unset( $data['item_meta'] );
		
		$data = $this->_parse_fields( $data );
		$data = array_map( 'maybe_serialize', $data );
		
		$wpdb->update( $this->wpdb_table, $data, array( $this->primary_key => $id ) );
		
		foreach( (array) $item_meta as $meta_key => $meta_value )
			update_metadata( $this->table, $id, $meta_key, $meta_value );
		
		return $id;
	}
		
	/** == Suppression d'un élément == **/
	public function delete_item( $id ){
		global $wpdb;
		
		return $wpdb->delete( $this->wpdb_table, array( $this->primary_key => $id ), '%d' );
	}
	
	/** == Récupération de l'élément voisin selon un critère == **/
	public function get_adjacent_item( $col, $value, $previous = true, $args = array(),  $output = OBJECT ){
		global $wpdb;
		
		if( ! $col = $this->_col_exists( $col ) )
			return;
		
		$op 		= $previous ? '<' : '>';
		$order 		= $previous ? 'DESC' : 'ASC';
		$orderby 	= $col;
		
		// Traitement des arguments
		$defaults = array(
			'item__in'	=> '',
			'exclude'	=> '',
			'search'	=> ''
		);
		unset( $args[$this->primary_col], $args[$this->primary_key] );		
		$args = $this->_parse_query_vars( $args, $defaults );
		extract( $args, EXTR_SKIP );		
		
		// Requête
		$query = "SELECT * FROM {$this->wpdb_table}";		
		/// Conditions
		$query .= " WHERE 1";
		$query .= " AND {$this->wpdb_table}.{$col} $op %d";
		//// Conditions prédéfinies
		$query .= " ". $this->_parse_conditions( $args, $defaults );
		/// Recherche
		if( $this->search_cols && ! empty( $search ) ) :
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$query .= " AND (";
			foreach( $this->search_cols as $search_col ) :
				$search_query[] = "{$this->wpdb_table}.{$search_col} LIKE '{$like}'";
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
		else
			$query .= $this->_parse_order( $orderby, $order );
		
		if( ! $item =  $wpdb->get_row( $wpdb->prepare( $query, $value ) ) )
			return;
		
		// Délinéarisation des tableaux
		$item = (object) array_map( 'maybe_unserialize', get_object_vars( $item ) );

		// Mise en cache
		wp_cache_add( $item->{$this->primary_key}, $item, $this->table );
		
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
	public function get_prev_item( $col, $value, $args = array(), $output = OBJECT ){
		return $this->get_adjacent_item( $col, $value, true, $args, $output );
	}
	
	/* == Récupération de l'élément suivant == */
	public function get_next_item( $col, $args = array(), $output = OBJECT ){
		return $this->get_adjacent_item( $col, $value, false, $args, $output );
	}
	
	/* == Récupération de l'élément voisin == */
	public function get_adjacent_item_by_id( $id, $previous = true, $args = array(), $output = OBJECT ){
		return $this->get_adjacent_item( $this->primary_key, $id, $previous, $args, $output );
	}
	
	/* == Récupération de l'élément précédent == */
	public function get_prev_item_by_id( $id, $args = array(), $output = OBJECT ){
		return $this->get_adjacent_item_by_id( $id, true, $args, $output );
	}
	
	/* == Récupération de l'élément suivant == */
	public function get_next_item_by_id( $id, $args = array(), $output = OBJECT ){
		return $this->get_adjacent_item_by_id( $id, false, $args, $output );
	}	
	
	/* = METADONNÉES = */		
	/** == Récupération de la valeur de la metadonnée d'un élément == 
 	 * @param int    $id  		 	ID de l'item
 	 * @param string $meta_key 		Optionel. Index de la métadonnée. Retournera, s'il n'est pas spécifié
 	 * 		                    	toutes les metadonnées relative à l'objet.
 	 * @param bool   $single    	Optionel, default is true.
 	 *                         		Si true, retourne uniquement la première valeur pour l'index meta_key spécifié.
	 *                          	Ce paramètres n'a pas d'impact lorsqu'aucun index meta_key n'est spécifié. 
	 **/
	function get_item_meta( $id, $meta_key = '', $single = true ){
		return get_metadata( $this->table, $id, $meta_key, $single );
	}
	
	/** == Récupération d'une metadonné selon sa meta_id == **/
	function get_item_meta_by_mid( $meta_id ){
		return get_metadata_by_mid( $this->table, $meta_id );
	}
	
	/** == Récupération de toutes les metadonnés d'un élément == 
	 * @param int    $id  		 	ID de l'item
	 **/
	function get_item_metas( $id ){
		return $this->get_item_meta( $id );
	}
	
	/** == Ajout d'une metadonnée d'un élément == 
 	 * @param int    $id  		 	ID de l'item
 	 * @param string $meta_key   	Index de la métadonnée.
 	 * @param mixed  $meta_value 	Valeur de la métadonnée. Les données non scalaires seront serialisées.
 	 * @param bool   $unique     	Optionnel, true par défaut.
	 **/
	function add_item_meta( $id, $meta_key, $meta_value, $unique = true ){
		return add_metadata( $this->table, $id, $meta_key, $meta_value, $unique );
	}
	
	/** == Mise à jour de la metadonné d'un élément == **/
	function update_item_meta( $id, $meta_key, $meta_value, $prev_value = '' ){
		return update_metadata( $this->table, $id, $meta_key, $meta_value, $prev_value );
	}
	
	/** == Récupération de la metadonné d'un élément == **/
	function delete_item_meta( $id, $key, $value = '' ){
		return delete_metadata( $this->table, $id, $key, $value );
	}
	
	/** == !!!! Suppression de toutes les métadonnées d'un élément == **/
	function delete_item_metadatas( $id ){
		global $wpdb;
		
		return $wpdb->delete( $this->wpdb_metatable, array( $this->table .'_id' => $id ), '%d' );
	}
	
	/* = VERROUILLAGE = */
	/** == Récupération des types de verrou == **/
	private function get_lock_types(){
		if( $this->has_meta && ! empty( $this->locks ) )
			return $this->locks;		
	}
	
	/** == Vérifie si un type de verrou est actif == **/
	public function has_lock_type( $type = null ){
		if( $type )
			return ( $this->get_lock_types() && in_array( $type, $this->get_lock_types() ) );
		elseif( $this->get_lock_types() )
			return true;
	}
	
	/** == Récupération du délai de vérouillage == **/
	public function get_lock_time(){
		return (int) $this->lock_time;
	}
	
	/** == Vérification du verrouillage selon son type == **/
	public function check_lock( $item_id, $type = 'edit', $user_id = 0, $lock_user = 0 ){
		if( ! $this->has_lock_type( $type ) )
			return;
		if( ! $user_id && ( 0 == ( $user_id = get_current_user_id() ) ) )
			return false;
		if ( ! $item = $this->get_item_by_id( $item_id ) )
			return false;
		
		if ( ! $lock = $this->get_item_meta( $item->{$this->primary_key}, "_{$type}_lock", true ) )
			return false;

		$lock = explode( ':', $lock );
		$time = $lock[0];
		$user = isset( $lock[1] ) ? $lock[1] : $lock_user;

		if ( $time && $time > time() - $this->lock_time && $user != $user_id )
			return $user;
		return false;		
	}
	
	/** == Vérification du verrouillage général d'un élément == **/
	public function check_locks( $item_id ){
		if( ! $this->get_lock_types() )
			return;
		if ( ! $item = $this->get_item_by_id( $item_id ) )
			return false;
		
		global $wpdb;
		$callback = function($lock){ return "\"_". $lock ."_lock\""; };
		$locks = implode(',', array_map( $callback, $this->get_lock_types() ) );
		$query = "SELECT meta_id FROM {$this->wpdb_metatable} WHERE {$this->table}_id = %d AND meta_key IN ({$locks})";
		
		return $wpdb->query( $wpdb->prepare( $query, $item->{$this->primary_key} ) );	
	}
		
	/** == Définition du verrouillage d'un élément pour un utilisateur == **/
	public function set_lock( $item_id, $type = 'edit', $user_id = 0 ){
		if( ! $this->has_lock_type( $type ) )
			return false;
		if( ! $item = $this->get_item_by_id( $item_id ) )
			return false;
		if( ! $user_id && ( 0 == ( $user_id = get_current_user_id() ) ) )
			return false;
		
		$now = time();
		$lock = "{$now}:{$user_id}";

		$this->update_item_meta( $item->{$this->primary_key}, "_{$type}_lock", $lock );
	}
	
	/** == Récupération du verrouillage d'un élément selon son type == **/
	public function get_lock( $item_id, $type = 'edit' ){
		if( ! $this->has_lock_type( $type ) )
			return null;
		
		return $this->get_item_meta( $item->{$this->primary_key}, "_{$type}_lock", true );
	}
	
	/** == Translation du verrouillage d'un élément pour un utilisateur == **/
	public function translate_lock( $item_id, $type = 'edit', $user_id = 0 ){		
		if( ! $this->has_lock_type( $type ) )
			return false;
		if( ! $item = $this->get_item_by_id( $item_id ) )
			return false;
		if( ! $user_id && ( 0 == ( $user_id = get_current_user_id() ) ) )
			return false;
		if ( ! $lock = $this->get_item_meta( $item->{$this->primary_key}, "_{$type}_lock", true ) )
			return false;
		
		$active_lock = array_map( 'absint', explode( ':', $lock ) );
		if ( $active_lock[1] === $user_id )
			return false;
		
		$new_lock = time() . ':' . $user_id;
		$this->update_item_meta( $item->{$this->primary_key}, "_{$type}_lock", $new_lock, implode( ':', $active_lock ) );
		
		return true;		
	}
	
	/** == Translation du verrouillage d'un élément pour un utilisateur == **/
	public function delete_lock( $item_id, $type = 'edit', $user_id = 0 ){		
		if( ! $this->has_lock_type( $type ) )
			return false;
		if( ! $item = $this->get_item_by_id( $item_id ) )
			return false;
		if( ! $user_id && ( 0 == ( $user_id = get_current_user_id() ) ) )
			return false;
		if ( ! $lock = $this->get_item_meta( $item->{$this->primary_key}, "_{$type}_lock", true ) )
			return false;
		
		$active_lock = array_map( 'absint', explode( ':', $lock ) );
		if ( $active_lock[1] !== $user_id )
			return false;

		$new_lock = ( time() - $this->lock_time + 5 ) . ':' . $user_id;
		$this->update_item_meta( $item->{$this->primary_key}, "_{$type}_lock", $new_lock, implode( ':', $active_lock ) );
		
		return true;		
	}
	
	/* = CONTRÔLEURS = */
	/** == Traitements des arguments de requête == **/
	protected function _parse_query_vars( $args, $defaults ){
		$args =  wp_parse_args( $args, $defaults );	
		
		if( ! empty( $args['include'] ) ) :
			$args['item__in'] = $args['include'];
			unset(  $args['include'] );
		endif;
			
		
		return $args;	
	}
	
	/** == Traitement des arguments == **/
	protected function _parse_fields( $args ){
		foreach( $args as $k => $value ) :
			if( in_array( $k, $this->reserved_args ) ) :
				unset( $args[$k] ); continue;
			elseif( in_array( $this->col_prefix.$k, $this->col_names ) ) :				
				$col = $this->col_prefix.$k;
				unset( $args[$k] );
				$args[$col] = $value;
			elseif( ! in_array( $k, $this->col_names ) ) :
				unset( $args[$k] ); continue;
			endif;			
		endforeach;
		
		/** @todo : Typage des valeurs  ! any cf parse_conditions **/
		
		return $args;		
	}
	
	/** ==  == **/
	protected function _col_exists( $col_name ){
		if( in_array( $col_name, $this->reserved_args ) )
			return;
		elseif( in_array( $this->col_prefix . $col_name, $this->col_names ) ) 				
			return $this->col_prefix . $col_name;
		elseif( in_array( $col_name, $this->col_names ) )
			return $col_name;
	}
	
	/** == Traitement des conditions == **/
	protected function _parse_conditions( $args, $diff = array() ){
		$args = $this->_parse_fields( $args );
		$conditions = array();

		foreach( $args as $col => $value ) :			
			if( ( $value === 'any' ) && isset( $this->col_{$col}['any'] ) )
				$value = $this->col_{$col}['any'];
			
			if( is_string( $value ) ) :
				$conditions[] = "AND  {$this->wpdb_table}.{$col} = '{$value}'";
			elseif( is_bool( $value ) &&  $value ) :
				$conditions[] = "AND  {$this->wpdb_table}.{$col}";
			elseif( is_bool( $value ) &&  ! $value ) :
				$conditions[] = "AND ! {$this->wpdb_table}.{$col}";
			elseif( is_numeric( $value ) ) :
				$conditions[] = "AND {$this->wpdb_table}.{$col} = {$value}";
			elseif( is_array( $value ) ) :
				$conditions[] = "AND {$this->wpdb_table}.{$col} IN ('". implode( "', '", $value ) ."')";	
			elseif( is_null( $value ) ) :
				$conditions[] = "AND {$this->wpdb_table}.{$col} IS NULL";	
			endif;			
		endforeach;
		
		return implode( ' ', $conditions );
	}
	
	/** == Traitement de l'ordre == **/
	protected function _parse_order( $orderby, $order = 'DESC' ){
		if( $orderby = $this->_col_exists( $orderby ) )
			return " ORDER BY {$this->wpdb_table}.{$orderby} {$order}";
	}
	
	/** == Traitement des exclusions == **/
	protected function _parse_exclude( $exclude ){
		if( ! is_array( $exclude ) )
			$exclude = array( $exclude );
		
		$not_in = implode(',',  array_map( 'absint', $exclude ) );
		return " AND {$this->wpdb_table}.{$this->primary_key} NOT IN ($not_in)";
	}
	
	/** == Traitements des éléments à inclure == **/
	protected function _parse_item__in( $item_ids ){
		if( ! is_array( $item_ids ) )
			$item_ids = array( $item_ids );
		
		return implode( ',', array_map( 'absint', $item_ids ) );
	}
	
	/** == Traitement de la requête des inclusions == **/
	protected function _parse_query_item__in( $item__in ){
		return " AND {$this->wpdb_table}.{$this->primary_key} IN ($item__in)";
	}
}