<?php
/* = COMMENTAIRES = */
class tiFy_Contest_DbComments extends tiFy_Db{
	/* = CONSTRUCTEUR = */	
	function __construct( ){	
		// Définition des arguments
		$this->install		= false;
		$this->table 		= 'comments';
		$this->col_prefix	= 'comment_';
		$this->has_meta		= 'comment';
		
		$this->cols			= array(
			'ID'				=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true,
			),
			'post_ID'			=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'default'			=> 0
			),
			'author'			=> array(
				'type'				=> 'TINYTEXT',
				'default'			=> false
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
				/** @todo **/
				'prefix'		=> false				
			)
		);		
		parent::__construct();				
	}
}

/* = POSTS = */
class tiFy_Contest_DbPosts extends tiFy_Db{
	/* = CONSTRUCTEUR = */	
	function __construct( ){	
		// Définition des arguments
		$this->install		= false;
		$this->table 		= 'posts';
		$this->col_prefix	= 'post_';
		$this->has_meta		= 'post';
		
		$this->cols			= array(
			'ID'				=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true,
				/** @todo **/
				'prefix'		=> false
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
				'type'				=> 'LONGTEXT'
			),
			'title'				=> array(
				'type'				=> 'TEXT',				
				'search'			=> true
			),
			'excerpt'			=> array(
				'type'				=> 'TEXT'
			),
			'status'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 20,
				'default'			=> 'publish'				
			),
			'comment_status'	=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 20,
				'default'			=> 'open',
				/** @todo **/
				'prefix'		=> false
			),
			'ping_status'	=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 20,
				'default'			=> 'open',
				/** @todo **/
				'prefix'		=> false
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
			'to_ping'			=> array(
				'type'				=> 'TEXT',
				/** @todo **/
				'prefix'		=> false
			),
			'pinged'			=> array(
				'type'				=> 'TEXT',
				/** @todo **/
				'prefix'		=> false
			),
			'modified'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),
			'modified_gmt'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),
			'content_filtered'	=> array(
				'type'				=> 'LONGTEXT'
			),
			'parent'				=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'default'			=> 0
			),
			'guid'				=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 255,
				'default'			=> '',
				/** @todo **/
				'prefix'		=> false				
			),
			'menu_order'		=> array(
				'type'				=> 'INT',
				'size'				=> 11,
				'default'			=> 0,
				/** @todo **/
				'prefix'		=> false				
			),
			'type'				=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 20,
				'default'			=> 'post'				
			),
			'mime_type'				=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 100,
				'default'			=> ''				
			),
			'comment_count'		=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'default'			=> 0,
				/** @todo **/
				'prefix'		=> false
			),
		);		
		parent::__construct();				
	}
}

/* = UTILSATEURS = */
class tiFy_Contest_DbUsers extends tiFy_Db{
	/* = CONSTRUCTEUR = */	
	function __construct( ){	
		// Définition des arguments
		$this->install		= false;
		$this->table 		= 'users';
		$this->col_prefix	= 'user_';
		$this->has_meta		= 'user';
		
		$this->cols			= array(
			'ID'				=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true,
				/** @todo **/
				'prefix'		=> false
			),
			'login'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 60,
				'default'			=> ''
			),
			'pass'				=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 64,
				'default'			=> ''
			),
			'nicename'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 50,
				'default'			=> ''
			),
			'email'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 100,
				'default'			=> ''
			),
			'url'				=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 100,
				'default'			=> ''
			),
			'registred'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),
			'activation_key'	=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 60,
				'default'			=> ''			
			),
			'status'			=> array(
				'type'				=> 'INT',
				'size'				=> 11,
				'default'			=> 0
			),
			'display_name'		=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 250,
				'default'			=> '',
				/** @todo **/
				'prefix'		=> false
			)
		);		
		parent::__construct();				
	}
}