<?php
class tiFy_Contest_Participant_AdminListTable extends tiFy_AdminView_List_Table {
	/* = ARGUMENTS = */
	public 	// Configuration 
			$roles;
			
	private	// Référence
			$master,
			$main;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_Contest_Master $master ){
		// Définition des classes de référence
		$this->master 	= $master;
		$this->main 	= $this->master->admin->participant;
		
		// Configuration
		$this->roles = array_keys( $this->master->roles );
		
		// Définition de la classe parente
       	parent::__construct( 
       		array(
	            'singular'  => 'tify_contest_participant',
	            'plural'    => 'tify_contest_participants',
	            'ajax'      => true,
	            'screen'	=> $this->master->admin->hookname['participant']
        	) 
		);
	}
	
	/* = CONFIGURATION = */
	/** == Définition des status == **/
	function set_status(){
		return 	array( 
			'available' 		=> array( 1 => __( 'Actif', 'tify' ), 0 => __( 'Inactif', 'tify' ), -1 => __( 'En attente', 'tify' )  ),
			'location'			=> $this->main->list_link		
		);
	}
	
	/* = PREPARATION DES ITEMS = */
	public function prepare_items(){
		global $wpdb;
		// Récupération des arguments
		$search = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';	
		$per_page = $this->get_items_per_page( $this->per_page_option, $this->per_page_default );
		$paged = $this->get_pagenum();
		$roles = ! empty( $_REQUEST['role'] ) ? array( $_REQUEST['role'] ) : $this->roles;		
		
		// Arguments par défaut
		$args = array(						
			'number' 		=> $per_page,
			'offset' 		=> ( $paged-1 ) * $per_page,
			'count_total'	=> true,
			'search' 		=> $search,
			'fields' 		=> 'all_with_meta',
			'orderby'		=> 'user_registered',
			'order'			=> 'DESC',
			'meta_query'	=> array(
				array(
			    	'key' => $wpdb->get_blog_prefix( get_current_blog_id() ) . 'capabilities',
			    	'value' => '"(' . implode('|', array_map( 'preg_quote', $roles ) ) . ')"',
			    	'compare' => 'REGEXP'
				)
			)
		);
		
		// Traitement des arguments
		if ( '' !== $args['search'] )
			$args['search'] = '*' . $args['search'] . '*';			
		if ( isset( $_REQUEST['orderby'] ) )
			$args['orderby'] = $_REQUEST['orderby'];
		if ( isset( $_REQUEST['order'] ) )
			$args['order'] = $_REQUEST['order'];
		if ( ! empty( $_REQUEST['status'] ) && $_REQUEST['status'] !== 'any' ) :
			$args['meta_query']['relation'] = 'AND'; 
			$args['meta_query'][] = array(
				'key' 		=> $wpdb->get_blog_prefix( ) .'tify_membership_status',
				'value'		=> (int) $_REQUEST['status'],
				'type'		=> 'NUMERIC'
			);
		endif;
		
		// Récupération des items
		$wp_user_query = new WP_User_Query( $args );
        $this->items = $wp_user_query->get_results();
		
		// Pagination
		$total_items = $wp_user_query->get_total();
		$this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $per_page,                    
            'total_pages' => ceil( $total_items / $per_page )
			) 
		);
		
		return wp_parse_args( $this->extra_parse_query_items(), $args );
	}

	/* = ORGANES DE NAVIGATION = */
	/** == Filtrage principal  == **/
	public function get_views(){
		// Bypass
		if( ! $_status = $this->_get_status() )
			return;
	
		global $wpdb;
		$count_query_args = array(	
			'orderby'		=> 'ID',
			'order'			=> 'DESC',
			'meta_query' 	=> 
				array(
					array(
				    	'key' => $wpdb->get_blog_prefix( ) . 'capabilities',
				    	'value' => '"(' . implode('|', array_map( 'preg_quote', $this->roles ) ) . ')"',
				    	'compare' => 'REGEXP'
					)
				)
		);		
		
		$views = array();
		
		if( $_status['any'] ) :
			$query_args = array();
			foreach( (array) $_status['query_args'] as $args => $value ) :
				$query_args[$args] = sprintf( $value, 'any' );
			endforeach;
			$location = add_query_arg( $query_args, $_status['location'] );
			
			$_count_query_args = $count_query_args;			
			$wp_user_query = new WP_User_Query( $_count_query_args );
       		$results = $wp_user_query->get_results();

			$views[] = 	"<a href=\"$location\" class=\"". ( ( is_null( $_status['current'] ) || $_status['current'] === 'any' ) ? 'current' : '' ) ."\">". ( is_string( $_status['any'] ) ? $_status['any'] : __( 'Tous', 'tify' ) ) ." <span class=\"count\">(". 
						$wp_user_query->get_total().
						")</span></a>";
		endif;	
				
		foreach( $_status['available'] as $status  => $label ) :
			$query_args = array();
			foreach( (array) $_status['query_args'] as $args => $value ) :
				$query_args[$args] = sprintf( $value, $status );
			endforeach;
			$location = add_query_arg( $query_args, $_status['location'] );	
			
			$_count_query_args = $count_query_args;	
			$_count_query_args['meta_query']['relation'] = 'AND'; 
			$_count_query_args['meta_query'][] = array(
				'key' 		=> $wpdb->get_blog_prefix( ) .'tify_membership_status',
				'value'		=> $status,
				'type'		=> 'NUMERIC'
			);
			$wp_user_query = new WP_User_Query( $_count_query_args );
       		$results = $wp_user_query->get_results();

			$views[] = 	"<a href=\"$location\" class=\"". ( ( (string) $status === $_status['current'] ) ? 'current' : '' ) ."\">$label <span class=\"count\">(". 
						$wp_user_query->get_total().
						")</span></a>";	
		endforeach;
		
		return $views;
	}
			
	/* = COLONNES = */
	/** == Définition des colonnes == **/
	public function get_columns() {
		$c = array(
			'cb'       			=> '<input type="checkbox" />',
			'user_login' 		=> __( 'Identifiant', 'tify' ),
			'display_name'		=> __( 'Nom', 'tify' ),
			'user_email'		=> __( 'E-mail', 'tify' ),
			'user_registered'	=> __( 'Date d\'enregistrement', 'tify' )
		);
	
		return $c;
	}
	
	/** == Définition de l'ordonnancement par colonne == **/
	public function get_sortable_columns() {
		$c = array(
			'user_login' 		=> 'user_login',
			'display_name'     	=> 'display_name',
			'user_email'    	=> 'user_email',
			'user_registered'	=> 'user_registered'
		);

		return $c;
	}
	
	/** == Contenu personnalisé : Case à cocher == **/
	public function column_cb( $item ){
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->ID );
    }
	
	/** == Contenu personnalisé : Login == **/
	function column_user_login( $item ){
		$edit_link = esc_url( add_query_arg( array( 'user_id' => $item->ID ), $this->main->edit_link ) );
		$actions = array();

		if ( current_user_can( 'edit_user',  $item->ID ) ) :
			$edit = "<strong><a href=\"{$edit_link}\">$item->user_login</a></strong><br />";
			$actions['edit'] = "<a href=\"{$edit_link}\">". __( 'Editer', 'tify' ) . "</a>";
		else :
			$edit = "<strong>$item->user_login</strong><br />";
		endif;
	
		$edit .= $this->row_actions( $actions );
		
		return $edit;
	}
	
	/** == Contenu personnalisé : Rôle == **/
	function column_user_registered( $item ){
		return mysql2date( __( 'd/m/Y à H:i', 'tify' ), $item->user_registered );
	}
		
	/* = TRAITEMENT DES ACTIONS = */
	function process_bulk_action(){
		if( $this->current_action() ) :	
			switch( $this->current_action() ) :
				case 'delete' :									
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );	
					wp_redirect( $sendback );
					exit;
				break;
				case 'trash' :									
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );	
					wp_redirect( $sendback );
					exit;
				break;
				case 'untrash' :									
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );	
					wp_redirect( $sendback );
					exit;
				break;		
			endswitch;
		elseif( ! empty( $_REQUEST['_wp_http_referer'] ) ) :
	 		wp_redirect( remove_query_arg( array('_wp_http_referer', '_wpnonce'), wp_unslash($_SERVER['REQUEST_URI']) ) );
	 		exit;
		endif;
	}
}