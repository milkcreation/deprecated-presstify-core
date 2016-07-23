<?php
class tiFy_Wistify_Campaign_AdminTable extends tiFy_AdminView_List_Table {
	/* = ARGUMENTS = */	
	private	// Référence
			$master,
			$main;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_Wistify_Master $master ){
		// Définition des classes de référence
		$this->master 	= $master;
		$this->main 	= $this->master->admin->campaign;
		
		// Définition de la classe parente
       	parent::__construct( 
       		array(
	            'singular'  => 'tify_wistify_campaign',
	            'plural'    => 'tify_wistify_campaigns',
	            'ajax'      => true,
	            'screen'	=> $this->master->hookname['campaign']
	        ), 
        	$this->master->db_campaign  
		);
		
		// Paramétrage
		/// Environnement
		$this->base_link = add_query_arg( array( 'page' => $this->master->menu_slug['campaign'] ), admin_url( '/admin.php' ) );
		$this->edit_base_link = add_query_arg( array( 'page' => $this->master->menu_slug['campaign'], 'view' => 'edit_form' ), admin_url( '/admin.php' ) ); 
		
		/// Notifications
		$this->notifications = array(
			'duplicated' 			=> array(
				'message'		=> __( 'La campagne a été dupliquée', 'tify' ),
				'type'			=> 'success'
			),
			'cancelled'			=> array(
				'message'		=> __( 'L\'expédition de la campagne a été annulé', 'tify' ),
				'type'			=> 'success'
			),
			'deleted' 				=> array(
				'message'		=> __( 'La campagne a été supprimée définitivement', 'tify' ),
				'type'			=> 'success'
			),
			'trashed' 				=> array(
				'message'		=> __( 'La campagne a été placée dans la corbeille', 'tify' ),
				'type'			=> 'success'
			),
			'untrashed' 				=> array(
				'message'		=> __( 'La campagne a été restaurée', 'tify' ),
				'type'			=> 'success'
			)
		);
		
		/// Vues Filtrées
		$current = empty( $_REQUEST['status'] ) ? 'any' : $_REQUEST['status'];
		$this->views = array(
			'any'		=> array(
				'label'				=> __( 'Tous (hors corbeille)', 'tify' ),
				'current'			=> ( $current === 'any' ) ? true : false,
				'remove_query_args'	=> 'status',
				'count_query_args'	=> array( 'status' => 'any' )
			),
			'edit'			=> array( 
				'label'				=> __( 'Edition', 'tify' ),
				'current'			=> ( $current === 'edit' ) ? true : false,
				'add_query_args'	=> array( 'status' => 'edit' ),
				'count_query_args'	=> array( 'status' => 'edit' )
			),
			'ready'			=>  array( 
				'label'				=>__( 'Préparée', 'tify' ),
				'current'			=> ( $current === 'ready' ) ? true : false,
				'add_query_args'	=> array( 'status' => 'ready' ),
				'count_query_args'	=> array( 'status' => 'ready' )
			),
			'send'			=>  array( 
				'label'				=>__( 'Attente d\'expédition', 'tify' ),
				'current'			=> ( $current === 'send' ) ? true : false,
				'add_query_args'	=> array( 'status' => 'send' ),
				'count_query_args'	=> array( 'status' => 'send' )
			),
			'forwarded'		=>  array( 
				'label'				=>__( 'Distribuée', 'tify' ),
				'current'			=> ( $current === 'forwarded' ) ? true : false,
				'add_query_args'	=> array( 'status' => 'forwarded' ),
				'count_query_args'	=> array( 'status' => 'forwarded' )
			),
			'trash' 		=> array( 
				'label'				=> __( 'Corbeille', 'tify' ),
				'current'			=> ( $current === 'trash' ) ? true : false,
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
	/** == Liste des colonnes == **/
	public function get_columns() {
		$c = array(
			'cb'       			=> '<input type="checkbox" />',
			'title' 			=> __( 'Intitulé', 'tify' ),
			'description'  		=> __( 'Description', 'tify' ),
			'infos'  			=> __( 'Informations', 'tify' ),
		);
		/*if( isset( $_REQUEST['status'] ) && ( $_REQUEST['status'] === 'send' ) )
			$c['campaign_queue'] = __( 'État de distribution', 'tify' );*/
				
		return $c;
	}
		
	/*** === COLONNE - Case à cocher === ***/
	public function column_cb( $item ){
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" /><div class="locked-indicator"></div>', $this->_args['singular'], $item->{$this->primary_key} );
    }

	/*** === COLONNE - Titre === ***/
	public function column_title( $item ){
		$title = ! $item->campaign_title ? __( '(Pas de titre)', 'tify' ) : $item->campaign_title;
		
		if( in_array( $item->campaign_status, array( 'edit', 'ready' ) ) ) :
			$actions = $this->default_single_actions( $item, array( 'edit', 'duplicate', 'trash' ) );
		elseif( $item->campaign_status === 'send' ) :
			$actions = $this->default_single_actions( $item, array( 'duplicate' ) );
			$actions['cancel'] = "<a href=\"".
									wp_nonce_url(
										$this->get_single_action_link( $item, 'cancel', array( '_wp_http_referer' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ),
										$this->action_prefix .'cancel'. $item->{$this->primary_key} 
									)
									."\" title=\"". __( 'Annuler l\'envoi de la campagne', 'tify' ) ."\" style=\"color:orange;\">".  
									__( 'Annuler l\'envoi', 'tify' ) 
									."</a>";
		elseif( $item->campaign_status === 'forwarded' ) :
			$actions = $this->default_single_actions( $item, array( 'duplicate' ) );
		elseif( $item->campaign_status === 'trash' ) :
			$actions = $this->default_single_actions( $item, array( 'untrash', 'delete' ) );
		else :
			$actions = $this->default_single_actions( $item, array( 'edit', 'trash' ) );
		endif;
			
		$status = ( ! in_array( $item->campaign_status, array( 'edit' ) ) && empty( $_REQUEST['status'] ) && isset( $this->views[$item->campaign_status]['label'] ) ) ? "<span> - ". $this->views[$item->campaign_status]['label'] ."</span>" : false;
		
		return sprintf('<strong><a href="%2$s">%1$s</a> %3$s</strong>%4$s', $title, $this->get_item_edit_link( $item ), $status, $this->row_actions( $actions ) );    	
	}

	/*** === COLONNE - Description === ***/
	public function column_description( $item ){
		return nl2br( $item->campaign_description );
	}
	
	/*** === COLONNE - Informations === ***/
	public function column_infos( $item ){
		$output  = "";
		$output .= 	"<ul>";
		// Date de création
		$output .= 		"<li>".
							"<label><strong>". __( 'Créé', 'tify' ) ." : </strong></label>".
							sprintf( __( 'le %s à %s', 'tify' ), mysql2date( 'd/m/Y', $item->campaign_date ), mysql2date( 'H\hi', $item->campaign_date ) );
		if( $userdata = get_userdata( $item->campaign_author ) )
			$output .=		"<br>". sprintf( __( 'par %s', 'tify' ), $userdata->display_name );							
		$output .= 		"</li>";
		// Date de Modification
		if( $item->campaign_modified !== "0000-00-00 00:00:00" ) :
			$output .= 		"<li>".
								"<label><strong>". __( 'Modifié', 'tify' ) ." : </strong></label>".
								sprintf( __( 'le %s à %s', 'tify' ), mysql2date( 'd/m/Y', $item->campaign_modified ), mysql2date( 'H\hi', $item->campaign_modified ) );
			if( ( $last_editor = $this->db->get_item_meta( $item->{$this->primary_key}, '_edit_last' ) ) && ( $userdata = get_userdata( $last_editor ) ) )
				$output .=		"<br>". sprintf( __( 'par %s', 'tify' ), $userdata->display_name );							
			$output .= 		"</li>";
		endif;
		$output .= "</ul>";
		
		return $output;
	}
	
	/*** === COLONNE - File d'attente d'expédition === ***/
	public function column_campaign_queue( $item ){
		$output  = "";
		$output .= "<div>". sprintf( _n( 'Envoi effectué', 'Envois effectués', $queue_token['processed'], 'tify' ) ) ." <strong>". (int) $queue_token['processed'] ."</strong> ". __( 'sur', 'tify' )." {$queue_token['total']}</div>";
		$output .= "<div>". sprintf( __( 'dernier envoi %s', 'tify'), isset( $queue_token['last_datetime'] ) ? mysql2date( 'd/m/Y à H:i:s', $queue_token['last_datetime'] ) : '0000-00-00 00:00:00' ) ."</div>";
		
		return $output;
	}
	
	/** == Définition de l'attribut classe de la ligne relative à l'élément == **/
	public function set_row_classes( $item, $classes = '' ){
		if( $this->db->check_lock( $item->campaign_id, 'edit' ) )
			return $classes." wp-locked";		
	}
	
	/* = TRAITEMENT DES ACTIONS = */
	/** == Éxecution de l'action - duplication == **/
	public function process_bulk_action_duplicate(){
		$item_id = (int) $_GET[$this->item_request];		
		check_admin_referer( $this->action_prefix . $this->current_action() . $item_id );

		if( ! $c = $this->db->get_item_by_id( $item_id ) )
			return;
							
		$args = array(
			'campaign_uid'				=> tify_generate_token(),
			'campaign_title'			=> $c->campaign_title,
			'campaign_description'		=> $c->campaign_description,
			'campaign_author'			=> wp_get_current_user()->ID, 
			'campaign_date'				=> current_time('mysql', false ),
			'campaign_status'			=> 'edit',
			'campaign_step'				=> $c->campaign_step, 
			'campaign_template_name'	=> $c->campaign_template_name,
			'campaign_content_html'		=> $c->campaign_content_html,
			'campaign_content_txt'		=> $c->campaign_content_txt,
			'campaign_recipients'		=> $c->campaign_recipients,
			'campaign_message_options'	=> $c->campaign_message_options,
			'campaign_send_options'		=> $c->campaign_send_options,
			'campaign_send_datetime'	=> '0000-00-00 00:00:00'							
		);
 		
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		
		$this->db->insert_item( $args );
		$sendback = add_query_arg( 'message', 'duplicated', $sendback );				

		wp_redirect( $sendback );
	}
	
	/** == Éxecution de l'action - Annulation de l'expédition == **/
	public function process_bulk_action_cancel(){
		$item_id = (int) $_GET[$this->item_request];
		check_admin_referer( $this->action_prefix . $this->current_action() . $item_id );
		
		$this->db->update_item( $item_id, array( 'status' => 'ready' ) );
		
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'cancelled', $sendback );

		wp_redirect( $sendback );
	}
}