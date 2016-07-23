<?php
/** 
 * @see https://codex.wordpress.org/Class_Reference/WP_List_Table
 */
if( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH .'wp-admin/includes/class-wp-list-table.php' );
	
class tiFy_AdminView_List_Table extends WP_List_Table{
	/* = ARGUMENTS = */
	public	// Paramètres
			/// Environnement
			//// Intitulé de l'option de personnalisation des éléments affichés par page
			$per_page_option,							
			//// Nombre d'éléments affichés par page
			$items_per_page,							
			//// Liste des messages de notification
			$notifications 			= array(			
			/*	
			 	array(
					'message'			=> ''				// (string) Intitulé du message de notification
			 		'type'				=> 'error'			// (string) error (default) | warning | success - Type de notification 
					'dismissible'		=> false			// (bool) Permet de rendre une notification révocable
				) 
			*/
			),
			//// Liste des champs cachés
			$hidden_fields			= array(			
			/*	
			 	array(
					'id'	=>	'',
					'class'	=>	'',
					'name'	=>	'',
					'value'	=>	'',
					'attrs'	=> array()
				) 
			 */
			),
			//// Préfixe du processus d'execution des actions
			$action_prefix,
			//// Liste des actions							
			$actions				= array(			
			/*	
				array(
					'label'				=> ''
			 		'title'				=> ''
					'single'			=> true
				) 
			*/
			),
			//// Liste des vues filtrées															
			$views					= array(			
			/*	
				array(
					'label'					=> '',		// Intitulé du filtre
			 		'value'					=> '',		// Valeur du filtre
				 	'link_attrs'			=> array(), // Attributs du lien du filtre
					'uri'					=> '',		// Url du lien de filtrage
					'add_query_args'		=> array(),	// Arguments complémentaires ajouter au lien du filtre
					'remove_query_args'		=> array(),	// Arguments retiré au lien du filtre
					'count_query_args'		=> true		// Affiche le compte des éléments du filtre			
				) 
			*/
			),
			//// Arguments par defaut de récupération d'élément			
			$prepare_query_args		= array(),			
			//// Base de lien
			$base_link,									// Base du lien de filtrage, d'action
			$edit_base_link,							// Base du lien d'édition d'un élément
			//// Elément
			$primary_key 			= 'ID',				// Clef primaire d'un élément
			$item_request			= 'post_id',		// Argument de requête d'un élément						
			//// Requêtes
			$cb_count_items_query	= '__return_zero',	// Requête de récupération du nombre d'éléments
			$cb_get_items_query		= '__return_empty';	// Requête de récupération des élément
			
	protected	// Référence
				$db,
				// Paramètres
				$row_classes;
			
	/* = CONSTRUCTEUR = */	
	public function __construct( ){
		// Initialisation
		call_user_func_array( array( $this, '_init' ), func_get_args() );	
	}
	
	/* = PARAMETRAGE = */
	private function _init( ){
		// Récupération des arguments
		$numargs 	= func_num_args();
		$args 		= ( $numargs >= 1 )? func_get_arg( 0 ) : array();		 
		$db 		= ( $numargs >= 2 )? func_get_arg( 1 ) : null;

		// Instanciation de la classe parente
       	parent::__construct( 
       		wp_parse_args(
       			$args,
	       		array(
		            'singular'  => 'tify_adminview_list_item',
		            'plural'    => 'tify_adminview_list_items',
		            'ajax'      => false,
		            'screen'	=> 'tify_adminview_list_screen'
	        	)
			)
		);
		
		// Paramètrage
		/// Environement
		$this->items_per_page 	= 20;
		$this->action_prefix	= get_class($this);
		
		/// Définition des notifications prédéfines
		$this->notifications = array(
			'deleted' 				=> array(
				'message'		=> __( 'L\'élément a été supprimé définitivement', 'tify' ),
				'type'			=> 'success'
			),
			'trashed' 				=> array(
				'message'		=> __( 'L\'élément a été placé dans la corbeille', 'tify' ),
				'type'			=> 'success'
			),
			'untrashed' 				=> array(
				'message'		=> __( 'L\'élément a été restauré', 'tify' ),
				'type'			=> 'success'
			)
		);	
			
		/// Instanciation des paramètres relatifs à la base de données
		$this->db = $db;
		
		/// Définition de la clé primaire
		$this->primary_key = $this->db->primary_key;
		
		/// Argument de requête d'un élément
		$this->item_request = $this->primary_key;
		
		/// Fonction de rappel
		$this->cb_count_items_query = array( $this->db, 'count_items' );
		$this->cb_get_items_query 	= array( $this->db, 'get_items' );

		/// Définition du lien de base d'exécution des actions 
		$this->base_link = $this->edit_base_link = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; //$this->screen->parent_file;
		
		// Actions et Filtres Wordpress
		add_action( 'current_screen', array( $this, '_wp_current_screen' ), 99 );
	}

	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Chargement de la page == **/
	public function _wp_current_screen( $current_screen ){
		if( $current_screen->id !== $this->screen->id )
			return;
		
		// Déclenchement du processus des actions
		$this->process_bulk_action();
		
		if( $this->screen && ( $current_screen->id === $this->screen->id ) )
			call_user_func( array( $this, 'current_screen' ), $current_screen );
	}
	
	/* = DECLENCHEURS = */
	/** == Au chargement de la page courante == **/
	public function current_screen(){}
	
	/* = CONFIGURATION = */
	/** == ACTIONS == **/
	/*** === Liste d'actions prédéfinies === ***/
	public function default_single_actions( $item, $action = array() ){
		// Edition		
		$edit 				= "<a href=\"".
									$this->get_item_edit_link( $item )
									."\" title=\"". __( 'Éditer l\'élément', 'tify' ) ."\">". 
									__( 'Éditer', 'tify' ) 
								."</a>";		
		// Mise à la corbeille																				
		$trash					= "<a href=\"". 
		        					$this->get_single_action_link( $item, 'trash', array( '_wp_http_referer' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) 
								."\" title=\"". __( 'Mise à la corbeille de l\'élément', 'tify' ) ."\">". 
								__( 'Mettre à la corbeille', 'tify' ) 
								."</a>";
		// Sortie de la corbeille
		$untrash				= "<a href=\"". 
		        					$this->get_single_action_link( $item, 'untrash', array( '_wp_http_referer' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) 
								."\" title=\"". __( 'Rétablissement de l\'élément', 'tify' ) ."\">". 
								__( 'Rétablir', 'tify' ) 
								."</a>";
		// Suppression définitive					
		$delete				= "<a href=\"". 
		        					$this->get_single_action_link( $item, 'delete', array( '_wp_http_referer' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) 
								."\" title=\"". __( 'Suppression définitive de l\'élément', 'tify' ) ."\">". 
								__( 'Supprimer définitivement', 'tify' ) 
								."</a>";
		// Duplication
		$duplicate				= "<a href=\"". 
		        					$this->get_single_action_link( $item, 'duplicate', array( '_wp_http_referer' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) 
								."\" title=\"". __( 'Dupliquer l\'élément', 'tify' ) ."\">". 
								__( 'Dupliquer', 'tify' ) 
								."</a>";
								
		if( empty( $action ) )
			return compact( 'edit', 'trash', 'untrash', 'delete', 'duplicate' );
		elseif( is_string( $action ) && isset( ${$action} ) )
			return array( $action => ${$action} );
		elseif( is_array( $action ) )
			return compact( $action );			
	}
	
	/** == VUES FILTREES == **/
	/*** === Traitement des arguments d'une vue === ***/
	private function _parse_view( $args ){
		static $instance; $instance++;

		$defaults = array(			
			// Lien
			'label'					=> sprintf( __( 'Filtre #%d', 'tify' ), $instance ),
			'current'				=> false,
			'class'					=> '',
			'link_attrs'			=> array(),
			'uri'					=> $this->base_link,
			'add_query_args'		=> false,
			'remove_query_args'		=> false,						
			'count_query_args'		=> false,
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		// Traitement de l'url
		$parsed_url = parse_url( $uri );
		parse_str(  $parsed_url['query'], $parsed_query );
		$_uri = $parsed_url['scheme'] .'://'. $parsed_url['host'] . $parsed_url['path']; 
	
		// Traitement des arguments de requête à ajouter au lien
		if( ! empty( $add_query_args ) )
			$parsed_query = wp_parse_args( $add_query_args, $parsed_query );
		
		// Traitement des argument de requête à retirer du lien
		if( empty( $remove_query_args ) )
			$remove_query_args = array();
		elseif( is_string( $remove_query_args ) )
			$remove_query_args = array( $remove_query_args );		
		array_push( $remove_query_args, 'action', 'action2', 'filter_action' );		
		foreach( $remove_query_args as $key )
			unset( $parsed_query[$key] );
		
		$uri = add_query_arg( $parsed_query, $_uri );
								
		$view  = "<a href=\"{$uri}\"";
		$view .= " class=\"". ( $current ? 'current' : '' ) ." {$class}\"";
		if( ! empty( $link_attrs ) )
			foreach( $link_attrs as $i => $j )
				$view .= "{$i}=\"{$j}\" ";
		$view .= ">";
		$view .= $label; 
		$view .= " <span class=\"count\">(". call_user_func( $this->cb_count_items_query, $count_query_args ) .")</span>";
		$view .= "</a>";	
		
		return $view;
	}
	
 	/*** === Récupération des vues filtrées === ***/
	private function _get_views( ){
		$views = array();
		foreach( (array) $this->views as $key => $view )
			$views[$key] = $this->_parse_view( $view );
		
		return $views;
	}
	
	/** == == **/
	private function prepare_row_classes( $item ) {
		$defaults_row_classes = '';
		$this->row_classes[ $item->{$this->primary_key} ] = $this->set_row_classes( $item, $defaults_row_classes );
	}
	
	/* = PARAMETRAGE = */
	/** == Récupération de l'attribut ID de la ligne relative à un item == **/
	private function get_row_id( $item ){
		if( isset( $item->{$this->primary_key} ) )
			return "item-{$item->{$this->primary_key}}";
	}
			
	/** == Récupération de l'attribut classe de la ligne relative à un item == **/
	private function get_row_classes( $item ){
		if( ! isset( $item->{$this->primary_key} ) )
			return;
		if( empty( $this->row_classes[$item->{$this->primary_key}] ) )
			return '';
		if( is_array( $this->row_classes[$item->{$this->primary_key}] ) )
			return implode( ' ', $this->row_classes[$item->{$this->primary_key}] );
		elseif( is_string( $this->row_classes[$item->{$this->primary_key}] ) )
			return $this->row_classes[$item->{$this->primary_key}];
	}
	
	/* = CONTROLEUR = */
	/** == Lien vers le formulaire d'édition d'un élément == **/
	public function get_item_edit_link( $item ){ 
		return add_query_arg( array( 'action' => 'edit', $this->item_request => $item->{$this->primary_key} ), ( $this->edit_base_link ? $this->edit_base_link : $this->base_link ) );
	}
	
	/** == Lien vers une action appliquée à un élément == **/
	public function get_single_action_link( $item, $action, $args = array(), $nonce = true ){
		$defaults['action'] 			= $action;
		$defaults[$this->item_request]	= $item->{$this->primary_key};		
		$args = wp_parse_args( $args, $defaults );
		
		$uri = add_query_arg( 
			$args,
			$this->base_link
		);
		
		if( $nonce )
			$uri = wp_nonce_url( $uri, ( is_bool( $nonce ) ? $this->action_prefix . $action . $item->{$this->primary_key} : $nonce ) );
		
		return $uri;
	}	
	
	/** == Récupération de la notification courante == **/
	public function current_notification(){
		if( ! empty( $_REQUEST['message'] ) && isset( $this->notifications[$_REQUEST['message']] ) )
			return array( wp_parse_args( $this->notifications[$_REQUEST['message']], array( 'message' => '', 'type' => 'error', 'dismissible' => false ) ) );		
	}
	
	/*** === Traitement des requêtes de récupération standard === ***/
	public function prepare_query_args(){
		// Récupération des arguments		
		$search 	= isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
		$per_page 	= $this->get_items_per_page( $this->per_page_option, $this->items_per_page );
		$paged 		= $this->get_pagenum();
		
		// Arguments par défaut
		$args = array(						
			'per_page' 	=> $per_page,
			'paged'		=> $paged,
			'search' 	=> $search,
			'order' 	=> 'DESC',
			'orderby' 	=> $this->primary_key
		);
			
		// Traitement des arguments
		foreach( $_REQUEST as $key => $value )
			$args[$key] = $value;
		
		return wp_parse_args( $this->prepare_query_args, $args );
	} 
	
	/* = SURCHARGE DE WP-LIST-TABLE = */
	/** == Préparation de la liste des éléments à afficher == **/
	public function prepare_items() {			
		// Récupération des items
		$this->items = call_user_func( $this->cb_get_items_query, $this->prepare_query_args() );
		
		// Définition des classes
		if( $this->items )
			array_map( array( $this, 'prepare_row_classes' ), $this->items );
		
		// Pagination
		$total_items 	= call_user_func( $this->cb_count_items_query, $this->prepare_query_args() );
		$per_page 		= $this->get_items_per_page( $this->per_page_option, $this->items_per_page );
		$this->set_pagination_args( 
			array(
            	'total_items' => $total_items,                  
            	'per_page'    => $per_page,                    
            	'total_pages' => ceil( $total_items / $per_page )
			) 
		);
	}
	
	/** == ORGANES DE NAVIGATION == **/
	/*** === Filtrage principal  === ***/
	public function get_views(){
		return $this->_get_views();
	}
	
	/** == Filtrage secondaire == **/
	protected function extra_tablenav( $which ) {}
			
	/* = COLONNES = */
	/** == Liste des colonnes == **/
	public function get_columns() {
		$c = array(
			'cb' => '<input type="checkbox" />'
		);	
		return $c;
	}
		
	/** == Définition de l'ordonnancement par colonne == **/
	public function get_sortable_columns() {
		$c = array(	
		);
		return $c;
	}
		
	/** == Contenu par défaut des colonnes == **/
	public function column_default( $item, $column_name ){
        switch( $column_name ) :
            default:
				if( isset( $item->{$column_name} ) )
					return $item->{$column_name};
			break;
		endswitch;
    }
	
	/** == Contenu d'une ligne == **/
	public function single_row( $item, $level = 0 ) {
	?>
		<tr id="<?php echo $this->get_row_id( $item );?>" class="<?php echo $this->get_row_classes( $item ); ?>">
			<?php $this->single_row_columns( $item ); ?>
		</tr>
	<?php
	}
	
	/** == Contenu personnalisé : Case à cocher == **/
	public function column_cb( $item ){
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->{$this->primary_key} );
    }
	
	/* = METHODES PUBLIQUES TIFY_ADMINVIEW_LIST_TABLE (SURCHARGE AUTORISEE) = */
	/** == Définition de l'attribut classe de la ligne relative à l'élément == **/
	protected function set_row_classes( $item, $classes = '' ){	}
	
	
	/* = AFFICHAGE = */
	/** == Affichage des messages de notifications == **/
	public function notifications(){
		$output = "";
		if ( $notifications = $this->current_notification() )
			foreach( $notifications as $i => $n )
				$output .= "<div id=\"{$n['type']}-{$i}\" class=\"notice notice-{$n['type']}". ($n['dismissible'] ? ' is-dismissible' : '' ) ."\"><p>{$n['message']}</p></div>";
			
		echo $output;
	}	
	
	/* = TRAITEMENT DES DONNÉES = */
	/*** === Éxecution des actions === ***/
	protected function process_bulk_action(){
		if( $this->current_action() && is_callable( array( $this, 'process_bulk_action_'. $this->current_action() ) ) ) :
			call_user_func( array( $this, 'process_bulk_action_'. $this->current_action() ) );
		/*elseif ( ! empty( $_REQUEST['_wp_http_referer'] ) ) :
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), $_REQUEST['_wp_http_referer'] ) );*/
		
		endif; 		
	}
	
	/** == Éxecution de l'action - suppression == **/
	protected function process_bulk_action_delete(){
		$item_id = $_GET[$this->item_request];	
		check_admin_referer( $this->action_prefix . $this->current_action() . $item_id );
		
		// Traitement de l'élément
		$this->db->delete_item( $item_id );
		if( $this->db->has_meta )
			$this->db->delete_item_metadatas( $item_id );
		
		// Traitement de la redirection
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'deleted', $sendback );	
		wp_redirect( $sendback );
	}
	
	/** == Éxecution de l'action - mise à la corbeille == **/
	protected function process_bulk_action_trash(){
		$item_id = (int) $_GET[$this->item_request];
		check_admin_referer( $this->action_prefix . $this->current_action() . $item_id );
			
		// Traitement de l'élément				
		/// Conservation du statut original
		if( $this->db->has_meta && ( $original_status = $this->db->get_item_var_by_id( $item_id, 'status' ) ) )
			$this->db->update_item_meta( $item_id, '_trash_meta_status', $original_status );					
		/// Modification du statut
		$this->db->update_item( $item_id, array( 'status' => 'trash' ) );
		
		// Traitement de la redirection
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'trashed', $sendback );									
		wp_redirect( $sendback );
	}
	
	/** == Éxecution de l'action - restauration d'élément à la corbeille == **/
	protected function process_bulk_action_untrash(){
		$item_id = (int) $_GET[$this->item_request];		
		check_admin_referer( $this->action_prefix . $this->current_action() . $item_id );
		
		// Traitement de l'élément				
		/// Récupération du statut original
		$original_status = ( $this->db->has_meta && ( $_original_status = $this->db->get_item_meta( $item_id, '_trash_meta_status', true ) ) ) ? $_original_status : 'draft';				
		if( $this->db->has_meta ) $this->db->delete_item_meta( $item_id, '_trash_meta_status' );
		/// Mise à jour du statut
		$this->db->update_item( $item_id, array( 'status' => $original_status ) );
		
		// Traitement de la redirection
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'untrashed', $sendback );	
		wp_redirect( $sendback );
	}	
}