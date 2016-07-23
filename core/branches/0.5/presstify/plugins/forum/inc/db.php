<?php
class tiFy_Forum_DbMain{
	/* = ARGUMENTS = */
	public	$master,
			$contribution,
			$contributor,
			$multi,
			$topic;
	
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Forum_Master $master ){
		// Déclaration de la classe de référence principale
		$this->master = $master;
		
		$this->contribution 	= new tiFy_Forum_DbContribution( $this->master );
		$this->contributor 		= new tiFy_Forum_DbContributor( $this->master );
		$this->multi 			= new tiFy_Forum_DbMulti( $this->master );
		$this->topic			= new tiFy_Forum_DbTopic( $this->master );
	}
}

/* = CONTRIBUTION = */
class tiFy_Forum_DbContribution extends tiFy_Db{
	/* = ARGUMENTS = */
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */	
	function __construct( tiFy_Forum_Master $master ){
		// Déclaration de la classe de référence principale
		$this->master = $master;
			
		// Définition des arguments
		$this->install 		= true;
		$this->table 		= 'tify_forum_contribution';
		$this->col_prefix	= 'contrib_'; 
		$this->has_meta		= true;
		$this->cols			= array(
			'id'				=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true,
			),
			'topic_id'			=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'default'			=> 0
			),
			'author'			=> array(
				'type'				=> 'TINYTEXT'				
			),
			'author_email'		=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 100,
				'default'			=> ''
			),
			'author_url'		=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 200,
				'default'			=> ''
			),
			'author_IP'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 100,
				'default'			=> ''
			),
			'date'				=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),
			'date_gmt'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),
			'content'			=> array(
				'type'				=> 'TEXT',
				'default'			=> false				
			),
			'karma'				=> array(
				'type'				=> 'INT',
				'size'				=> 11,
				'default'			=> 0
			),
			'approved'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 20,
				'default'			=> 1
			),
			'agent'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 255,
				'default'			=> ''				
			),
			'type'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 20,
				'default'			=> ''				
			),
			'parent'		=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'default'			=> 0
			),
			'user_id'		=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'default'			=> 0,			
			)
		);
		
		parent::__construct();
	}
}

/* = CONTRIBUTION = */
class tiFy_Forum_DbContributor extends tiFy_Contest_DbUsers{
}

/* = FORUM MULTIPLES = */
class tiFy_Forum_DbMulti extends tiFy_Db{
	/* = ARGUMENTS = */
	private	// Référence
			$master;	
	
	/* = CONSTRUCTEUR = */	
	function __construct( tiFy_Forum_Master $master ){
		// Déclaration de la classe de référence principale
		$this->master = $master;
			
		// Définition des arguments
		$this->install 		= false;
		$this->table 		= 'tify_forum_multi';
		$this->col_prefix	= 'multi_'; 
		$this->has_meta		= false;
		$this->cols			= array();
		
		parent::__construct();	
	}
}

/* = SUJETS = */
class tiFy_Forum_DbTopic extends tiFy_Db{
	/* = ARGUMENTS = */
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */	
	function __construct( tiFy_Forum_Master $master ){
		// Déclaration de la classe de référence principale
		$this->master = $master;
			
		// Définition des arguments
		$this->install 		= true;
		$this->table 		= 'tify_forum_topic';
		$this->col_prefix	= 'topic_'; 
		$this->has_meta		= true;
		$this->cols			= array(
			'id'				=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true
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
			'content'			=> array(
				'type'				=> 'LONGTEXT',
				'search'			=> true
			),
			'title'				=> array(
				'type'				=> 'TEXT',				
				'search'			=> true
			),
			'excerpt'			=> array(
				'type'				=> 'TEXT',
				'search'			=> true
			),
			'status'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 20,
				'default'			=> 'publish'				
			),
			'contrib_status'	=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 20,
				'default'			=> 'open'
			),
			'password'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 20,
				'default'			=> ''				
			),
			'name'				=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 200,
				'default'			=> ''				
			),
			'modified'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),
			'modified_gmt'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),
			'parent'				=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'default'			=> 0
			),
			'menu_order'		=> array(
				'type'				=> 'INT',
				'size'				=> 11,
				'default'			=> 0			
			),
			'contrib_count'		=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'default'			=> 0
			)
		);
		
		parent::__construct();	
	}
}
