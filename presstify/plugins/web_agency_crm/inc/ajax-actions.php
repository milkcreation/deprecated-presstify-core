<?php
class tiFy_WebAgencyCRM_MainAjaxActions{
	/* = ARGUMENTS = */
	public	// Configuration
			$shedules = array();
					
	private	// Références
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_WebAgencyCRM_Master $master ){
		// Déclaration des Références
		$this->master = $master;
		
		add_action( 'wp_ajax_web_agency_crm_get_tasks', array( $this, 'ajax_get_tasks' ) );
		add_action( 'wp_ajax_nopriv_web_agency_crm_get_tasks', array( $this, 'ajax_get_tasks' ) );
	}
	
	public function ajax_get_tasks(){		
		$s				= new DateTime( $_POST['start'] );
		$start_datetime = $s->format('Y-m-d 00:00:00');
		$e				= new DateTime( $_POST['end'] );
		$end_datetime 	= $e->format('Y-m-d 23:59:59');
		
		global $wpdb;
		
		$res = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT task_start_datetime as start, task_end_datetime as end, task_title as title, task_id as id, task_employee as employee".
				" FROM {$wpdb->tify_wacrm_task}".
				" WHERE task_start_datetime >= %s AND task_start_datetime <= %s",
				$start_datetime,
				$end_datetime
			)
		);	
		
		wp_send_json( $res );
	}
}