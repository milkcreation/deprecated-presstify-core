<?php
class tiFy_Wistify_List_AdminTable extends tiFy_AdminView_List_Table {
	/* = ARGUMENTS = */	
	private	// Référence
			$master,
			$main;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_Wistify_Master $master ){
		// Définition des classe de référence
		$this->master 	= $master;
		$this->main 	= $this->master->admin->list;	
			
		// Définition de la classe parente
       	parent::__construct( 
       		array(
	            'singular'  => 'tify_wistify_list',
	            'plural'    => 'tify_wistify_lists',
	            'ajax'      => true,
	            'screen'	=> $this->master->hookname['list']
        	), 
        	$this->master->db_list 
		);
		
		// Paramétrage
		/// Environnement
		$this->base_link = add_query_arg( array( 'page' => $this->master->menu_slug['list'] ), admin_url( '/admin.php' ) );
		$this->edit_base_link = add_query_arg( array( 'page' => $this->master->menu_slug['list'], 'view' => 'edit_form' ), admin_url( '/admin.php' ) ); 
		
		/// Notifications
		$this->notifications = array(
			'deleted' 				=> array(
				'message'		=> __( 'La liste de diffusion a été défintivement supprimée', 'tify' ),
				'type'			=> 'success'
			),
			'trashed' 				=> array(
				'message'		=> __( 'La liste de diffusion a été placée dans la corbeille', 'tify' ),
				'type'			=> 'success'
			),
			'untrashed' 				=> array(
				'message'		=> __( 'La liste de diffusion a été rétablie', 'tify' ),
				'type'			=> 'success'
			)
		);
		
		/// Vue Filtrée
		$status = ! empty( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'any';
		$public = isset( $_REQUEST['public'] ) ? ( ! empty( $_REQUEST['public'] ) ? true : false ) : null;
		$this->views = array(
			'any'	=> array(
				'label'					=> __( 'Toutes (hors corbeille)', 'tify' ),
				'current'				=> ( $status === 'any' && is_null( $public ) ) ? true : false,
				'remove_query_args'		=> array( 'status', 'public'),
				'count_query_args'		=> array( 'status' => 'any' )
			),				
			'public'		=> array(
				'label'				=> __( 'Publique', 'tify' ),
				'current'			=> ( ( $status === 'any' ) && ! is_null( $public ) && $public ) ? true : false,
				'add_query_args'	=> array( 'status' => 'any', 'public' => 1 ),
				'count_query_args'	=> array( 'status' => 'any', 'public' => 1 ),
				
			),
			'private'		=> array(
				'label'				=> __( 'Privée', 'tify' ),
				'current'			=> ( ( $status === 'any' ) && ! is_null( $public ) && ! $public ) ? true : false,
				'add_query_args'	=> array( 'status' => 'any', 'public' => 0 ),
				'count_query_args'	=> array( 'status' => 'any', 'public' => 0 ),
				
			),
			'trash' 	=>  array(
				'label'				=> __( 'Corbeille', 'tify' ),
				'current'			=> ( $status === 'trash' ) ? true : false,
				'add_query_args'	=> array( 'status' => 'trash' ),
				'count_query_args'	=> array( 'status' => 'trash' )
			)
		);
		
		/// Arguments de récupération des éléments
		$this->prepare_query_args = array(
			'status' 	=> ! empty( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'any'
		);
	}
					
	/* = COLONNES = */
	/** == Définition des colonnes == **/
	public function get_columns() {
		$c = array(
			'cb'       				=> '<input type="checkbox" />',
			'list_title' 			=> __( 'Intitulé', 'tify' ),
			'list_content'  		=> __( 'Description', 'tify' ),
			'subscribers_number'    => __( 'Nombre d\'abonnés', 'tify' ),
			'list_date' 			=> __( 'Date de création', 'tify' ),
			'list_public' 			=> __( 'Droit d\'accès', 'tify' )
		);	
		return $c;
	}
	
	/** == Définition de l'ordonnancement par colonne == **/
	public function get_sortable_columns() {
		return array(	
			'list_title'  => 'title'
		);
	}	

	/** == Contenu personnalisé : Titre == **/
	public function column_list_title( $item ){
		$title = ! $item->list_title ? __( '(Pas de titre)', 'tify' ) : $item->list_title;
		
		if( $item->list_status !== 'trash' )
			$actions = $this->default_single_actions( $item, array( 'edit', 'trash' ) );
		else
			$actions = $this->default_single_actions( $item, array( 'untrash', 'delete' ) );
			
		$status = ( ! in_array( $item->list_status, array( 'publish', 'auto-draft' ) ) && ( $this->current_view() === 'any' ) ) ? "<span> - ". $this->views[$item->list_status]['label'] ."</span>" : false;
		
		return sprintf('<strong><a href="%2$s">%1$s</a>%3$s</strong>%4$s', $title, $this->get_item_edit_link( $item ), $status, $this->row_actions( $actions ) );	
	}

	/** == Contenu personnalisé : Nombre d'abonnés == **/
	public function column_subscribers_number( $item ){
		$registred 		= (int) $this->master->db_subscriber->count_items( array( 'list_id' => $item->list_id, 'status' => 'registred', 'active' => 1 ) );
		$unsubscribed 	= (int) $this->master->db_subscriber->count_items( array( 'list_id' => $item->list_id, 'status' => 'registred', 'active' => 0 ) );
		$waiting 		= (int) $this->master->db_subscriber->count_items( array( 'list_id' => $item->list_id, 'status' => 'registred', 'active' => -1 ) );
		$trashed 		= (int) $this->master->db_subscriber->count_items( array( 'list_id' => $item->list_id, 'status' => 'trash' ) );			
		$total 			= (int) $this->master->db_subscriber->count_items( array( 'list_id' => $item->list_id ) );
		
		$output = "<strong style=\"text-transform:uppercase\">". sprintf( _n( '%d abonné au total', '%d abonnés au total', ( $total <= 1 ), 'tify' ), $total ) ."</strong>";
		$output .= "<br><em style=\"color:#999; font-size:0.9em;\">". sprintf( _n( '%d inscrit', '%d inscrits', ( $registred <= 1 ), 'tify' ), $registred ) .", </em>";
		$output .= "<em style=\"color:#999; font-size:0.9em;\">". sprintf( _n( '%d désinscrit', '%d désinscrits', ( $unsubscribed <= 1 ), 'tify' ), $unsubscribed ) .", </em>";
		$output .= "<em style=\"color:#999; font-size:0.9em;\">". sprintf( _n( '%d en attente', '%d en attente', ( $waiting <= 1 ), 'tify' ), $waiting ) .", </em>";
		$output .= "<em style=\"color:#999; font-size:0.9em;\">". sprintf( _n( '%d à la corbeille', '%d à la corbeille', ( $trashed <= 1 ), 'tify' ), $trashed ) ."</em>";
		
		return $output;
	}
	
	/** == Contenu personnalisé : Date de création de la liste == **/
	public function column_list_date( $item ){
		if( $item->list_date !== '0000-00-00 00:00:00' )
			return mysql2date( __( 'd/m/Y à H:i', 'tify' ), $item->list_date );
		else
			return __( 'Indéterminée', 'tify' );
	}
	
	/** == == **/
	public function column_list_public( $item ){
		return ( $item->list_public ) ? "<strong style=\"color:green;\">". __( 'Publique', 'tify' ) ."</strong>" : "<strong style=\"color:red;\">". __( 'Privée', 'tify' ) ."</strong>";
	}
	
	/* = TRAITEMENT DES ACTIONS = */
	public function process_bulk_action_delete(){
		$item_id = (int) $_GET[$this->item_request];
		check_admin_referer( $this->action_prefix . $this->current_action() . $item_id );
	
		// Destruction des liaisons abonnés <> liste
		$this->master->db_list_rel->delete_list_subscribers( $item_id );
		// Suppression de la liste de diffusion
		$this->db->delete_item( $item_id );
		
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 1, $sendback );

		wp_redirect( $sendback );
	}
}