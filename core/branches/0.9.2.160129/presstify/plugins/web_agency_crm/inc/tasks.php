<?php
class tiFy_WebAgencyCRM_MainTasks{
	/* = ARGUMENTS = */
	public	// Configuration
			$shedules = array();
					
	private	// Références
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_WebAgencyCRM_Master $master ){
		// Déclaration des Références
		$this->master = $master;
		
		//add_action( 'admin_init', array( $this, 'wp_admin_init' ), 99 );
	}
	
	public function _del_import_project(){	
		$upload_dir = wp_upload_dir();
		$db = $this->master->db->project;
		
		$output = "";		
		if ( ( $handle = fopen( $upload_dir['basedir'] . "/projets.csv", "r" ) ) !== FALSE ) :
			$row = 0; $column_headers = array();
		    while ( ( ( $data = fgetcsv( $handle ) ) !== FALSE ) ) :
				if( ! $row ++ )
					continue;
				if( $db->get_item_by( 'ref', $data[0] ) )
					continue;
				
				$args = array( 
					'ref'		=> $data[0],
					'title'		=> $data[1],
					'date'		=> current_time('mysql', false ),
					'date_gmt'	=> current_time('mysql', true )
				);
				
				$db->insert_item( $args );

			endwhile;
		    fclose($handle);
		endif;
		exit;
	}
	
	public function wp_admin_init(){	
		$upload_dir = wp_upload_dir();
		$db_task 	= $this->master->db->task;
		$db_project = $this->master->db->project;
		
		$output = "";		
		if ( ( $handle = fopen( $upload_dir['basedir'] . "/task-jordy.csv", "r" ) ) !== FALSE ) :
			$row = 0; $column_headers = array();
		    while ( ( ( $data = fgetcsv( $handle ) ) !== FALSE ) ) :
				if( ! $row ++ )
					continue;
				$project_id = ( $p = $db_project->get_item_by( 'ref', $data[4] ) )? (int) $p->project_id : 0;
				if( ! $project_id )
					echo '<div style="color:red;">Introuvable</div>';
	
				list( $d, $m, $Y ) = preg_split( '/\//', $data[0] );
				$args = array(
					'id'				=> 0,
					'project_id'		=> $project_id,
					'employee'			=> 31,
					'author'			=> 1,
					'start_datetime'	=> $Y .'-'. $m .'-'. $d .' '. $data[1] .':00',
					'end_datetime'		=> $Y .'-'. $m .'-'. $d .' '. $data[2] .':00',
					'title'				=> $data[5],
					'date'				=> current_time('mysql', false ),
					'date_gmt'			=> current_time('mysql', true )
				);
				$db_task->insert_item( $args );

			endwhile;
		    fclose($handle);
		endif;
		exit;
	}
}