<?php
class tiFy_WebAgencyCRM_AdminListCustomer extends tiFy_AdminView_ListTable{
	/* = ARGUMENTS = */
	public 	// Configuration 
			$roles;
			
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_WebAgencyCRM_Master $master, tiFy_Query $query ){
		// Définition des classes de référence
		$this->master 	= $master;
		
		// Configuration
		$this->roles = array_keys( $this->master->roles );
		
		// Instanciation de la classe parente
       	parent::__construct( $query );			
	}
	
	/* = PARAMETRAGE = */
	/** == Récupération des éléments == **/
	public function prepare_items(){
		global $wpdb;
		// Récupération des arguments
		$search = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';	
		$per_page = $this->get_items_per_page( $this->per_page_option, $this->items_per_page );
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
			    	'value' => '"(tify_wacrm_customer)"',
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
            'per_page'    => $this->per_page,                    
            'total_pages' => ceil( $total_items / $this->per_page )
			) 
		);
		
		return wp_parse_args( $this->extra_parse_query_items(), $args );
	}
			
	/** == Définition de la liste des colonnes == **/
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
	
	/* = AFFICHAGE = */
	/** == Contenu personnalisé : Case à cocher == **/
	public function column_cb( $item ){
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->ID );
    }
	
	/** == Contenu personnalisé : Login == **/
	function column_user_login( $item ){
		$edit_link = esc_url( add_query_arg( array( 'user_id' => $item->ID ) ) );
		$actions = array();

		if ( current_user_can( 'edit_user',  $item->ID ) ) :
			$edit = "<strong><a href=\"{$edit_link}\">$item->user_login</a></strong><br />";
			$actions['edit'] = "<a href=\"{$edit_link}\">". __( 'Editer', 'tify' ) . "</a>";
		else :
			$edit = "<strong>$item->user_login</strong><br />";
		endif;
		
		return $edit;
	}
	
	/** == Contenu personnalisé : Rôle == **/
	function column_user_registered( $item ){
		return mysql2date( __( 'd/m/Y à H:i', 'tify' ), $item->user_registered );
	}
}