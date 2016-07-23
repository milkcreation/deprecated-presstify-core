<?php
class tiFy_Wistify_AdminListCampaign extends tiFy_AdminView_ListTable {
	/* = ARGUMENTS = */
	public 	// Paramètres
			$status;
			
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_Wistify_Master $master, tiFy_Query $query ){
		// Définition des classes de référence
		$this->master 	= $master;
		
		// Instanciation de la classe parente
       	parent::__construct( $query );
		
		// Paramétrage
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
		$this->status = empty( $_REQUEST['status'] ) ? 'any' : $_REQUEST['status'];
		$this->views = array(
			'any'		=> array(
				'label'				=> __( 'Tous (hors corbeille)', 'tify' ),
				'current'			=> ( $this->status === 'any' ) ? true : false,
				'remove_query_args'	=> 'status',
				'count_query_args'	=> array( 'status' => 'any' )
			),
			'edit'			=> array( 
				'label'				=> __( 'Edition', 'tify' ),
				'current'			=> ( $this->status === 'edit' ) ? true : false,
				'add_query_args'	=> array( 'status' => 'edit' ),
				'count_query_args'	=> array( 'status' => 'edit' )
			),
			'ready'			=>  array( 
				'label'				=>__( 'Préparée', 'tify' ),
				'current'			=> ( $this->status === 'ready' ) ? true : false,
				'add_query_args'	=> array( 'status' => 'ready' ),
				'count_query_args'	=> array( 'status' => 'ready' )
			),
			'send'			=>  array( 
				'label'				=>__( 'Attente d\'expédition', 'tify' ),
				'current'			=> ( $this->status === 'send' ) ? true : false,
				'add_query_args'	=> array( 'status' => 'send' ),
				'count_query_args'	=> array( 'status' => 'send' )
			),
			'forwarded'		=>  array( 
				'label'				=>__( 'Distribuée', 'tify' ),
				'current'			=> ( $this->status === 'forwarded' ) ? true : false,
				'add_query_args'	=> array( 'status' => 'forwarded' ),
				'count_query_args'	=> array( 'status' => 'forwarded' )
			),
			'trash' 		=> array( 
				'label'				=> __( 'Corbeille', 'tify' ),
				'current'			=> ( $this->status === 'trash' ) ? true : false,
				'add_query_args'	=> array( 'status' => 'trash' ),
				'count_query_args'	=> array( 'status' => 'trash' )
			)
		);
	}
	
	/* = DECLENCHEUR = */
	public function current_screen( $current_screen ){
		wp_enqueue_style( 'tify_wistify_campaign-table', $this->master->admin->uri .'/css/campaign-table.css', array( ), '151019' );
	}
	
	/* = PARAMETRAGE = */
	/** == Liste des colonnes == **/
	public function get_columns() {
		$c = array(
			'cb'       			=> '<input type="checkbox" />',
			'title' 			=> __( 'Intitulé', 'tify' ),
			'description'  		=> __( 'Description', 'tify' ),
			'infos'  			=> __( 'Informations', 'tify' ),
		);
				
		return $c;
	}
	
	/** == Agrégations des actions aux éléments de la colonne primaire == **/
	public function handle_row_actions( $item, $column_name, $primary ) {
		if ( $primary !== $column_name )
			return;

		if( in_array( $item->campaign_status, array( 'edit', 'ready' ) ) ) : 
			return $this->row_actions( $this->get_item_actions( $item, array( 'edit', 'duplicate', 'trash' ) ) );
		elseif( $item->campaign_status === 'send' ) :
			$actions = $this->get_item_actions( $item, array( 'duplicate' ) );
		
			$actions['cancel'] = "<a href=\"".
									$this->get_single_action_uri( $item->{$this->primary_key}, 'cancel', array( '_wp_http_referer' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) 
									."\" title=\"". __( 'Annuler l\'envoi de la campagne', 'tify' ) ."\" style=\"color:orange;\">".  
									__( 'Annuler l\'envoi', 'tify' ) 
									."</a>";
			return $this->row_actions( $actions );
		elseif( $item->campaign_status === 'forwarded' ) :
			return $this->row_actions( $this->get_item_actions( $item, array( 'duplicate' ) ) );
		elseif( $item->campaign_status === 'trash' ) :
			return $this->row_actions( $this->get_item_actions( $item, array( 'untrash', 'delete' ) ) );
		else :
			return $this->row_actions( $this->get_item_actions( $item, array( 'edit', 'trash' ) ) );
		endif;
	}
	
	/* = TRAITEMENT DES DONNEES = */
	/** == Éxecution de l'action - duplication == **/
	public function process_bulk_action_duplicate(){
		$item_id = $this->current_item();		
		check_admin_referer( $this->db->table . $this->current_action() . $item_id );

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
		$item_id = $this->current_item();		
		check_admin_referer( $this->db->table . $this->current_action() . $item_id );
		
		$this->db->update_item( $item_id, array( 'status' => 'ready' ) );
		
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'cancelled', $sendback );

		wp_redirect( $sendback );
	}
	
	/* = AFFICHAGE = */
	/*** === COLONNE - Case à cocher === ***/
	public function column_cb( $item ){
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" /><div class="locked-indicator"></div>', $this->_args['singular'], $item->{$this->primary_key} );
    }

	/*** === COLONNE - Titre === ***/
	public function column_title( $item ){
		$title = ! $item->campaign_title ? __( '(Pas de titre)', 'tify' ) : $item->campaign_title;				
		$label = ( ! in_array( $item->campaign_status, array( 'edit' ) ) && ( $this->status === 'any' ) && isset( $this->views[$item->campaign_status]['label'] ) ) ? "<span> - ". $this->views[$item->campaign_status]['label'] ."</span>" : false;
		
		return sprintf('<strong><a href="%2$s">%1$s</a> %3$s</strong>', $title, $this->get_edit_uri( $item->{$this->primary_key} ), $label );    	
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
}