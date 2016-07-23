<?php
/* = CAMPAGNES = */
class tiFy_Wistify_DbCampaign extends tiFy_Db{
	private // Référence
			$master;

	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Wistify_Master $master ){
		// Déclaration de la classe de référence
		$this->master = $master;
				
		// Définition des arguments
		$this->table 		= 'wistify_campaign';
		$this->col_prefix	= 'campaign_'; 
		$this->has_meta		= true;
		$this->cols			= array(
			'id' 			=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true
			),
			'uid' 				=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 32,
				'default'			=> null
			),
			'title'				=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 255,
				'default'			=> null,
				
				'search'			=> true
			),
			'description'		=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 255,
				'default'			=> null,
				
				'search'			=> true
			),
			'author'			=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'default'			=> 0				
			),
			'date'				=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),
			'modified'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),
			'status'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 25,
				'default'			=> 'draft',
				
				'any'				=> array( 'edit', 'preparing', 'ready', 'send', 'forwarded' )
			),
			'step'				=> array(
				'type'				=> 'INT',
				'size'				=> 2,
				'default'			=> 0
			),
			'template_name'		=> array(
				'type'				=> 'LONGTEXT'
			),
			'content_html'		=> array(
				'type'				=> 'LONGTEXT'		
			),
			'content_txt'		=> array(
				'type'				=> 'LONGTEXT'			
			),
			'recipients'		=> array(
				'type'				=> 'LONGTEXT'
			),
			'message_options'	=> array(
				'type'				=> 'LONGTEXT'
			),
			'send_options'		=> array(
				'type'				=> 'LONGTEXT'
			),
			'send_datetime'		=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			)
		);
		
		$this->locks = array( 'edit', 'send' );
			
		parent::__construct();	
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

/* = LISTES DE DIFFUSION = */
class tiFy_Wistify_DbList extends tiFy_Db{
	/* = ARGUMENTS = */
	private // Référence
			$master,
			$rel_db;

	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Wistify_Master $master ){
		// Déclaration de la classe de référence
		$this->master = $master;
				
		// Définition des arguments
		$this->table 		= 'wistify_list';
		$this->col_prefix	= 'list_'; 
		$this->primary_key	= 'list_id';
		$this->has_meta		= false;
		$this->cols			= array(
			'id' 			=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true
			),
			'uid' 			=> array(
				'type'			=> 'VARCHAR',
				'size'			=> 32
			),
			'title'			=> array(
				'type'			=> 'VARCHAR',
				'size'			=> 255,
				
				'search'		=> true
			),
			'description'	=> array(
				'type'			=> 'LONGTEXT',
				
				'search'		=> true
			),
			'date'		=> array(
				'type'			=> 'DATETIME',
				'default'		=> '0000-00-00 00:00:00'
			),
			'modified'		=> array(
				'type'			=> 'DATETIME',
				'default'		=> '0000-00-00 00:00:00'
			),
			'status'		=> array(
				'type'			=> 'VARCHAR',
				'size'			=> 20,
				'default'		=> 'publish',
				
				'any'			=> 'publish'
			),
			'menu_order'		=> array(
				'type'			=> 'BIGINT',
				'size'			=> 20,
				'default'		=> 0
			),
			'public'		=> array(
				'type'			=> 'TINYINT',
				'size'			=> 1,
				'default'		=> 1
			)
		);		
		parent::__construct();	
		
		$this->rel_db = $this->master->db_list_rel;
	}
	
	/** == Vérifie si un intitulé est déjà utilisé pour une liste de diffusion == **/
	function title_exists( $title, $list_id = null ){
		global $wpdb;
		
		$query = "SELECT COUNT(list_id) FROM {$this->wpdb_table} WHERE 1 AND list_title = %s";
		if( $list_id )
			$query .= " AND list_id != %d";
		
		return $wpdb->get_var( $wpdb->prepare( $query, $title, $list_id ) );
	}
	
	/* = REQUETES PERSONNALISÉES = */
	/** == Récupére les listes de diffusion d'un abonné == **/
	function get_subscriber_list_ids( $subscriber_id, $active = 1 ){
		global $wpdb;
		
		return $wpdb->get_col( $wpdb->prepare( "SELECT rel_list_id FROM {$this->rel_db->wpdb_table} INNER JOIN {$this->wpdb_table} ON ( {$this->rel_db->wpdb_table}.rel_list_id = {$this->wpdb_table}.list_id ) WHERE {$this->rel_db->wpdb_table}.rel_subscriber_id = %d AND {$this->rel_db->wpdb_table}.rel_active = %d", $subscriber_id, $active ) );
	}	
}

/* = RELATION LISTE <> ABONNES = */
class tiFy_Wistify_DbListRelationships extends tiFy_Db{
	/* = ARGUMENTS = */
	private // Référence
			$master;

	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Wistify_Master $master ){
		// Déclaration de la classe de référence
		$this->master = $master;
				
		// Définition des arguments
		$this->table 		= 'wistify_list_relationships';
		$this->col_prefix	= 'rel_'; 
		$this->has_meta		= false;
		$this->cols			= array(
			'id' 			=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true
			),
			'subscriber_id'	=> array(
				'type'			=> 'BIGINT',
				'size'			=> 20,
				'unsigned'		=> true
			),
			'list_id'		=> array(
				'type'			=> 'BIGINT',
				'size'			=> 20,
				'default'		=> 0,
				'unsigned'		=> true
			),
			'active'		=> array(
				'type'			=> 'TINYINT',
				'size'			=> 1
			),
			'created'		=> array(
				'type'			=> 'DATETIME',
				'default'		=> '0000-00-00 00:00:00'
			),
			'modified'		=> array(
				'type'			=> 'DATETIME',
				'default'		=> '0000-00-00 00:00:00'
			)
		);
		parent::__construct();
	}
	
	/**
	 * Suppression de toutes les relation liste de diffusion/abonnés
	 */
	 function delete_list_subscribers( $list_id ){
		global $wpdb;
	
		return $wpdb->delete( $this->wpdb_table, array( 'rel_list_id' => $list_id ) );
	}
	 
	/** == Ajout d'une relation abonné/liste de diffusion == **/
	function insert_subscriber_for_list( $subscriber_id, $list_id, $active = 0 ){
		global $wpdb;
		
		if( ! $rel = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->wpdb_table} WHERE {$this->wpdb_table}.rel_subscriber_id = %d AND {$this->wpdb_table}.rel_list_id = %d", $subscriber_id, $list_id ) ) )
			return $wpdb->insert( $this->wpdb_table, array( 'rel_list_id' => $list_id, 'rel_subscriber_id' => $subscriber_id, 'rel_created' => current_time( 'mysql' ), 'rel_active' => $active ) );
		elseif( $rel->rel_active != $active )
			return $this->update_item( $rel->rel_id, array( 'rel_active' => $active, 'rel_modified' => current_time( 'mysql' ) ) );
	}
		
	/** == Suppression d'une relation abonné/liste de diffusion == **/
	function delete_subscriber_for_list( $subscriber_id, $list_id ){
		global $wpdb;
			
		return $wpdb->delete( $this->wpdb_table, array( 'rel_list_id' => $list_id, 'rel_subscriber_id' => $subscriber_id ) );
	}
	
	/** == Suppression de toutes les relation abonné/listes de diffusion == **/
	function delete_subscriber_lists( $subscriber_id ){
		global $wpdb;
	
		return $wpdb->delete( $this->wpdb_table, array( 'rel_subscriber_id' => $subscriber_id ) );
	}
	 
	/** == Vérifie si un abonné est affilié à la liste des orphelins == **/
	function is_orphan( $subscriber_id, $active = null ){
		global $wpdb;
		
		$query = "SELECT * FROM {$this->wpdb_table} WHERE rel_subscriber_id = %d AND rel_list_id = 0";
		if( ! is_null( $active ) )
			 $query .= " AND rel_active = %d";
		return $wpdb->query( $wpdb->prepare( $query, $subscriber_id, $active ) );
	} 
}

/* = FILE DES MESSAGES = */
class tiFy_Wistify_DbQueue extends tiFy_Db{
	/* = ARGUMENTS = */
	private // Référence
			$master;

	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Wistify_Master $master ){
		// Déclaration de la classe de référence
		$this->master = $master;
				
		// Définition des arguments
		$this->table 		= 'wistify_queue';
		$this->col_prefix	= 'queue_';
		$this->primary_col = 'id';
		$this->has_meta		= false;
		$this->cols			= array(
			'id' 			=> array(
				'type'			=> 'BIGINT',
				'size'			=> 20
			),
			'email' 		=> array(
				'type'			=> 'VARCHAR',
				'size'			=> 255,
				
				'search'		=> true
			),
			'campaign_id'	=> array(				
				'type'			=> 'BIGINT',
				'size'			=> 20,
				'unsigned'		=> true
			),
			'message'	=> array(				
				'type'			=> 'BLOB'
			),
			'locked'	=> array(				
				'type'			=> 'TINYINT',
				'size'			=> 1,
				'default'		=> 0
			)
		);
		
		parent::__construct();				
	}
	
	/* = REQUETES PERSONNALISÉES = */
	/** == Suppression de la file d'une campagne == **/
	function reset_campaign( $campaign_id ){
		global $wpdb;
		
		return $wpdb->delete( $this->wpdb_table, array( 'queue_campaign_id' => $campaign_id ), '%d' );		
	}
	
	/** == Vérifie l'existance d'une campagne dans la file == **/
	function has_campaign( $campaign_id ){
		global $wpdb;
		
		return $wpdb->query( $wpdb->prepare( "SELECT queue_campaign_id FROM $this->wpdb_table WHERE queue_campaign_id = %d", $campaign_id ) );		
	}	
}

/* = RAPPORT D'ACHEMINEMENT = */
class tiFy_Wistify_DbReport extends tiFy_Db{
	/* = ARGUMENTS = */
	private // Référence
			$master;

	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Wistify_Master $master ){
		// Déclaration de la classe de référence
		$this->master = $master;
				
		// Définition des arguments
		$this->table 		= 'wistify_report';
		$this->col_prefix	= 'report_';
		$this->has_meta		= false;
		$this->cols			= array(
			'id'			=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true
			),
			'campaign_id'	=> array(
				'type'			=> 'BIGINT',
				'size'			=> 20,
				'unsigned'		=> true,
				'default'		=> 0,
				'any'			=> true
			),
			'posted_ts'		=> array(
				'type'			=> 'INT',
				'size'			=> 13,
				'default'		=> 0
			),
			'updated_ts'	=> array(
				'type'			=> 'INT',
				'size'			=> 13,
				'default'		=> 0
			),
			'md_ts'			=> array(
				'type'			=> 'INT',
				'size'			=> 13,
				'default'		=> 0
			),
			'md__id'			=> array(				
				'type'			=> 'VARCHAR',
				'size'			=> 32
			),			
			'md_sender'		=> array(
				'type'			=> 'VARCHAR',
				'size'			=> 255
			),
			'md_template'		=> array(
				'type'			=> 'VARCHAR',
				'size'			=> 255
			),
			'md_subject'		=> array(
				'type'			=> 'VARCHAR',
				'size'			=> 255
			),
			'md_email'			=> array(
				'type'			=> 'VARCHAR',
				'size'			=> 255,
				
				'search'		=> true
			),
			'md_tags'			=> array(
				'type'			=> 'LONGTEXT'
			),
			'md_opens'			=> array(
				'type'			=> 'INT',
				'size'			=> 5,
			),
			'md_opens_detail'	=> array(
				'type'			=> 'LONGTEXT'
			),
			'md_clicks'		=> array(
				'type'			=> 'INT',
				'size'			=> 5,
			),
			'md_clicks_detail'	=> array(
				'type'			=> 'LONGTEXT',
			),
			'md_state'			=> array(
				'type'			=> 'VARCHAR',
				'size'			=> 25,
				
				'any'			=> array( 'bounced', 'deferred', 'posted', 'rejected', 'sent', 'soft-bounced', 'spam', 'unknown' )
			),
			'md_metadata'		=> array(
				'type'			=> 'LONGTEXT'
			),
			'md_smtp_events'	=> array(
				'type'			=> 'LONGTEXT'
			),
			'md_resends'		=> array(
				'type'			=> 'LONGTEXT'
			),
			'md_reject_reason'	=> array(
				'type'			=> 'LONGTEXT'
			)
		);
		
		parent::__construct();				
	}	
}

/* = ABONNES = */
class tiFy_Wistify_DbSubscriber extends tiFy_Db{
	/* = ARGUMENTS = */
	private // Référence
			$master,
			$rel_db;

	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Wistify_Master $master ){
		// Déclaration de la classe de référence
		$this->master = $master;
				
		// Définition des arguments
		$this->table 		= 'wistify_subscriber';
		$this->col_prefix	= 'subscriber_';
		$this->has_meta		= true;
		$this->cols			= array(
			'id' 			=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true
			),
			'uid' 			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 32,
				'default'			=> null
			),
			'email'			=> array(
				'type'			=> 'VARCHAR',
				'size'			=> 255,
				
				'search'		=> true
			),
			'date'			=> array(
				'type'			=> 'DATETIME',
				'default'		=> '0000-00-00 00:00:00'
			),
			'modified'		=> array(
				'type'			=> 'DATETIME',
				'default'		=> '0000-00-00 00:00:00'
			),
			'status'		=> array(
				'type'			=> 'VARCHAR',
				'size'			=> 255,
				'default'		=> 'registred',
				
				'any'			=> array( 'registred', 'waiting', 'unsubscribed' )
			)
		);		
		parent::__construct();
		
		$this->rel_db = $this->master->db_list_rel;				
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
		$query  = "SELECT COUNT( DISTINCT {$this->wpdb_table}.{$this->primary_key} ) FROM {$this->wpdb_table}";
		// Jointure
		$query .= " INNER JOIN {$this->rel_db->wpdb_table} ON {$this->wpdb_table}.subscriber_id = {$this->rel_db->wpdb_table}.rel_subscriber_id";
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
				$search_query[] = "{$this->wpdb_table}.{$search_col} LIKE '{$like}'";
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
		$query  = "SELECT DISTINCT {$this->wpdb_table}.{$col} FROM {$this->wpdb_table}";	
		// Jointure
		$query .= " INNER JOIN {$this->rel_db->wpdb_table} ON {$this->wpdb_table}.subscriber_id = {$this->rel_db->wpdb_table}.rel_subscriber_id";
				
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
		
	/* = REQUETES PERSONNALISÉES = */	
	/** == Vérifie l'existance d'un email pour un abonné == **/
	function email_exists( $email, $exclude_id = null ){
		global $wpdb;
		
		$query = "SELECT COUNT(subscriber_id) FROM {$this->wpdb_table} WHERE 1 AND subscriber_email = %s";
		if( $exclude_id )
			$query .= " AND subscriber_id != %d";
		
		return $wpdb->get_var( $wpdb->prepare( $query, $email, $exclude_id ) );
	}
}