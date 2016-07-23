<?php
class tiFy_WebAgencyCRM_DbMain{
	/* = ARGUMENTS = */
	public	$master,
			$customer,
			$partner,
			$project,
			$task;	
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_WebAgencyCRM_Master $master ){
		// Déclaration de la classe de référence principale
		$this->master = $master;
		
		$this->customer 	= new tiFy_WebAgencyCRM_DbCustomer( $this->master );
		$this->partner 		= new tiFy_WebAgencyCRM_DbPartner( $this->master );
		$this->project 		= new tiFy_WebAgencyCRM_DbProject( $this->master );
		$this->task 		= new tiFy_WebAgencyCRM_DbTask( $this->master );
		$this->team 		= new tiFy_WebAgencyCRM_DbTeam( $this->master );
	}
}

/* = CLIENTS = */
class tiFy_WebAgencyCRM_DbCustomer extends tiFy_Contest_DbUsers{
	/* = ARGUMENTS = */
	private	// Référence
			$master;
			
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_WebAgencyCRM_Master $master ){
		// Déclaration de la classe de référence principale
		$this->master = $master;
		
		parent::__construct();
	}
}

/* = PARTENAIRES = */
class tiFy_WebAgencyCRM_DbPartner extends tiFy_Db{
	/* = ARGUMENTS = */
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_WebAgencyCRM_Master $master ){
		// Déclaration de la classe de référence principale
		$this->master = $master;
			
		// Définition des arguments
		$this->install 		= true;
		$this->table 		= 'tify_wacrm_partner';
		$this->col_prefix	= 'partner_'; 
		$this->has_meta		= true;
		$this->cols			= array(
			'id'				=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true
			),
			'ref'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 64
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
			'date_gmt'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),			
			'title'				=> array(
				'type'				=> 'TEXT',				
				'search'			=> true
			),
			'content'			=> array(
				'type'				=> 'LONGTEXT',
				'search'			=> true
			),
			'status'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 20,
				'default'			=> 'publish'				
			),
			'modified'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),
			'modified_gmt'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			)
		);
		
		parent::__construct();	
	}
}


/* = PROJETS = */
class tiFy_WebAgencyCRM_DbProject extends tiFy_Db{
	/* = ARGUMENTS = */
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_WebAgencyCRM_Master $master ){
		// Déclaration de la classe de référence principale
		$this->master = $master;
			
		// Définition des arguments
		$this->install 		= true;
		$this->table 		= 'tify_wacrm_project';
		$this->col_prefix	= 'project_'; 
		$this->has_meta		= true;
		$this->cols			= array(
			'id'				=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true
			),
			'customer_id'		=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true
			),
			'ref'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 64
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
			'date_gmt'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),			
			'title'				=> array(
				'type'				=> 'TEXT',				
				'search'			=> true
			),
			'content'			=> array(
				'type'				=> 'LONGTEXT',
				'search'			=> true
			),
			'status'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 20,
				'default'			=> 'publish'				
			),
			'modified'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),
			'modified_gmt'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			)
		);
		
		parent::__construct();	
	}
}

/* = PROJETS = */
class tiFy_WebAgencyCRM_DbTask extends tiFy_Db{
	/* = ARGUMENTS = */
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_WebAgencyCRM_Master $master ){
		// Déclaration de la classe de référence principale
		$this->master = $master;
			
		// Définition des arguments
		$this->install 		= true;
		$this->table 		= 'tify_wacrm_task';
		$this->col_prefix	= 'task_'; 
		$this->has_meta		= true;
		$this->cols			= array(
			'id'				=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true
			),
			'project_id'		=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'default'			=> 0
			),
			'employee'			=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'default'			=> 0
			),
			'author'			=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'default'			=> 0
			),
			'start_datetime'	=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),
			'end_datetime'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),			
			'title'				=> array(
				'type'				=> 'TEXT',				
				'search'			=> true
			),
			'content'			=> array(
				'type'				=> 'LONGTEXT',
				'search'			=> true
			),
			'status'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 20,
				'default'			=> 'publish'				
			),
			'date'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),
			'date_gmt'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),
			'modified'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),
			'modified_gmt'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			)
		);
		
		parent::__construct();	
	}
		
	public function cumul( $project_id ){
		global $wpdb;
		
		return $wpdb->get_var( $wpdb->prepare( "SELECT SEC_TO_TIME( SUM( TIME_TO_SEC( TIMEDIFF(task_end_datetime,task_start_datetime) ) ) ) FROM {$this->wpdb_table} WHERE {$this->wpdb_table}.task_project_id = %d", $project_id ) );		
	}
	
	public function cumul_by_employee( $project_id ){
		global $wpdb;
		
		return $wpdb->get_results( $wpdb->prepare( "SELECT task_employee, SEC_TO_TIME( SUM( TIME_TO_SEC( TIMEDIFF(task_end_datetime,task_start_datetime) ) ) ) as time FROM {$this->wpdb_table} WHERE {$this->wpdb_table}.task_project_id = %d GROUP BY task_employee", $project_id ) );		
	}
}

/* = EQUIPE = */
class tiFy_WebAgencyCRM_DbTeam extends tiFy_Contest_DbUsers{
	/* = ARGUMENTS = */
	private	// Référence
			$master;
			
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_WebAgencyCRM_Master $master ){
		// Déclaration de la classe de référence principale
		$this->master = $master;
		
		parent::__construct();
	}
}