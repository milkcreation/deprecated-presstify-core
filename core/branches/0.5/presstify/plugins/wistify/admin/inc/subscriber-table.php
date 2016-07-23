<?php
class tiFy_Wistify_Subscriber_AdminTable extends tiFy_AdminView_List_Table{
	/* = ARGUMENTS = */	
	private	// Référence
			$master,
			$main;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_Wistify_Master $master ){
		// Définition des classe de référence
		$this->master 	= $master;
		$this->main 	= $this->master->admin->subscriber;
			
		// Définition de la classe parente
       	parent::__construct( 
       		array(
	            'singular'  => 'tiFy_wistify_subscriber',
	            'plural'    => 'tiFy_wistify_subscribers',
	            'ajax'      => true,
	            'screen'	=> $this->master->hookname['subscriber']
        	), 
        	$this->master->db_subscriber
		);
		
		// Paramétrage
		/// Environnement
		$this->base_link = add_query_arg( array( 'page' => $this->master->menu_slug['subscriber'] ), admin_url( '/admin.php' ) );
		$this->edit_base_link = add_query_arg( array( 'page' => $this->master->menu_slug['subscriber'], 'view' => 'edit_form' ), admin_url( '/admin.php' ) ); 
		
		/// Notifications
		$this->notifications = array(
			'deleted' 				=> array(
				'message'		=> __( 'L\'abonné a été supprimé définitivement', 'tify' ),
				'type'			=> 'success'
			),
			'trashed' 				=> array(
				'message'		=> __( 'L\'abonné a été placé dans la corbeille', 'tify' ),
				'type'			=> 'success'
			),
			'untrashed' 				=> array(
				'message'		=> __( 'L\'abonné a été restauré', 'tify' ),
				'type'			=> 'success'
			)
		);
		
		/// Vues Filtrées
		$status = ! empty( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'any' ;
		$active = isset( $_REQUEST['active'] ) ? ( ! empty( $_REQUEST['active'] ) ? (int) $_REQUEST['active'] : 0 ) : null;
		$this->views = array(
			'any'		=> array(
				'label'				=> __( 'Tous (hors corbeille)', 'tify' ),
				'current'				=> ( $status === 'any' ) ? true : false,
				'remove_query_args'	=> array( 'status' ),
				'add_query_args'	=> array( 'list_id' => ( ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1 ) ),
				'count_query_args'	=> array( 'status' => 'any', 'list_id' => ( ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1 ) )
			),
			'registred'		=> array(
				'label'					=> __( 'Inscrits', 'tify' ),
				'current'				=> ( ( $status === 'registred' ) && ! is_null( $active ) && ( $active === 1 ) ) ? true : false,
				'add_query_args'		=> array( 'status' => 'registred', 'list_id' => ( ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1 ), 'active' => 1 ),
				'count_query_args'		=> array( 'status' => 'registred', 'list_id' => ( ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1 ), 'active' => 1 ),
				
			),			
			'unsubscribed'	=> array(
				'label'					=> __( 'Désinscrits', 'tify' ),
				'current'				=> ( ( $status === 'registred' ) && ! is_null( $active ) && ( $active === 0 ) ) ? true : false,
				'add_query_args'		=> array( 'status' => 'registred', 'list_id' => ( ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1 ), 'active' => 0 ),
				'count_query_args'		=> array( 'status' => 'registred', 'list_id' => ( ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1 ), 'active' => 0 )
			),
			'waiting'		=> array(
				'label'					=> __( 'En attente', 'tify' ),
				'current'				=> ( ( $status === 'registred' ) && ! is_null( $active ) && ( $active === -1 ) ) ? true : false,
				'add_query_args'		=> array( 'status' => 'registred', 'list_id' => ( ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1 ), 'active' => -1 ),
				'count_query_args'		=> array( 'status' => 'registred', 'list_id' => ( ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1 ), 'active' => -1 )
			),
			'trash' => array(
				'label'					=> __( 'Corbeille', 'tify' ),
				'current'				=> ( $status === 'trash' ) ? true : false,			
				'add_query_args'		=> array( 'status' => 'trash' ),
				'remove_query_args'		=> array( 'active' ),
				'count_query_args'		=> array( 'status' => 'trash' )
			)
		);
		
		/// Arguments de récupération des éléments
		$this->prepare_query_args = array(
			'status' 	=> ! empty( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'any',
			'list_id'	=> isset( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1,
			'active'	=> isset( $_REQUEST['active'] ) ? $_REQUEST['active'] : null
		);
	}
	
	/* = ORGANES DE NAVIGATION = */	
	/** == Filtrage avancé  == **/
	protected function extra_tablenav( $which ) {
	?>
		<div class="alignleft actions">
		<?php if ( 'top' == $which ) : ?>
			<label class="screen-reader-text" for="list_id"><?php _e( 'Filtre par liste de diffusion', 'tify' ); ?></label>
			<?php 
				wistify_mailing_lists_dropdown( 
					array(
						'show_option_all'	=> __( 'Toutes les listes de diffusion', 'tify' ),
						'show_count'		=> true,
						
						'selected' 			=> ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1,
						'orderby'			=> 'title',
						'order'				=> 'ASC'
					)
				); 
				submit_button( __( 'Filtrer', 'tify' ), 'button', 'filter_action', false, array( 'id' => 'mailing_list-query-submit' ) );?>
		<?php endif;?>
		</div>
	<?php
	}
			
	/* = COLONNES = */
	/** == Définition des colonnes == **/
	public function get_columns() {
		$c = array(
			'cb'       					=> '<input type="checkbox" />',
			'subscriber_email' 			=> __( 'Email', 'tify' ),
			'subscriber_lists' 			=> __( 'Listes de diffusion', 'tify' ),			
			'subscriber_date' 			=> __( 'Depuis le', 'tify' )
		);	
		return $c;
	}
	
	/** == Définition de l'ordonnancement par colonne == **/
	public function get_sortable_columns() {
		return array(	
			'subscriber_email'  => 'email',
			'subscriber_date'	=> array( 'date', true )
		);
	}	
	
	/** == Contenu personnalisé : Titre == **/
	function column_subscriber_email( $item ){
		$title = ! $item->subscriber_email ? __( '(Pas d\'email)', 'tify' ) : $item->subscriber_email;
			
		if( $item->subscriber_status !== 'trash' )
			$actions = $this->default_single_actions( $item, array( 'edit', 'trash' ) );
		else
			$actions = $this->default_single_actions( $item, array( 'untrash', 'delete' ) );
			
		$orphan = ( $this->master->db_list_rel->is_orphan( $item->subscriber_id ) ) ? "<span> - ". __( 'Orphelin', 'tify' ) ."</span>" : false;
		
		return sprintf('<strong><a href="%2$s">%1$s</a>%3$s</strong>%4$s', $title, $this->get_item_edit_link( $item ), $orphan, $this->row_actions( $actions ) );    	
	}

	/** == Contenu personnalisé : Listes de diffusion == **/
	function column_subscriber_lists( $item ){
		$output  = "";
		$output .= "<ul style=\"margin:0\">\n";
		$output .= "\t<li style=\"margin-bottom:2px;\"><b>".__( 'Inscrit à : ')."</b>\n";
		if( $list_ids = $this->master->db_list_rel->get_items_col( 'list_id', array( 'subscriber_id' => $item->{$this->db->primary_key}, 'active' => 1 ) ) ) : 
			$list = array();
			foreach( $list_ids as $list_id )
				$list[] = "<a href=\"". ( add_query_arg( 'list_id', $list_id, $this->base_link ) ) ."\">". ( $list_id ? $this->master->db_list->get_item_var_by_id( $list_id, 'title' ) : __( 'Inscription sans liste', 'tify' ) )  ."</a>";
			$output .= join( ', ', $list );
		else :
			$output .=  __( 'Aucune', 'tify' );
		endif;
		$output .= "\t<li style=\"margin-bottom:2px;\"><b>".__( 'Désinscrit de : ')."</b>\n";
		if( $list_ids = $this->master->db_list->get_subscriber_list_ids( $item->{$this->db->primary_key}, 0 ) ) : 
			$list = array();
			foreach( $list_ids as $list_id )
				$list[] = "<a href=\"". ( add_query_arg( 'list_id', $list_id, $this->base_link ) ) ."\">". $this->master->db_list->get_item_var_by_id( $list_id, 'title' ) ."</a>";
			$output .= join( ', ', $list );
		else :
			$output .=  __( 'Aucune', 'tify' );
		endif;
		$output .= "\t</li>\n";
		$output .= "\t<li style=\"margin-bottom:2px;\"><b>".__( 'En attente pour : ')."</b>\n";
		if( $list_ids = $this->master->db_list_rel->get_items_col( 'list_id', array( 'subscriber_id' => $item->{$this->db->primary_key}, 'active' => -1 ) ) ) : 
			$list = array();
			foreach( (array) $list_ids as $list_id )
				$list[] = "<a href=\"". ( add_query_arg( 'list_id', $list_id, $this->base_link ) ) ."\">". ( $list_id ? $this->master->db_list->get_item_var_by_id( $list_id, 'title' ) : __( 'Inscription sans liste', 'tify' ) ) ."</a>";
			$output .= join( ', ', $list );
		else :
			$output .=  __( 'Aucune', 'tify' );
		endif;
		$output .= "\t</li>\n";	
		
		$output .= "<ul>\n";
		
		return $output;		
	}
	
	/** == Contenu personnalisé : Date d'inscription == **/
	function column_subscriber_date( $item ){
		if( $item->subscriber_date !== '0000-00-00 00:00:00' )
			return mysql2date( __( 'd/m/Y à H:i', 'tify' ), $item->subscriber_date );
		else
			return __( 'Indéterminé', 'tify' );
	}
}