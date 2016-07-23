<?php
class tiFy_Forum_AdminListContribution extends tiFy_AdminView_List_Table{
	/* = PARAMETRES = */
	private	// Référence
			$master,
			$main;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Forum_Master $master ){
		// Définition des classes de référence
		$this->master 	= $master;
		$this->main 	= $this->master->admin->contribution;
		
		// Définition de la classe parente
       	parent::__construct( 
       		array(
	            'singular'  => 'tify_contest_contribution',
	            'plural'    => 'tify_contest_contributions',
	            'ajax'      => true,
	            'screen'	=> $this->master->hookname['contribution']
        	),
        	$this->master->db->contribution
		);
	}
	
	/* = CONFIGURATION = */
	
	/* = ORGANES DE NAVIGATION = */	
	
	/* = AFFICHAGE = */	
	/** == COLONNES == **/
	/*** === Liste des colonnes === ***/
	public function get_columns(){
		return array(
			'contrib_author'	=> __( 'Auteur', 'tify' ),
			'contrib_content'	=> __( 'Contribution', 'tify' ),
			'contrib_topic_id'	=> __( 'En réponse à', 'tify' )
		);
	}
	
	/* = TRAITEMENT DES ACTIONS = */
	function process_bulk_action(){
		if( $this->current_action() ) :
			switch( $this->current_action() ) :
				case 'delete' :
					$item_id = (int) $_GET[$this->db->primary_key];			
					check_admin_referer( $this->actions_prefix . $this->current_action() . $item_id );
					$this->db->delete_item( $item_id );
					$this->db->delete_item_metadatas( $item_id );
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
					$sendback = add_query_arg( 'message', 'deleted', $sendback );
	
					wp_redirect( $sendback );
					exit;
				break;				
				case 'trash' :
					$item_id = (int) $_GET[$this->db->primary_key];		
					check_admin_referer( $this->actions_prefix . $this->current_action() . $item_id );
									
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
					check_admin_referer( $this->actions_prefix . $this->current_action() . $item_id );
					
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
			endswitch;
		elseif ( ! empty( $_REQUEST['_wp_http_referer'] ) ) :
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
	 		exit;
		endif;
	} 
}