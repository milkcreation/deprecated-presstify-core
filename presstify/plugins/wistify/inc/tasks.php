<?php
class tiFy_Wistify_Tasks{
	/* = ARGUMENTS = */
	public	// Configuration
			$shedules = array();
					
	private	// Références
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Wistify_Master $master ){
		// Déclaration des Références
		$this->master = $master;			
		
		// Configuration
		/// Définition des tâches planifiées
		$this->shedules = array( 
			'tify_wistify_queue_handle'		=> array(
				'title'			=> __( 'Traitement de la file de mail', 'tify' ),
				'timestamp'		=> mktime( date("H")-1, 0, 0, date("m"), date("d"), date("Y") ),
				'recurrance' 	=> 'hourly',
				'action'		=> array( $this, 'shedule_queue_handle' )
			), 
			'tify_wistify_report_update'	=> array(
				'title'			=> __( 'Mise à jour des rapports d\'envoi', 'tify' ),
				'timestamp'		=> mktime( 4, 0, 0, date("m"), date("d")+1, date("Y") ),
				'recurrance' 	=> 'twicedaily',
				'action'		=> array( $this, 'shedule_report_update' )
			),  
			'tify_wistify_report_archive'		=> array(
				'title'			=> __( 'Archivage des rapports d\'envoi', 'tify' ),
				'timestamp'		=> mktime( 2, 0, 0, date("m"), date("d")+1, date("Y") ),
				'recurrance' 	=> 'daily',
				'action'		=> array( $this, 'shedule_report_archive' )
			) 
		);
				
		// Plannification des tâches
		foreach( $this->shedules as $hook => $args ) :
			if( ! wp_get_schedule( $hook ) )
				wp_schedule_event( $args['timestamp'], $args['recurrance'], $hook );
			add_action( $hook, $args['action'] );
		endforeach;	
			
		// Actions et Filtres Wordpress	
		add_action( 'init', array( $this, 'wp_init' ) );	
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation de l'interface d'administration == **/
	public function wp_init(){
		if( defined( 'DOING_CRON') && DOING_CRON === true && isset( $_REQUEST['wistify_queue_handle'] ) )
			$this->shedule_queue_handle();
		if( defined( 'DOING_CRON') && DOING_CRON === true && isset( $_REQUEST['wistify_report_update'] ) )
			$this->shedule_report_update();
		if( defined( 'DOING_CRON') && DOING_CRON === true && isset( $_REQUEST['wistify_report_archive'] ) )
			$this->shedule_report_archive();
	}
		
	/* = TACHES PLANIFIEES = */
	/** == Traitement de la file des mails == **/
	public function shedule_queue_handle(){
		global $wpdb;
		
		// Récupération de la temporisation de traitement		
		$prev_time 			= date( 'U', mktime( date('H')-1, date('i'), date('s'), date('m'), date('d'), date('Y') ) );
		$start_time 		= current_time( 'timestamp' );
		$end_time			= date( 'U', mktime( date('H')+1, date('i'), date('s'), date('m'), date('d'), date('Y') ) );

		// Vérifie s'il existe des campagnes à envoyer
		if( ! $campaign_ids = $wpdb->get_col( $wpdb->prepare( "SELECT campaign_id FROM {$wpdb->wistify_campaign} WHERE UNIX_TIMESTAMP(campaign_send_datetime) <= %d AND campaign_status = 'send' ORDER BY campaign_send_datetime", $start_time ) ) )
			return;

		// Récupération du quota d'envoi par heure
		$info = $this->master->Mandrill->result( 'users', 'info', array() );
		if( is_wp_error( $info ) )
			return;
		/// Vérifie si la capacité d'envoi est suffisante
		if( $info['hourly_quota'] <= 0 )
			return;		
		$md_hourly_quota = $info['hourly_quota'];
		
		// Récupération de la quantité de emails envoyés depuis une heure
		$mandrill = new Mandrill( $this->master->get_mandrill_api_key() );
		$search = $mandrill->messages->searchTimeSeries( "ts:[$prev_time $start_time]" );
		if( is_wp_error( $search ) )
			return;
		$search = current( $search );
		$md_hourly_send = empty( $search )? 0 : $search['sent'];
		/// Vérifie si les envois effectués sont inférieurs au quota autorisé
		if( $md_hourly_send >= $md_hourly_quota )
			return;
		
		// Définition du nombre d'envoi maximum
		$max = $md_hourly_quota - $md_hourly_send;
		// Définition du nombre d'envoi effectués
		$count = 0;
		
		foreach( $campaign_ids as $campaign_id ) :
			// Le nombre d'envoi maximum est atteint
			if( $count >= $max )
				return;
			
			// Vérifie s'il y a encore des messages en attente d'acheminement dans la file
			if( 0 === $this->master->db_queue->count_items( array( 'campaign_id' => $campaign_id ) ) ) :
				$this->master->db_campaign->update_status( $campaign_id, 'forwarded' );
				continue;
			endif;	
							
			// Récupération des messages
			for( $count; $count <= $max; $count++ ) :
				// La fin de la tâche est atteinte
				if( time() >= ( $end_time - 60 ) )
					return;
				
				// Vérifie s'il existe encore des messages à envoyer
				if( ! $q = $this->master->db_queue->get_item( array( 'campaign_id' => $campaign_id, 'locked' => 0, 'orderby' => 'id', 'order' => 'ASC' ) ) )
					break;
				
				// Verrouillage du message à envoyer
				$this->master->db_queue->update_item( $q->queue_id, array( 'locked' => 1 ) );	
				
				// Tentative d'envoi du message
				$result = $this->master->Mandrill->result( 'messages', 'send', array( $q->queue_message ) );
				if( is_wp_error( $result ) ) :
					$this->master->db_queue->update_item( $q->queue_id, array( 'locked' => 0 ) );
					continue;
				endif;
				$resp = current( $result );

				// Sauvegarde du rapport
				$this->master->db_report->insert_item( 
					array(
						'report_campaign_id'		=> $campaign_id,
						'report_posted_ts'			=> current_time( 'timestamp' ),
						'report_md__id'				=> $resp['_id'],
						'report_md_sender'			=> $q->queue_message['from_email'],
						'report_md_subject'			=> $q->queue_message['subject'],
						'report_md_email'			=> $resp['email'],
						'report_md_state'			=> 'posted',
						'report_md_reject_reason'	=> $resp['reject_reason'],
					)
				);
				// Suppression du message de la file
				$this->master->db_queue->delete_item( $q->queue_id );
			endfor;

			// Vérifie s'il y a encore des messages en attente d'acheminement dans la file
			if( 0 === $this->master->db_queue->count_items( array( 'campaign_id' => $campaign_id ) ) ) :
				$this->master->db_campaign->update_status( $campaign_id, 'forwarded' );
				continue;
			endif; 
		endforeach;
	}
		
	/** == Mise à jour des rapports d'acheminement == **/
	public function shedule_report_update(){
		global $wpdb;
		
		$query = "SELECT report_id FROM {$wpdb->wistify_report} WHERE 1 AND report_posted_ts < %d ORDER BY FIELD(report_md_state,'posted') DESC, report_updated_ts ASC, report_posted_ts ASC";
		$report_ids = $wpdb->get_col( $wpdb->prepare( $query, time() - HOUR_IN_SECONDS ) );

		foreach( (array) $report_ids as $report_id ) :
			$this->update_report( $report_id, true );
		endforeach;
	}
	
	/** == Archivage des rapports d'acheminement == **/
	public function shedule_report_archive(){		
		global $wpdb;
		require_once( ABSPATH .'wp-admin/install-helper.php' );
		
		$expired = current_time( 'timestamp' ) - (30 * DAY_IN_SECONDS );
		$report_ids = $wpdb->get_col( $wpdb->prepare( "SELECT report_id FROM {$wpdb->wistify_report} WHERE report_posted_ts < %d ORDER BY report_posted_ts ASC", $expired ) );	
	
		foreach( $report_ids as $key => $report_id ) :
			$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->wistify_report} WHERE report_id = %d", $report_id ), ARRAY_A );
			$table_name = $wpdb->prefix. 'wistify_report_archives'. date( 'Y', $data['report_posted_ts'] ) . date( 'm', $data['report_posted_ts'] );
			maybe_create_table( $table_name, $this->report_archive_create_dll( $table_name ) );
			unset( $data['report_id'] );			
			if( $wpdb->insert( $table_name, $data ) )
				$wpdb->delete( $wpdb->wistify_report, array( 'report_id' => $report_id ) );
		endforeach;	
		
	}
	
	/* = CONTROLEUR = */
	/** == Envoi d'un email == **/
	public function mail( $campaign_id, $to, $args = array( ) ){
		if( ! $campaign = $this->master->db_campaign->get_item_by_id( $campaign_id ) )
			return;		
		
		$message = $this->prepare_mail( $campaign_id, $to, $args );

		return $this->master->Mandrill->result( 'messages', 'send', array( $message ) );
	}
	
	/** == Préparation du message == **/
	public function prepare_mail( $campaign_id, $to, $args = array() ){
		$campaign = $this->master->db_campaign->get_item_by_id( $campaign_id );
		
		// Pré-traitement
		$message = $campaign->campaign_message_options;
		$message['html'] = $campaign->campaign_content_html;  
		$message = wp_parse_args( $args, $message );
		
		// Formatage du sujet de message
		$message['subject'] = wp_unslash( $message['subject'] );
				
		// Traitement du contenu du message						 
		tify_require( 'mailer' );
		$mailer = new tiFy_Mailer;
		$mailer->prepare( 
			array( 
				'to'				=> 	$to,
				'subject' 			=> 	$message['subject'],				
				'html_head'			=>	$this->master->templates->html_head( $campaign_id ),
				'html_body_attrs'	=> 	$this->master->templates->html_body_attrs( $campaign_id ),
				"html" 				=> 	$this->master->templates->html_content( $campaign_id )																 
			) 
		);		
		$message['html'] = $mailer->get_html();					
		$message['text'] = $mailer->get_text();
		
		// Traitement du destinataire
		$message['to'][0]['email'] = $to;
						
		// Traitement des variables
		$vars = array();
		$vars['c'] = $campaign->campaign_uid;		
			
		if( ! empty( $_POST['service_account'] ) ) :
			$vars['u'] = $_POST['service_account'];
			set_transient( 'wty_account_'. $vars['u'], $to, HOUR_IN_SECONDS );
		elseif( $u = $this->master->db_subscriber->get_item_by( 'email', $to ) ) :
			$vars['u'] = $u->subscriber_uid;
		endif;
		
		// Variables globales		
		$message['global_merge_vars'] = array(
			array(
				'name' 		=> 'ARCHIVE',
				'content'	=> add_query_arg( array( 'u' => $vars['u'], 'c' => $vars['c'] ), home_url( '/wistify/archive' ) )
			),
			array(
				'name' 		=> 'UNSUB',
				'content'	=> add_query_arg( array( 'u' => $vars['u'], 'c' => $vars['c'] ), home_url( '/wistify/unsubscribe' ) )
			)
		);	
		
		// Mots-clefs	
		$message['tags'] = array( $campaign->campaign_uid, tify_excerpt( $campaign->campaign_title, array( 'max' => 45, 'teaser' => false ) ) );
			
		/// Convertion des valeurs boléennes
		foreach( $message as $k => &$v )
			if( in_array( $k, array( 'important', 'track_opens', 'track_clicks', 'auto_text', 'auto_html', 'inline_css', 'url_strip_qs', 'preserve_recipients', 'view_content_link', 'merge' ) ) )
				$v = filter_var( $v, FILTER_VALIDATE_BOOLEAN );
			
		return $message;
	}
	
	/** == Création de la table des archives mensuel des rapports == **/
	private function report_archive_create_dll( $table_name ){
		global $wpdb;
		
		$charset_collate = '';
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";
		
		return "CREATE TABLE IF NOT EXISTS `{$table_name}` (
			`report_id` bigint(20) unsigned NOT NULL auto_increment,
			`report_campaign_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			`report_posted_ts` int(13) NOT NULL DEFAULT '0',
			`report_updated_ts` int(13) NOT NULL DEFAULT '0',
			`report_md_ts` int(13) NOT NULL DEFAULT '0',
			`report_md__id` varchar(32) NOT NULL,
			`report_md_sender` varchar(255) NOT NULL,
			`report_md_template` varchar(255) NOT NULL,
			`report_md_subject` varchar(255) NOT NULL,
			`report_md_email` varchar(255) NOT NULL,
			`report_md_tags` longtext NOT NULL,
			`report_md_opens` int(5) NOT NULL,
			`report_md_opens_detail` longtext NOT NULL,
			`report_md_clicks` int(5) NOT NULL,
			`report_md_clicks_detail` longtext NOT NULL,
			`report_md_state` varchar(25) NOT NULL,
			`report_md_metadata` longtext NOT NULL,
			`report_md_smtp_events` longtext NOT NULL,
			`report_md_resends` longtext NOT NULL,
			`report_md_reject_reason` longtext NOT NULL,
			PRIMARY KEY  (`report_id`)
		) $charset_collate;\n";		
	}
	
	/** == Mise à jour des informations de rapport d'un message == **/
	public function update_report( $report_id, $resolve = false ){
		if( ! $id = $this->master->db_report->get_item_var_by_id( $report_id, 'md__id' ) )
			return false;
				
		$info = $this->master->Mandrill->result( 'messages', 'info', array( $id ) );	
		
		$args = array( 'ts', 'sender', 'template', 'subject', 'email', 'tags', 'opens', 'opens_detail', 'clicks', 'clicks_detail', 'state', 'metadata', 'smtp_events', 'resends' );
		if( ! is_wp_error( $info ) ) :			
			$data = array(
				'report_updated_ts'	=> current_time( 'timestamp' )
			);
			foreach( $args as $arg ) :
				if( isset( $info[$arg] ) )
					$data['report_md_'. $arg] = $info[$arg];		
			endforeach;
		elseif( $resolve && ( $info = $this->resolve_report( $report_id ) ) ) :
			$data = array(
				'report_updated_ts'	=> current_time( 'timestamp' )
			);
			foreach( $args as $arg ) :
				if( isset( $info[$arg] ) )
					$data['report_md_'. $arg] = $info[$arg];
			endforeach;
		else :
			$data = array(
				'report_updated_ts'	=> current_time( 'timestamp' ),
				'report_md_state' 	=> 'unknown'
			);				
		endif;	

		return $this->master->db_report->update_item( $report_id, $data );
	}

	/** == Resolution des rapport inconnus == 
	 * @see https://mandrill.zendesk.com/hc/en-us/articles/205583137-How-do-I-search-my-outbound-activity-in-Mandrill- 
	 **/
	public function resolve_report( $report_id ){		
		if( ! $report = $this->master->db_report->get_item_by_id( $report_id ) )
			return false;

		$result = $this->master->Mandrill->result( 'messages', 'search', array( "full_email:{$report->report_md_email} AND subject:{$report->report_md_subject}" ) );
		if( ! is_wp_error( $result ) && ( count( $result ) === 1 ) )
			return $result[0];
		
		return false;
	}
}