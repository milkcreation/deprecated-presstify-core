<?php
namespace tiFy\Core\Admin\Model;

/** 
 * @see https://codex.wordpress.org/Class_Reference/WP_List_Table
 */
if( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH .'wp-admin/includes/class-wp-list-table.php' );
	
abstract class Table extends \WP_List_Table
{
	use \tiFy\Environment\Traits\Path;
	
	/* = ARGUMENTS = */
	// Classe de la vue
	public $View					= null;
	
	// Nom du modèle
	public $Name					= null;
	
	// Écran courant
	protected $Screen				= null;
	
	// Configuration
	/// Url de la page d'administration
	protected $BaseUri				= null;
	
	/// Url de la page d'édition d'un élément
	protected $EditBaseUri			= null;
	
	// Intitulé des objets traités
	protected $Plural				= null;
	
	// Intitulé d'un objet traité
	protected $Singular				= null;
	
	/// Message de notification
	protected $Notices				= array();
	
	/// Liste des statuts des objets traités
	protected $Statuses				= array();
	
	/// Liens de vue filtrées
	protected $FilteredViewLinks	= array();	
		
	/// Colonnes de la table
	protected $Columns				= array();
	
	/// Colonne principale de la table
	protected $PrimaryColumn		= null;
	
	/// Colonnes selon lesquelles les éléments peuvent être triés
	protected $SortableColumns		= array();
	
	/// Colonnes Masquées
	protected $HiddenColumns		= array();
	
	/// Nombre d'éléments affichés par page
	protected $PerPage				= null;
	
	/// 
	protected $PerPageOptionName	= null;
	
	/// Arguments de requête
	protected $QueryArgs			= array();
	
	/// Intitulé affiché lorsque la table est vide
	protected $NoItems				= array();
	
	/// Actions groupées
	protected $BulkActions			= array();
	
	/// Actions sur un élément
	protected $RowActions			= array();
	
	/// Cartographie des paramètres
	protected $ParamsMap			= array( 
		'BaseUri', 'EditBaseUri', 'Plural', 'Singular', 'Notices', 'Statuses', 'FilteredViewLinks', 
		'Columns', 'PrimaryColumn', 'SortableColumns', 'HiddenColumns', 'PerPage', 'PerPageOptionName',
		'QueryArgs', 'NoItems', 'BulkActions', 'RowActions'	
	);
	
	/* = CONSTRUCTEUR = */
	/** == ! IMPORTANT : court-circuitage du constructeur natif de WP_List_Table == **/
	public function __construct(){}
						
	/* = DECLARATION DES PARAMETRES = */
	/** == Définition l'url de la page d'édition d'un élément == **/
	public function set_edit_link()
	{
		return false;	
	}
	
	/** == Définition l'intitulé des objets traités == **/
	public function set_plural()
	{
		return null;	
	}
	
	/** == Définition l'intitulé d'un objet traité == **/
	public function set_singular()
	{
		return null;	
	}
	
	/** == Définition des messages de notification == **/
	public function set_notices()
	{
		return array();	
	}
	
	/** == Définition des status == **/
	public function set_statuses()
	{
		return array();
	}
	
	/** == Définition des vues filtrées == **/
	public function set_views()
	{
		return array();
	}
	
	/** == Définition des colonnes de la table == **/
	public function set_columns()
	{
		return array();
	}
	
	/** == Définition de la colonne principale == **/
	public function set_primary_column()
	{
		return null;
	}
	
	/** == Définition des colonnes pouvant être ordonnées == **/
	public function set_sortable_columns()
	{
		return array();
	}
	
	/** == Définition des colonnes masquées == **/
	public function set_hidden_columns()
	{
		return array();
	}
	
	/** == Définition des arguments de requête == **/
	public function set_query_args()
	{
		return array();
	}
		
	/** == Définition du nombre d'élément à afficher par page == **/
	public function set_per_page()
	{
		return 0;
	}
	
	/** == Définition du nombre d'élément à afficher par page == **/
	public function set_per_page_option_name()
	{
		return true;
	}
		
	/** == Définition de l'intitulé lorque la table est vide == **/
	public function set_no_items()
	{
		return '';
	}
	
	/** == Définition des actions groupées == **/
	public function set_bulk_actions()
	{
		return array();
	}
	
	/** == Définition des actions sur un élément == **/
	public function set_row_actions()
	{
		return array();
	}
	
	/** == Définition de l'ajout automatique des actions de l'élément pour la colonne principale == **/
	public function set_handle_row_actions()
	{
		return true;
	}
	
	/* = INITIALISATION DES PARAMETRES = */
	/** == Initialisation des paramètres de configuration de la table == **/
	protected function init_params()
	{
		foreach( (array) $this->ParamsMap as $param ) :
			if( ! method_exists( $this, 'init_param_' . $param ) ) 
				continue;
			call_user_func( array( $this, 'init_param_' . $param ) );
		endforeach;
	}
	
	/** == Initialisation de l'url de la page d'administration == **/
	public function init_param_BaseUri()
	{
		$this->BaseUri = $this->View->getModelAttrs( 'base_url', $this->Name );
	}
	
	/** == Initialisation de l'url d'édition d'un élément == **/
	public function init_param_EditBaseUri()
	{
		$this->EditBaseUri = $this->View->getModelAttrs( 'base_url', 'EditForm' );
	}
	
	/** == Initialisation de l'intitulé des objets traités == **/
	public function init_param_Plural()
	{
		if( ! $plural = $this->set_plural() )
			$plural = $this->View->getID();
		
		$this->Plural = sanitize_key( $plural );
	}
	
	/** == Initialisation de l'intitulé d'un objet traité == **/
	public function init_param_Singular()
	{
		if( ! $singular = $this->set_singular() )
			$singular = $this->View->getID();
		
		$this->Singular = sanitize_key( $singular );
	}
	
	/** == Initialisation des notifications == **/
	public function init_param_Notices()
	{
		$this->Notices = \tiFy\Core\Admin\Helpers::ListTableNoticesMap( $this->set_notices() );
	}
	
	/** == Initialisation des statuts == **/
	public function init_param_Statuses()
	{
		$this->Statuses = $this->set_statuses();
	}
	
	/** == Initialisation des vues filtrées == **/
	public function init_param_FilteredViewLinks()
	{
		$views = $this->set_views();
		
		foreach( $views as &$attrs ) :
			if( is_string( $attrs ) )
				continue;
			if( ! isset( $attrs['base_uri' ] ) )
				$attrs['base_uri'] = $this->BaseUri;
		endforeach;
			
		$this->FilteredViewLinks = \tiFy\Core\Admin\Helpers::ListTableFilteredViewsMap( $views );
	}
	
	/** == Initialisation des colonnes de la table == **/
	public function init_param_Columns()
	{			
		if( $columns = $this->set_columns() ) :
		elseif( $columns = $this->View->getModelAttrs( 'columns', $this->Name ) ) :
		else :
			$columns['cb'] = "<input id=\"cb-select-all-1\" type=\"checkbox\" />";
			foreach( (array)  $this->View->getDb()->ColNames as $name ) :
				$columns[$name] = $name;
			endforeach;
		endif;
		$this->Columns = $columns;
	}
	
	/** == Initialisation de la colonne principale == **/
	public function init_param_PrimaryColumn()
	{
		if( $primary = $this->set_primary_column() ) :
		elseif( $primary = $this->View->getModelAttrs( 'primary_column', $this->Name ) ) :
		else :
			$primary = null;
		endif;

		if( $primary ) :
			$this->PrimaryColumn = $primary;
			add_filter( 'list_table_primary_column', function( $default, $screen_id ) use ( $primary ){ return $primary; }, 10, 2 );
		endif;
	}
	
		/** == Initialisation des colonnes masquée == **/
	public function init_param_HiddenColumns()
	{
		if( $hidden_cols = $this->set_hidden_columns() ) :
		elseif( $hidden_cols = $this->View->getModelAttrs( 'hidden_columns', $this->Name ) ) :
		else :
			$hidden_cols = array();
		endif;

		if( $hidden_cols ) :
			$this->HiddenColumns = $hidden_cols;
			add_filter( 'hidden_columns', function( $hidden, $screen, $use_defaults ) use ( $hidden_cols ){ return $hidden_cols; }, 10, 3 );
		endif;
	}
	
	/** == Initialisation des arguments de requête == **/
	public function init_param_QueryArgs()
	{
		$this->QueryArgs = (array) $this->set_query_args();
	}
	
	/** == Initialisation du nombre d'éléments affichés par page == **/
	public function init_param_PerPage()
	{
		$this->PerPage = ( $per_page = (int) $this->set_per_page() ) ? $per_page : 20;	
	}
	
	/** == == **/
	public function init_param_PerPageOptionName()
	{
		if( ! $per_page_option = $this->set_per_page_option_name() )
			return;
			
		$per_page_option = is_bool( $per_page_option ) ? $this->View->getID() .'_per_page' : (string) $per_page_option;
		add_filter( 'set-screen-option', function( $none, $option, $value ) use ( $per_page_option ){ return ( $per_page_option  ===  $option ) ? $value : $none; }, 10, 3 );
		$per_page = $this->PerPage;
		add_filter( $this->PerPageOptionName, function() use ( $per_page ){ return $per_page; }, 0 );
	}
	
	/** == Initialisation de l'intitulé lorsque la table est vide == **/
	public function init_param_NoItems()
	{
		$this->NoItems = ( $no_items = $this->set_no_items() ) ? $no_items :  ( ( $no_items = $this->View->getLabel( 'not_found' ) ) ? $no_items : __( 'No items found.' ) );	
	}
	
	/** == Initialisation des actions groupées == **/
	public function init_param_BulkActions()
	{
		$this->BulkActions = $this->set_bulk_actions();	
	}
	
	/** == Initialisation des actions sur un élément de la liste == **/
	public function init_param_RowActions()
	{
		foreach( (array) $this->set_row_actions() as $action => $attr ) :
			if( is_int( $action ) ) :
				$this->RowActions[$attr] = array();
			else :
				$this->RowActions[$action] = $attr;
			endif;
		endforeach;	
	}
	
	/** == Initialisation de la classe table native de Wordpress == **/
	final public function _wp_list_table_init( $args = array() )
	{
		parent::__construct(
			wp_parse_args(
				$args,
				array(
					'plural' 	=> $this->Plural,
					'singular' 	=> $this->Singular,
					'ajax' 		=> true,
					'screen' 	=> null
				)
			)			 
		);
	}
	
	/* = DECLENCHEURS = */
	/** == Affichage de l'écran courant == **/
	final public function _current_screen( $current_screen = null )
	{	
		// Définition de l'écran courant
		if( $current_screen )
			$this->Screen = $current_screen;
				
		// Initialisation des paramètres de configuration de la table
		$this->init_params();
		
		// Initialisation de la classe de table native de Wordpress
		$args = array();
		if( $this->Screen )
			$args = array( 'screen' => $this->Screen->id );
		$this->_wp_list_table_init( $args );
		
		// Activation de l'interface de gestion du nombre d'éléments par page
		if( $this->Screen )
			$this->Screen->add_option(
				'per_page',
				array(
						'option' => $this->PerPageOptionName
				)
			);
				
		// Traitement
		/// Exécution des actions
		$this->process_bulk_actions();
		
		/// Affichage des messages de notification
		foreach( (array) $this->Notices as $nid => $nattr ) :
			if( ! isset( $_REQUEST[ $nattr['query_arg'] ] ) || ( $_REQUEST[ $nattr['query_arg'] ] !== $nid ) )
				continue;

			add_action( 'admin_notices', function() use( $nattr ){
			?>
				<div class="notice notice-<?php echo $nattr['notice'];?><?php echo $nattr['dismissible'] ? ' is-dismissible':'';?>">
		        	<p><?php echo $nattr['message'] ?></p>
		    	</div>
		    <?php
		    			
			});
		endforeach;
		
		/// Récupération des éléments à afficher
		$this->prepare_items();
	}
				
	/* = TRAITEMENT = */
	/** == Récupération de l'élément à traité == **/
	public function current_item() 
	{
		if ( ! empty( $_REQUEST[$this->View->getDb()->Primary] ) ) :
			if( is_array( $_REQUEST[$this->View->getDb()->Primary] ) )
				return array_map('intval', $_REQUEST[$this->View->getDb()->Primary] );
			else 
				return array( (int) $_REQUEST[$this->View->getDb()->Primary] );
		endif;
		
		return 0;
	}
	
	/** == Récupération de l'action courante == **/
	public function current_action() 
	{		
		if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
			return $_REQUEST['action'];
		if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
			return $_REQUEST['action2'];

		return false;
	}
	
	/** == Récupération des éléments == **/
	public function prepare_items() 
	{				
		// Récupération des items
		$query = $this->View->getDb()->query( $this->parse_query_args() );
		$this->items = $query->items;
		
		// Pagination
		$total_items 	= $query->found_items;
		$per_page 		= $this->get_items_per_page( $this->View->getDb()->Name, $this->PerPage );
		$this->set_pagination_args( 
			array(
            	'total_items' 		=> $total_items,                  
            	'per_page'    		=> $per_page,                    
            	'total_pages' 		=> ceil( $total_items / $per_page )
			) 
		);
	}
	
	/** == Traitement des arguments de requête == **/
	public function parse_query_args()
	{
		// Récupération des arguments		
		$per_page 	= $this->get_items_per_page( $this->View->getDb()->Name, $this->PerPage );
		$paged 		= $this->get_pagenum();

		// Arguments par défaut
		$query_args = array(						
			'per_page' 	=> $per_page,
			'paged'		=> $paged,
			'order'		=> 'DESC',
			'orderby'	=> $this->View->getDb()->Primary
		);
			
		// Traitement des arguments
		foreach( (array) $_REQUEST as $key => $value ) :
			if( method_exists( $this, 'parse_query_arg_' . $key ) ) :
				 call_user_func_array( array( $this, 'parse_query_arg_' . $key ), array( &$query_args, $value ) );
			elseif( $this->View->getDb()->isCol( $key ) ) :
				$query_args[$key] = $value;
			endif;
		endforeach;

		return wp_parse_args( $this->QueryArgs, $query_args );
	}
	
	/** == Traitement de l'argument de requête de recherche == **/
	public function parse_query_arg_s( &$query_args, $value )
	{
		if( ! empty( $value ) )
			$query_args['s'] = wp_unslash( trim( $value ) );
	}
		
	/** == Compte le nombre d'éléments == **/
	public function count_items( $args = array() )
	{
		return $this->View->getDb()->select()->count( $args );
	}
	
	/** == Récupération des actions sur un élément == **/
	public function item_row_actions( $item, $actions = array() )
	{		
		$row_actions = array();
		foreach( (array) $actions as $action ) :
			if( ! isset( $this->RowActions[$action] ) )
				continue;
			if( is_string( $this->RowActions[$action] ) ) :
				$row_actions[$action] = $this->RowActions[$action];
			else :
				$args = $this->item_row_actions_parse_args( $item, $action, $this->RowActions[$action] );
				$row_actions[$action] = \tiFy\Core\Admin\Helpers::RowActionLink( $action, $args );
			endif;
		endforeach;		
				
		return $row_actions;		
	}
	
	/** == Traitement des arguments des actions sur un élément == **/
	public function item_row_actions_parse_args( $item, $action, $args = array() )
	{		
		$defaults = array(
			'edit'		=> $this->get_item_edit_args( $item, array(), __( 'Modifier' ) ),
		);
		
		if( $this->View->getDb() ) :
			$defaults += array(
				'delete'	=> array(
					'label'		=> __( 'Supprimer définitivement', 'tify' ),
					'title'		=>  __( 'Suppression définitive de l\'élément', 'tify' ),
					'nonce'		=> $this->get_item_nonce_action( 'delete', $item->{$this->View->getDb()->Primary} )
				),
				'trash'		=> array(
					'label'		=> __( 'Corbeille', 'tify' ),
					'title'		=> __( 'Mise à la corbeille de l\'élément', 'tify' ),
					'nonce'		=> $this->get_item_nonce_action( 'trash', $item->{$this->View->getDb()->Primary} )
				),
				'untrash'	=> array(
					'label'		=> __( 'Restaurer', 'tify' ),
					'title'		=> __( 'Rétablissement de l\'élément', 'tify' ),
					'nonce'		=> $this->get_item_nonce_action( 'untrash', $item->{$this->View->getDb()->Primary} )
				),
				'duplicate'	=> array(
					'label'		=> __( 'Dupliquer', 'tify' ),
					'title'		=> __( 'Dupliquer l\'élément', 'tify' ),
					'nonce'		=> $this->get_item_nonce_action( 'duplicate', $item->{$this->View->getDb()->Primary} )
				)
			);
		endif;
		
		if( isset( $defaults[$action] ) )
			$args = wp_parse_args( $args, $defaults[$action] );
		
		if( ! isset( $args['base_uri'] ) )
			$args['base_uri'] = $this->BaseUri;
		
		if( $this->View->getDb() && ! isset( $args['query_args'][$this->View->getDb()->Primary] ) )
			$args['query_args'][$this->View->getDb()->Primary] = $item->{$this->View->getDb()->Primary};
		
		return $args;
	}
	
	/** == Éxecution des actions == **/
	protected function process_bulk_actions()
	{
		// Traitement des actions
		if( ! $this->current_item() ) :
			return;
		elseif( method_exists( $this, 'process_bulk_action_'. $this->current_action() ) ) :
			call_user_func( array( $this, 'process_bulk_action_'. $this->current_action() ) );
		elseif( ! empty( $_REQUEST['_wp_http_referer'] ) ) :
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), $_REQUEST['_wp_http_referer'] ) );
			exit;
		endif; 		
	}
	
	/** == Éxecution de l'action - suppression == **/
	protected function process_bulk_action_delete()
	{
		$item_ids = $this->current_item();

		// Vérification des permissions d'accès
		if( ! wp_verify_nonce( @$_REQUEST['_wpnonce'], 'bulk-'. $this->Plural ) ) :
			check_admin_referer( $this->get_item_nonce_action( 'delete', reset( $item_ids ) ) );
		endif;
		
		// Traitement de l'élément
		foreach( (array) $item_ids as $item_id ) :
			$this->View->getDb()->handle()->delete_by_id( $item_id );
			if( $this->View->getDb()->meta() )
				$this->View->getDb()->meta()->delete_all( $item_id );
		endforeach;
		
		// Traitement de la redirection
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'deleted', $sendback );	
		
		wp_redirect( $sendback );
		exit;
	}
	
	/** == Éxecution de l'action - mise à la corbeille == **/
	protected function process_bulk_action_trash()
	{
		$item_ids = $this->current_item();
		
		// Vérification des permissions d'accès
		if( ! wp_verify_nonce( @$_REQUEST['_wpnonce'], 'bulk-'. $this->Plural ) ) :
			check_admin_referer( $this->get_item_nonce_action( 'trash', reset( $item_ids ) ) );
		endif;
		
		// Bypass
		if( ! $this->View->getDb()->isCol( 'status' ) )
			return;
		
		// Traitement de l'élément
		foreach( (array) $item_ids as $item_id ) :
			/// Conservation du statut original
			if( $this->View->getDb()->meta() && ( $original_status = $this->View->getDb()->select()->cell_by_id( $item_id, 'status' ) ) )
				$this->View->getDb()->meta()->update( $item_id, '_trash_meta_status', $original_status );					
			/// Modification du statut
			$this->View->getDb()->handle()->update( $item_id, array( 'status' => 'trash' ) );
		endforeach;
			
		// Traitement de la redirection
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'trashed', $sendback );
											
		wp_redirect( $sendback );
		exit;
	}
	
	/** == Éxecution de l'action - restauration d'élément à la corbeille == **/
	protected function process_bulk_action_untrash()
	{
		$item_ids = $this->current_item();	
		
		// Vérification des permissions d'accès
		if( ! wp_verify_nonce( @$_REQUEST['_wpnonce'], 'bulk-'. $this->Plural ) ) :
			check_admin_referer( $this->get_item_nonce_action( 'untrash', reset( $item_ids ) ) );
		endif;
		
		// Bypass
		if( ! $this->View->getDb()->isCol( 'status' ) )
			return;
		
		// Traitement de l'élément
		foreach( (array) $item_ids as $item_id ) :
			/// Récupération du statut original
			$original_status = ( $this->View->getDb()->meta() && ( $_original_status = $this->View->getDb()->meta()->get( $item_id, '_trash_meta_status', true ) ) ) ? $_original_status : $this->View->getDb()->getColAttr( 'status', 'default' );				
			if( $this->View->getDb()->meta() ) $this->View->getDb()->meta()->delete( $item_id, '_trash_meta_status' );
			/// Mise à jour du statut
			$this->View->getDb()->handle()->update( $item_id, array( 'status' => $original_status ) );
		endforeach;
			
		// Traitement de la redirection
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'untrashed', $sendback );
			
		wp_redirect( $sendback );
		exit;
	}
	
	/* = HELPERS = */
	/** == Récupération de l'intitulé d'un statut == **/
	public function get_status( $status, $singular = true )
	{
		return \tiFy\Core\Admin\Helpers::getStatus( $status, $singular, $this->Statuses );
	}
	
	/** == Récupération du titre la colonne de selection multiple == **/
	public function get_cb_column_header()
	{
		return "<input id=\"cb-select-all-1\" type=\"checkbox\" />";
	}
	
	/** == Récupération des actions sur un élément == **/
	public function get_row_actions( $item, $actions, $always_visible = false )
	{
		return $this->row_actions( $this->item_row_actions( $item, $actions ), $always_visible );
	}
	
	/** == Récupération de l'attribut de sécurisation d'une action == **/
	public function get_item_nonce_action( $action, $suffix = null )
	{
		$nonce_action = $this->Singular . $action;
		
		if( isset( $suffix ) )
			$nonce_action .= $suffix;
		
		return $nonce_action;
	}
		
	/** == Lien d'édition d'un élément == **/
	public function get_item_edit_link( $item, $args = array(), $label, $class = '' ) 
	{
		if( $args = $this->get_item_edit_args( $item, $args, $label, $class ) )
			return \tiFy\Core\Admin\Helpers::RowActionLink( 'edit', $args );
	}
	
	/** == Arguments d'édition d'un élément == **/
	public function get_item_edit_args( $item, $args = array(), $label, $class = '' ) 
	{
		if( $base_uri = $this->EditBaseUri )
			return array(
				'label'			=> $label,
				'class'			=> $class,
				'base_uri'		=> $base_uri,
				'query_args'	=> array_merge( $args, array( $this->View->getDb()->Primary => $item->{$this->View->getDb()->Primary} ) ),
				'nonce'			=> false,
				'referer'		=> false
			);
	}
	
	/* = INTERFACE D'AFFICHAGE = * /
	/** == Récupération des vues filtrées == **/
	public function get_views()
	{		
		return $this->FilteredViewLinks;
	}
	
	/** == Récupération des vues actions groupées == **/
	public function get_bulk_actions() 
	{
		return $this->BulkActions;
	}
	
	/** == Récupération des colonnes de la table == **/
	public function get_columns() 
	{
		return $this->Columns;
	}
	
	/** == Récupération des colonnes selon lesquelles les éléments peuvent être triés == **/
	public function get_sortable_columns()
	{
		return $this->SortableColumns;
	}
		
	/** == == **/
	public function no_items() 
	{
		echo $this->NoItems;
	}
					
	/** == Ajout automatique des actions sur l'élément de la colonne principal == **/
	public function handle_row_actions( $item, $column_name, $primary ) 
	{
		if ( ( $primary !== $column_name ) || ! $this->set_handle_row_actions() )
			return;
		
		return $this->get_row_actions( $item, array_keys( $this->RowActions ) );
	}
	
	/* = AFFICHAGE = */		
	/** == Vues filtrées == **/
	public function views() 
	{
		$views = $this->get_views();
		$views = apply_filters( "views_{$this->screen->id}", $views );

		if ( empty( $views ) )
			return;

		$this->screen->render_screen_reader_content( 'heading_views' );
		
		$_views = array();
		$output  = "";
		$output .= "<ul class='subsubsub'>\n";
		foreach ( (array) $views as $class => $view ) :
			if( ! $view )
				continue;
				
			$_views[ $class ] = "\t<li class='$class'>$view";
		endforeach;
		
		$output .= implode( " |</li>\n", $_views ) . "</li>\n";
		$output .= "</ul>";
		
		if( ! empty( $_views ) )
			echo $output;		
	}
				
	/** == Contenu des colonnes par défaut == **/
	public function column_default( $item, $column_name )
	{
		// Bypass 
		if( ! isset( $item->{$column_name} ) )
			return;
		
		$col_type = strtoupper( $this->View->getDb()->getColAttr( $column_name, 'type' ) );

        switch( $col_type ) :
            default:
        		if( is_array( $item->{$column_name} ) ) :
        			return join( ', ', $item->{$column_name} );
        		else :	
					return $item->{$column_name};
        		endif;
				break;
			case 'DATETIME' :
				return mysql2date( get_option( 'date_format') .' @ '.get_option( 'time_format' ), $item->{$column_name} );
				break;
		endswitch;
    }
	
	/** == Contenu de la colonne Case à cocher == **/
	public function column_cb( $item )
	{
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->View->getDb()->Primary, $item->{$this->View->getDb()->Primary} );
    }
    
    /** == Rendu de la page  == **/
    public function Render()
    {
    ?>
		<div class="wrap">
    		<h2>
    			<?php echo $this->View->getLabel( 'all_items' );?>
    			
    			<?php if( $this->EditBaseUri ) : ?>
    				<a class="add-new-h2" href="<?php echo $this->EditBaseUri;?>"><?php echo $this->View->getLabel( 'add_new' );?></a>
    			<?php endif;?>
    			
    			<?php if( $this->View->getModelAttrs( 'base_url', 'Import' ) ) : ?>
    				<a class="add-new-h2" href="<?php echo $this->View->getModelAttrs( 'base_url', 'Import' );?>"><?php echo $this->View->getLabel( 'import_items' );?></a>
    			<?php endif;?>
    		</h2>
    		
    		<?php $this->views(); ?>
    		
    		<form method="post" action="<?php echo $this->View->getModelAttrs( 'base_url', $this->Name );?>">
    			<?php $this->search_box( $this->View->getLabel( 'search_items' ), $this->View->getID() );?>
    			<?php $this->display();?>
			</form>
    	</div>
    <?php
    }
}