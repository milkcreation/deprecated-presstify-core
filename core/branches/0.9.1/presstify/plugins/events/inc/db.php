<?php
class tiFy_EventsDb extends tiFy_Db{
	/* = ARGUMENTS = */
	private $master;
	/* = CONSTRUCTEUR = */	
	function __construct( tiFy_Events $master ){
		$this->master = $master;		
		
		// Définition des arguments
		$this->table 		= 'tify_events';
		$this->col_prefix	= 'event_'; 
		$this->has_meta		= true;
		$this->cols			= array(
			'id' 			=> array(
				'type'				=> 'BIGINT',
				'size'				=> 20,
				'unsigned'			=> true,
				'auto_increment'	=> true
			),
			'post_id' 			=> array(
				'type'			=> 'BIGINT',
				'size'			=> 20,
				'unsigned'		=> true,
				'key'			=> 'event_post_id'
			),
			'start_datetime'		=> array(
				'type'			=> 'DATETIME',
				'default'		=> '0000-00-00 00:00:00'
			),
			'end_datetime'		=> array(
				'type'			=> 'DATETIME',
				'default'		=> '0000-00-00 00:00:00'
			)
		);		
		parent::__construct();				
	}	
}