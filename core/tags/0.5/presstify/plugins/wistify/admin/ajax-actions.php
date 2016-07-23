<?php
class tiFy_Wistify_AjaxActions{
	/* = ARGUMENTS = */
	public	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Wistify_Master $master ){
		// Référence
		$this->master = $master;
		
		// ACTIONS ET FILTRES WORDPRESS
		/// Messages
		add_action( 'wp_ajax_wistify_messages_send', array( $this, 'messages_send' ) );
		/// Abonnés
		add_action( 'wp_ajax_wistify_autocomplete_recipients', array( $this, 'autocomplete_recipients' ) );
		/// Rapports 
		add_action( 'wp_ajax_wistify_report_update', array( $this, 'report_update' ) );
		/// Campagne
		add_action( 'wp_ajax_wistify_campaign_preview', array( $this, 'campaign_preview' ) );
		add_action( 'wp_ajax_wistify_campaign_prepare', array( $this, 'campaign_prepare' ) );
		add_action( 'wp_ajax_wistify_campaign_prepare_recipients_subscriber', array( $this, 'campaign_prepare_subscriber' ) );
		add_action( 'wp_ajax_wistify_campaign_prepare_recipients_mailing_list', array( $this, 'campaign_prepare_list' ) );
		add_action( 'wp_ajax_wistify_campaign_prepare_update_status', array( $this, 'campaign_prepare_status' ) ); 
	}
	
	/* = ACTIONS = */
	/** == MESSAGES == **/
	/*** === Envoi des messages === ***/
	public function messages_send(){
		check_ajax_referer( 'wistify_messages_send', '_wty_ajax_nonce' );
				
		$campaign_id 		= $_POST['campaign_id'];
		$to				 	= $_POST['recipient_email'];
		$args 				= $_POST['message'];
		
		$response = $this->master->tasks->mail( $campaign_id, $to, $args );
		
		( is_wp_error( $response ) ) ? wp_send_json_error( $response ) : wp_send_json_success( current( $response ) );
	}

	/** == ABONNES == **/
	/*** === Récupération de déstinataires par autocomplétion === ***/
	public function autocomplete_recipients(){
		$defaults = array(
			'term'				=> '',
			'elements'			=> array( 'label', 'type', 'type_label', 'ico', 'numbers' ),			
			'extras'			=> array(),
			'types'				=> array( 'subscriber', 'list' ),
			'name'				=> 'campaign_recipients'			
		);
		extract( $defaults );
		
		// Valeur de retour par défaut
		$response = array();
			
		// Traitement des arguments de requête
		if( isset( $_POST['term'] ) )
			$term = $_POST['term'];
		if( ! empty( $_POST['elements'] ) && is_array( $_POST['elements'] ) )
			 $elements = $_POST['elements'];
				
		// Recherche parmi les abonnés Wistify
		if( in_array( 'subscriber', $types ) ) :					
			if( $results = $this->master->db_subscriber->get_items( array( 'status' => 'registred', 'search' => $term ) ) ) :
				foreach ( (array) $results as $result ){
					// Données requises
					$label 				= $result->subscriber_email;
					$value 				= $result->subscriber_id;
					
					// Données de rendu
					$type 				= 'wystify_subscriber';
					$type_label			= __( 'Abonné', 'tify' ); 
					$ico 				= '<i class="fa fa-user"></i><i class="badge wisti-logo"></i>';
					
					// Génération du rendu
					$render				= "<a href=\"#\">". $this->autocomplete_recipients_item_render( compact( $elements ) ) ."</a>";
					
					// Génération de la selection	
					$selected 			= 	"<li data-numbers=\"1\">\n". 
											"\t". $this->autocomplete_recipients_item_render( compact( $elements ) ) ."\n".
											"\t<a href=\"\" class=\"tify_button_remove remove\"></a>\n".
											"\t<input type=\"hidden\" name=\"{$name}[{$type}][]\" value=\"{$value}\">\n".										
											"</li>\n";
												
					// Valeur de retour
					$response[] = compact( 'label', 'value', 'render', 'selected' );
				}
			endif;
		endif;
		
		// Recherche parmi les listes de diffusion Wistify
		if( in_array( 'list', $types ) ) :		
			if( $results = $this->master->db_list->get_items( array( 'search' => $term ) ) ) :
				foreach ( (array) $results as $result ){
					// Données requises
					$label 				= $result->list_title;
					$value 				= $result->list_id;
					
					// Données de rendu					
					$type 				= 'wystify_mailing_list';
					$type_label			= __( 'Liste de diffusion', 'tify' );					
					$ico 				= "<i class=\"fa fa-group\"></i><i class=\"badge wisti-logo\"></i>";
					$numbers 			= $this->master->db_subscriber->count_items( array( 'list_id' => $result->list_id, 'status' => 'registred', 'active' => 1 ) );
					
					// Génération du rendu
					$render			 	= "<a href=\"#\">". $this->autocomplete_recipients_item_render( compact( $elements ) ) ."</a>";
					
					// Génération de la selection
					$selected		 	= "<li data-numbers=\"{$numbers}\">\n". 
										  "\t". $this->autocomplete_recipients_item_render( compact( $elements ) ) ."\n".
										  "\t<a href=\"\" class=\"tify_button_remove remove\"></a>\n".
										  "\t<input type=\"hidden\" name=\"{$name}[{$type}][]\" value=\"{$value}\">\n".
										  "\t</li>\n";
										  
					// Valeur de retour
					$response[] = compact( 'label', 'value', 'render', 'selected' );
				}
			endif;
		endif;
		
		// Recherche parmi les utilisateurs Wordpress
		/*if( in_array( 'wordpress-user', $types ) ) :
			$user_query_args = array(
				'search'         => '*'. $_REQUEST['term'] .'*',
				'search_columns' => array( 'user_login', 'user_email', 'user_nicename' )
			);	
			$user_query = new WP_User_Query( $user_query_args ); 	
			if( $results = $user_query->get_results() ) :
				foreach ( (array) $results as $result ){
					$label				= $result->user_email;
					$type 				= 'wordpress_user';
					$type_label			= __( 'Utilisateur Wordpress', 'tify' ); 
					$value 				= $result->ID;
					$ico 				= '<i class="fa fa-user"></i><i class="badge dashicons dashicons-wordpress"></i>';
					$_render			= 	"<span class=\"ico\">{$ico}</span>\n". 
											"<span class=\"label\">{$label}</span>\n".
											"<span class=\"type\">{$type_label}</span>\n";
					$render['label'] 	= 	"<a href=\"\">". $_render ."</a>";	
					$render['value'] 	= 	"<li data-numbers=\"1\">\n". 
											"\t". $_render ."\n".
											"\t<a href=\"\" class=\"tify_button_remove remove\"></a>\n".
											"\t<input type=\"hidden\" name=\"{$_REQUEST['name']}[{$type}][]\" value=\"{$value}\">\n".
											"\t</li>\n";	
					$response[] = $render;
				}
			endif;
		endif;
		 * 
		// Recherche parmi les roles Wordpress
		if( in_array( 'wordpress-role', $types ) ) :
			$results = array();
			foreach( get_editable_roles() as $role => $value ) :
				if( preg_match( '/'. preg_quote( $_REQUEST['term'] ) .'/i', translate_user_role( $value['name'] ) ) ) :
				 	$results[$role] = translate_user_role( $value['name'] );	
				endif;
			endforeach;
					
			if( $results ) :
				foreach ( (array) $results as $role_id => $result ){
					
					$label				= $result;
					$type 				= 'wordpress_role';
					$type_label			= __( 'Groupe d\'utilisateurs Wordpress', 'tify' ); 
					$value 				= $role_id;
					$user_query 		= new WP_User_Query( array( 'role' => $role_id ) );
					$numbers			= $user_query->get_total();
					$ico 				= '<i class="fa fa-group"></i><i class="badge dashicons dashicons-wordpress"></i>';
					$_render			= 	"<span class=\"ico\">{$ico}</span>\n". 
											"<span class=\"label\">{$label}</span>\n".
											"<span class=\"type\">{$type_label}</span>\n".
											"<span class=\"numbers\">{$numbers}</span>\n";
					$render['label'] 	= 	"<a href=\"\">". $_render ."</a>";	
					$render['value'] 	= 	"<li data-numbers=\"{$numbers}\">\n". 
											"\t". $_render ."\n".
											"\t<a href=\"\" class=\"tify_button_remove remove\"></a>\n".
											"\t<input type=\"hidden\" name=\"{$_REQUEST['name']}[{$type}][]\" value=\"{$value}\">\n".
											"\t</li>\n";	
					$response[] = $render;
				}
			endif;
		endif;*/	
				
		wp_send_json( $response );
	}

	/** == Rendu d'un élément de l'autocomplétion des abonnés == **/
	private function autocomplete_recipients_item_render( $args = array() ){
		extract( $args );
		$output   = "";
		$output  .= "<span class=\"ico\">{$ico}</span>\n". 
					"<span class=\"label\">{$label}</span>\n".
					"<span class=\"type\">{$type_label}</span>\n";
		if( isset( $numbers ) )
			$output .= "<span class=\"numbers\">{$numbers}</span>\n";
		
		return $output;
	}
	
	/** == CAMPAGNES == **/
	/*** === Prévisualisation de la campagne === ***/
	private function campaign_preview(){
		if( isset( $_POST['campaign_id'] ) )
			return;
		
		check_ajax_referer( 'wistify_campaign_preview', '_wty_ajax_nonce' );
		check_admin_referer( 'wistify_campaign_preview', '_wty_ajax_nonce' );	
		
	}
	
	/*** === Lancement de la préparation de la campagne === ***/
	public function campaign_prepare(){
		$recipients = false;		
		$types = array();
		$count = array(); 
		$total = 0;
		
		// Récupération des variables
		$campaign_id = (int) $_REQUEST['campaign_id'];
		
		// Réattribution du status d'édition pour la campagne
		$this->master->db_campaign->update_status( $campaign_id, 'edit' );
		
		// Nettoyage des messages déja présent dans la file
		$this->master->db_queue->reset_campaign( $campaign_id );
		$this->master->db_campaign->set_prepare_log( $campaign_id );				
				
		// Compte le nombre d'emails à envoyer
		if( $recipients = $this->master->db_campaign->get_item_var_by_id( $campaign_id, 'recipients' ) ) :						
			/// Abonnés Wistify
			if( isset( $recipients['wystify_subscriber'] ) ) :
				$count['wystify_subscriber'] = 0;
				foreach( $recipients['wystify_subscriber'] as $subscriber_id ) :
					if( $this->master->db_subscriber->get_item_var_by_id( $subscriber_id, 'status' ) === 'registred' ) :
						$count['wystify_subscriber']++; 
						$total++;
					endif;
				endforeach;		
				if( $count['wystify_subscriber'] )
					array_push( $types, 'wystify_subscriber' );	
			endif;
			/// Listes de diffusion Wistify	
			if( isset( $recipients['wystify_mailing_list'] ) ) :
				$count['wystify_mailing_list'] = 0;				
				foreach( $recipients['wystify_mailing_list'] as $wml ) :					
					if( ! $c = $this->master->db_subscriber->count_items( array( 'list_id' => $wml, 'status' => 'registred', 'active' => 1 ) ) )
						continue;
					if( ! isset( $count['wystify_mailing_list'] ) )
						$count['wystify_mailing_list'] = 0;										 
					$count['wystify_mailing_list'] += $c; 
					$total += $c;
				endforeach;
				if( $count['wystify_mailing_list'] )
					array_push( $types, 'wystify_mailing_list' );				
			endif;				
		endif;
		
		// Mise à jour des logs
		$this->master->db_campaign->update_prepare_log( $campaign_id, array( 'total' => $total ) );

		$response = compact( 'recipients', 'types', 'count', 'total' );
				
		wp_send_json( $response );
	}

	/*** === Préparation des abonnés === ***/
	public function campaign_prepare_subscriber(){
		foreach( $_REQUEST['subscriber_ids'] as $subscriber_id )		
			$emails[] = $this->master->db_subscriber->get_item_var_by_id( $subscriber_id, 'email' );		
		$processed 	= count( $emails );		
		$errors 		= $this->prepare_sends( $emails, $_REQUEST['campaign_id'] );
		$unprocessed 	= 0;
		foreach( $errors as $error )
			$unprocessed += count( $error );	
		$enqueue 	= $processed - $unprocessed;
		
		// Mise à jour des logs
		$this->master->db_campaign->update_prepare_log( $_REQUEST['campaign_id'], $errors, true );
		
		$response = compact( 'emails', 'processed', 'errors', 'enqueue' );
					
		wp_send_json( $response );
	}
		
	/*** === Préparation des abonnés d'une liste de diffusion === ***/
	public function campaign_prepare_list(){
		$emails 		= $this->master->db_subscriber->get_items_col( 'email', array( 'list_id' => $_REQUEST['list_id'], 'status'=> 'registred', 'active' => 1, 'per_page' => $_REQUEST['per_page'], 'paged' => $_REQUEST['paged'] ) );
		$processed		= count( $emails );
		$errors 		= $this->prepare_sends( $emails, $_REQUEST['campaign_id'] );
		$unprocessed 	= 0;
		foreach( $errors as $error )
			$unprocessed += count( $error );	
		$enqueue 	= $processed - $unprocessed;
		
		// Mise à jour des logs
		$this->master->db_campaign->update_prepare_log( $_REQUEST['campaign_id'], $errors, true );
		
		$response = compact( 'emails', 'processed', 'errors', 'enqueue' );
		
		wp_send_json( $response );
	}
	
	/*** === Lancement de la préparation de la campagne === ***/
	public function campaign_prepare_status(){
		if( ! isset( $_POST['campaign_id'] ) )
			die(0);
		
		$campaign_id  	= (int) $_POST['campaign_id'];
		$enqueue		= (int) $_POST['enqueue'];
		
		// Mise à jour des logs
		$this->master->db_campaign->update_prepare_log( $_REQUEST['campaign_id'], array( 'enqueue' => $enqueue ), true );
			
		if( $this->master->db_queue->has_campaign( $campaign_id ) ) 
			if( $this->master->db_campaign->update_status( $campaign_id, 'ready' ) )
				wp_send_json_success();
		
		wp_send_json_error();
	}
	
	/** == Préparation des envois == **/
	private function prepare_sends( $emails, $campaign_id ){
		$errors = array();
				
		foreach( (array) $emails as $email ) :
			// Vérification de la validité du mail
			if( ! is_email( $email ) ) :
				$errors['invalid'][] = sprintf( __( '"%s" n\'est pas un email valide', 'tify' ), $email );
				continue;
			endif;
					
			// Vérification des doublons
			if( $queue = $this->master->db_queue->get_item( array( 'email' => esc_attr( $email ), 'campaign_id' => $campaign_id ) ) ) :
				$errors['duplicate'][] = $queue->queue_email;
			else :
				// Préparation du message			
				$message = $this->master->tasks->prepare_mail( $campaign_id, $email );	
				// Insertion dans la file d'attente
				$this->master->db_queue->insert_item( array( 'queue_email' => esc_attr( $email ), 'queue_campaign_id' => $campaign_id, 'queue_message' => $message ) );
			endif;
		endforeach;
		
		return $errors;
	}	
	
	/* = RAPPORTS = */
	/** == Mise à jour des informations de rapport == **/
	public function report_update(){
		if( ! $_POST['report_id'] )
			die(0);
		$report_id = $_POST['report_id'];
			
		check_ajax_referer( "wistify_report_update-". $report_id, '_ajax_nonce' );
		
		// Mise à jour du rapport
		$this->master->tasks->update_report( $report_id, true );
		
		// Chargement du gestionnaire de vue
		tify_require( 'admin_view' );		
		require_once( $this->master->admin->dir .'/inc/report-table.php' );		
		$this->table = new tiFy_Wistify_Report_AdminTable( $this->master );
		
		$report = $this->master->db_report->get_item_by_id( $report_id );
		
		// Retourne la ligne du rapport
		echo $this->table->single_row( $report );
		exit;				
	}	
}