<?php
/* = PARTICIPANTS = */
class tiFy_Contest_ParticipantDb{
	private // Référence
			$master;

	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Contest_Master $master ){
		// Déclaration de la classe de référence
		$this->master = $master;
	}
	
	/** == Vérifie si l'utilisateur courant à un compte accès pro. == */
	public function has_account( $user_id = 0 ){
		if( ! $user_id )
			$user_id = get_current_user_id();
		if( ! $user_id )
			return false;
		if( in_array( get_user_role( $user_id ), array_keys( $this->master->roles ) )  )
			return true;
		
		return false;
	}
}

/* = PARTICIPATIONS = */
class tiFy_Contest_ParticipationDb extends tiFy_Db{
	/* = ARGUMENTS = */
	public	$install = true;
	
	private // Référence
			$master;

	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Contest_Master $master ){
		// Déclaration de la classe de référence
		$this->master = $master;		
			
		// Définition des arguments
		$this->table 		= 'tify_contest_part';
		$this->col_prefix	= 'part_';
		$this->has_meta		= true;
		$this->cols			= array(
			'id'			=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true
			),
			'contest_id'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 255,
			),
			'session'	=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 32
			),
			'user_id'			=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 255,
			),
			'status'		=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 20,
				'default'			=> 'moderate',
				
				'any'				=> array( 'publish', 'moderate' )
			),
			'date'			=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			)
		);
		
		parent::__construct();				
	}
	
	/* = ALIAS = */
	/** == Compte le nombre de participation à un jeu concours == **/
	public function count( $contest_id = '', $args = array() ){
		if( ! $this->master->is_registred( $contest_id ) )
			return 0;
		
		$defaults = array(
		
		);
		$args = wp_parse_args( $args, $defaults );
		$args['contest_id'] = $contest_id;
		
		return $this->count_items( $args );		
	}
	
	/** == Récupére une participation à un jeu concours == **/
	public function get_part( $part_id = 0 ){
		return $this->get_item_by_id( $part_id );		
	}
	
	/** == Récupére le jeu concours relatif à une participation == **/
	public function get_part_contest( $part_id = 0 ){
		return $this->get_item_var_by_id( $part_id, 'contest_id' );		
	}
	
	/** == Récupére une metadonnée de participation à un jeu concours == **/
	public function get_part_meta( $part_id = 0, $meta_key, $single = true ){
		return $this->get_item_meta( $part_id, $meta_key, $single );		
	}
}

class tiFy_Contest_PollDb extends tiFy_Db{
	/* = ARGUMENTS = */
	public	$install = true;

	private // Référence
			$master;

	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Contest_Master $master ){
		// Déclaration de la classe de référence
		$this->master = $master;
			
		// Définition des arguments
		$this->table 		= 'tify_contest_poll';
		$this->col_prefix	= 'poll_';
		$this->cols			= array(
			'id'				=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true
			),
			'part_id'			=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'default'			=> 0
			),
			'date'				=> array(
				'type'				=> 'DATETIME',
				'default'			=> '0000-00-00 00:00:00'
			),
			'user_id'			=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'default'			=> 0
			),
			'user_remote_addr'	=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 255,
			),
			'user_email'		=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 255,
				'default'			=> null,
				
				'search'			=> true
			),
			'http_referer'		=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 255,
				'default'			=> null
			),
			'activation_key'	=> array(
				'type'				=> 'VARCHAR',
				'size'				=> 64,
				'default'			=> null
			),
			'active'			=> array(
				'type'				=> 'TINYINT',
				'size'				=> 1,
				'default'			=> 0
			)
		);
		
		parent::__construct();				
	}	
}

class tiFy_Contest_RankingDb extends tiFy_Db{
	/* = ARGUMENTS = */
	public	$install = true;

	private // Référence
			$master;

	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Contest_Master $master ){
		// Déclaration de la classe de référence
		$this->master = $master;
		
		// Définition des arguments
		$this->table 		= 'tify_contest_ranking';
		$this->col_prefix	= 'ranking_';
		$this->cols			= array(
			'id'				=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true
			),
			'part_id'			=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'default'			=> 0
			),
			'current_pos'		=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'default'			=> 0
			),
			'current_count'		=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'default'			=> 0
			),
			'current_ts'			=> array(
				'type'				=> 'INT',
				'size'				=> 12,
				'default'			=> null
			),
			'previous_pos'		=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'default'			=> 0
			),
			'previous_count'		=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'default'			=> 0
			),
			'previous_ts'		=> array(
				'type'				=> 'INT',
				'size'				=> 12,
				'default'			=> null
			)
		);
		
		parent::__construct();				
	}	
}