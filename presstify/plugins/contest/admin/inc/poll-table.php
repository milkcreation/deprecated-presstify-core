<?php
class tiFy_Contest_Poll_AdminListTable extends tiFy_AdminView_List_Table{
	/* = PARAMETRES = */
	private	// Référence
			$master,
			$main;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Contest_Master $master ){
		// Définition des classes de référence
		$this->master 	= $master;
		$this->main 	= $this->master->admin->poll;
		
		// Définition de la classe parente
       	parent::__construct( 
       		array(
	            'singular'  => 'tify_contest_poll',
	            'plural'    => 'tify_contest_polls',
	            'ajax'      => true,
	            'screen'	=> $this->master->admin->hookname['poll']
        	),
        	$this->master->db_poll 
		);
	}
	
	/* = CONFIGURATION = */
	/** == Définition des status == **/
	function set_status(){
		return 	array(
			'any'				=> array(
				'label'				=> __( 'Tous', 'tify' ),
				'query_args'		=> array( 'active' => -1 ),
				'count_query_args'	=> array( ),
				'current'			=> ( ! isset( $_REQUEST['active'] ) || ( $_REQUEST['active'] == -1 ) ) ? 'any' : ''
			), 
			'available' 		=> array(			
				'validate'			=> array( 
					'label'				=> __( 'Validé', 'tify' ),
					'query_args'		=> array( 'active' => 1 ),
					'count_query_args'	=> array( 'active' => 1 ),
					'current'			=> ( isset( $_REQUEST['active'] ) && ( $_REQUEST['active'] == 1 ) ) ? 'validate' : ''
				),
				'waiting'			=> array( 
					'label'				=> __( 'En attente', 'tify' ),
					'query_args'		=> array( 'active' => 0 ),
					'count_query_args'	=> array( 'active' => 0 ),
					'current'			=> ( isset( $_REQUEST['active'] ) && ( $_REQUEST['active'] == 0 ) ) ? 'waiting' : ''
				)
			)
		);
	}
	
	/** == Traitement de la requête de récupération des items == **/
	public function extra_parse_query_items(){
		$args = array();
				
		if( isset( $_REQUEST['active'] ) && $_REQUEST['active'] > -1 )
			$args['active'] = $_REQUEST['active'];
					
		return $args;
	} 
	
	/* = ORGANES DE NAVIGATION = */	
	/** == Filtrage avancé  == **/
	protected function extra_tablenav( $which ) {

	}
	
	/* = AFFICHAGE = */	
	/** == COLONNES == **/
	/*** === Liste des colonnes === ***/
	public function get_columns(){
		return array(
			'poll_user_email' 			=> __( 'Email', 'tify' ),
			'poll_date' 				=> __( 'Date', 'tify' ),
			'poll_part' 				=> __( 'Participation', 'tify' ),
			'poll_active' 				=> __( 'Validé', 'tify' ),	
			/*'ranking' 			=> __( 'Classement', 'tify' ),	
			'poll' 				=> __( 'Votes', 'tify' ),
			'part_details' 		=> __( 'Détails de la participation', 'tify' ),
			'user_id' 			=> __( 'Proposé par', 'tify' ),
			'social_actions' 	=> __( 'Partages', 'tify' )*/
		);
	}
	
	
	public function column_poll_part( $item ){
		return $this->master->db_participation->get_item_meta( $item->poll_part_id, 'recipe_name' );
	}

	/* = TRAITEMENT DES ACTIONS = */
	function process_bulk_action(){
		if( $this->current_action() ) :
			switch( $this->current_action() ) :
				case 'delete' :
					$item_id = (int) $_GET[$this->db->primary_key];			
					check_admin_referer( $this->actions_prefix .'_delete_'. $item_id );
					$this->db->delete_item( $item_id );
					$this->db->delete_item_metadatas( $item_id );
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
					$sendback = add_query_arg( 'message', 'deleted', $sendback );
	
					wp_redirect( $sendback );
					exit;
				break;				
				case 'trash' :
					$item_id = (int) $_GET[$this->db->primary_key];		
					check_admin_referer( $this->actions_prefix .'_trash_'. $item_id );
									
					// Récupération du statut original de la campagne et mise en cache
					if( $original_status = $this->db->get_item_var_by_id( $item_id, 'status' ) )
						$this->db->update_item_meta( $item_id, '_trash_meta_status', $original_status );
					
					// Modification du statut de la campagne
					$this->db->update_item( $item_id, array( 'part_status' => 'trash' ) );
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
					$sendback = add_query_arg( 'message', 'trashed', $sendback );
									
					wp_redirect( $sendback );
					exit;
				break;
				case 'untrash' :
					$item_id = (int) $_GET[$this->db->primary_key];			
					check_admin_referer( $this->actions_prefix .'_untrash_'. $item_id );
					
					// Récupération du statut original de la campagne et suppression du cache
					$original_status = ( $_original_status = $this->db->get_item_meta( $item_id, '_trash_meta_status', true ) ) ? $_original_status : 'draft';				
					if( $_original_status ) $this->db->delete_item_meta( $item_id, '_trash_meta_status' );
					
					// Récupération du statut de la campagne
					$this->db->update_item( $item_id, array( 'part_status' => $original_status ) );
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
					$sendback = add_query_arg( 'message', 'untrashed', $sendback );
	
					wp_redirect( $sendback );
					exit;
				break;
				case 'approved' :
					$item_id = (int) $_GET[$this->db->primary_key];			
					check_admin_referer( $this->actions_prefix .'_'. $this->current_action() .'_'. $item_id );
										
					// Mise à jour du statut
					$this->db->update_item( $item_id, array( 'part_status' => 'publish' ) );
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
					$sendback = add_query_arg( 'message', 'approved', $sendback );
	
					wp_redirect( $sendback );
					exit;
				break;
				case 'unapproved' :
					$item_id = (int) $_GET[$this->db->primary_key];			
					check_admin_referer( $this->actions_prefix .'_'. $this->current_action() .'_'. $item_id );
										
					// Mise à jour du statut
					$this->db->update_item( $item_id, array( 'part_status' => 'moderate' ) );
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
					$sendback = add_query_arg( 'message', 'unapproved', $sendback );
	
					wp_redirect( $sendback );
					exit;
				break;
				case 'update_ranking' :
					check_admin_referer( 'tify_contest_'. $this->current_action() .'_'. get_current_user_id() );

					$this->master->tasks->update_ranking();
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
					$sendback = add_query_arg( 'message', 'unapproved', $sendback );
	
					wp_redirect( $sendback );
					exit;
				break;
			endswitch;
		elseif ( ! empty( $_REQUEST['_wp_http_referer'] ) ) :
			wp_redirect( remove_query_arg( array('_wp_http_referer', '_wpnonce'), wp_unslash($_SERVER['REQUEST_URI']) ) );
	 		exit;
		endif;
	} 
}