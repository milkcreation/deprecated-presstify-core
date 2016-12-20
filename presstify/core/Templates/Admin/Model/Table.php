<?php
namespace tiFy\Core\Templates\Admin\Model;

use \tiFy\Core\Templates\Admin\Helpers;

/** 
 * @see https://codex.wordpress.org/Class_Reference/WP_List_Table
 */
if( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH .'wp-admin/includes/class-wp-list-table.php' );
	
abstract class Table extends \WP_List_Table
{
	use \tiFy\Environment\Traits\Path;
	use \tiFy\Core\Templates\Traits\Table\Actions;
	use \tiFy\Core\Templates\Traits\Table\Notices;
	use \tiFy\Core\Templates\Traits\Table\Params;
	use \tiFy\Core\Templates\Traits\Table\Views;
	
	/* = ARGUMENTS = */	
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
	protected $NoItems				= '';
	
	/// Actions groupées
	protected $BulkActions			= array();
	
	/// Actions sur un élément
	protected $RowActions			= array();
	
	/// Titre de la page
	protected $PageTitle			= null;
			
	/// Cartographie des paramètres
	protected $ParamsMap			= array( 
		'BaseUri', 'EditBaseUri', 'Plural', 'Singular', 'Notices', 'Statuses', 'FilteredViewLinks', 
		'Columns', 'PrimaryColumn', 'SortableColumns', 'HiddenColumns', 'PerPage', 'PerPageOptionName',
		'QueryArgs', 'NoItems', 'BulkActions', 'RowActions',
		'PageTitle'
	);
	
	protected $compat_fields = array( 
		'_args', '_pagination_args', 'screen', '_actions', '_pagination',  
		'template', 'db', 'label', 'getConfig' 
	);

	
	/* = CONSTRUCTEUR = */
	/** == ! IMPORTANT : court-circuitage du constructeur natif de WP_List_Table == **/
	public function __construct(){}
	
	/* = METHODES MAGIQUES = */
	/** == Appel des méthodes dynamiques == **/
    final public function __call( $name, $arguments )
    {
        if( in_array( $name, array( 'template', 'db', 'label', 'getConfig' ) ) ) :
    		return call_user_func_array( $this->{$name}, $arguments );
        else :
        	parent::__call( $name, $arguments );
        endif;
    }	

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
	
	/** == Définition du titre de la page == **/
	public function set_page_title()
	{
		return '';
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
	/** == initialisation globale == **/
	final public function _init()
	{
		// Pré-initialisation des paramètres
		/// Option de personnalisation du nombre d'élément par page			
		$this->PerPageOptionName = $per_page_option_name = sanitize_key( $this->template()->getID() .'_per_page' );
		//// Permettre l'enregistrement : @see set_screen_options -> wp-admin/includes/misc.php
		add_filter( 
			'set-screen-option', 
			function( $none, $option, $value ) use ( $per_page_option_name ){ 
				return ( $per_page_option_name  ===  $option ) ? $value : $none; 
			}, 
			10, 
			3 
		);
		
		/// Nombre d'éléments par page
		if( $per_page = (int) get_user_option( $this->PerPageOptionName ) ) :
		elseif( $per_page = (int) $this->getConfig( 'per_page' ) ) :
		elseif( $per_page = (int) $this->set_per_page() ) :
		else :
			$per_page = 20; 
		endif;
		$this->PerPage = $per_page;	
		//// Définition de la valeur du nombre d'éléments par page
		add_filter( $this->PerPageOptionName, function() use ( $per_page ){ return $per_page; }, 0 );
	}
	
	/** == Affichage de l'écran courant == **/
	final public function _current_screen( $current_screen = null )
	{	
		// Définition de l'écran courant
		if( $current_screen )
			$this->Screen = $current_screen;
				
		// Initialisation des paramètres de configuration de la table
		$this->initParams();	
		
		// Initialisation de la classe de table native de Wordpress
		$args = array();
		if( $this->Screen )
			$args = array( 'screen' => $this->Screen->id );
		$this->_wp_list_table_init( $args );
		
		// Activation de l'interface de gestion du nombre d'éléments par page
		if( $this->Screen ) :
			$this->Screen->add_option(
				'per_page',
				array(
					'option' => $this->PerPageOptionName
				)
			);			
		endif;
	
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
		if ( ! empty( $_REQUEST[$this->db()->Primary] ) ) :
			if( is_array( $_REQUEST[$this->db()->Primary] ) )
				return array_map('intval', $_REQUEST[$this->db()->Primary] );
			else 
				return array( (int) $_REQUEST[$this->db()->Primary] );
		endif;
		
		return 0;
	}
		
	/** == Récupération des éléments == **/
	public function prepare_items() 
	{				
		// Récupération des items
		$query = $this->db()->query( $this->parse_query_args() );
		$this->items = $query->items;
		
		// Pagination
		$total_items 	= $query->found_items;
		$per_page 		= $this->get_items_per_page( $this->db()->Name, $this->PerPage );
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
		$per_page 	= $this->get_items_per_page( $this->db()->Name, $this->PerPage );
		$paged 		= $this->get_pagenum();

		// Arguments par défaut
		$query_args = array(						
			'per_page' 	=> $per_page,
			'paged'		=> $paged,
			'order'		=> 'DESC',
			'orderby'	=> $this->db()->Primary
		);
			
		// Traitement des arguments
		foreach( (array) $_REQUEST as $key => $value ) :
			if( method_exists( $this, 'parse_query_arg_' . $key ) ) :
				 call_user_func_array( array( $this, 'parse_query_arg_' . $key ), array( &$query_args, $value ) );
			elseif( $this->db()->isCol( $key ) ) :
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
		return $this->db()->select()->count( $args );
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
				$row_actions[$action] = Helpers::RowActionLink( $action, $args );
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
		
		if( $this->db() ) :
			$defaults += array(
				'delete'	=> array(
					'label'		=> __( 'Supprimer définitivement', 'tify' ),
					'title'		=>  __( 'Suppression définitive de l\'élément', 'tify' ),
					'nonce'		=> $this->get_item_nonce_action( 'delete', $item->{$this->db()->Primary} )
				),
				'trash'		=> array(
					'label'		=> __( 'Corbeille', 'tify' ),
					'title'		=> __( 'Mise à la corbeille de l\'élément', 'tify' ),
					'nonce'		=> $this->get_item_nonce_action( 'trash', $item->{$this->db()->Primary} )
				),
				'untrash'	=> array(
					'label'		=> __( 'Restaurer', 'tify' ),
					'title'		=> __( 'Rétablissement de l\'élément', 'tify' ),
					'nonce'		=> $this->get_item_nonce_action( 'untrash', $item->{$this->db()->Primary} )
				),
				'duplicate'	=> array(
					'label'		=> __( 'Dupliquer', 'tify' ),
					'title'		=> __( 'Dupliquer l\'élément', 'tify' ),
					'nonce'		=> $this->get_item_nonce_action( 'duplicate', $item->{$this->db()->Primary} )
				)
			);
		endif;
		
		if( isset( $defaults[$action] ) )
			$args = wp_parse_args( $args, $defaults[$action] );
		
		if( ! isset( $args['base_uri'] ) )
			$args['base_uri'] = $this->BaseUri;
		
		if( $this->db() && ! isset( $args['query_args'][$this->db()->Primary] ) )
			$args['query_args'][$this->db()->Primary] = $item->{$this->db()->Primary};
		
		return $args;
	}
	
	/** == Éxecution des actions == **/
	protected function process_bulk_actions()
	{
		if( defined( 'DOING_AJAX' ) && ( DOING_AJAX === true ) )
			return;
		
		if( method_exists( $this, 'process_bulk_action_'. $this->current_action() ) ) :
			call_user_func( array( $this, 'process_bulk_action_'. $this->current_action() ) );
		elseif( ! empty( $_REQUEST['_wp_http_referer'] ) ) :
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
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
			$this->db()->handle()->delete_by_id( $item_id );
			if( $this->db()->meta() )
				$this->db()->meta()->delete_all( $item_id );
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
		if( ! $this->db()->isCol( 'status' ) )
			return;
		
		// Traitement de l'élément
		foreach( (array) $item_ids as $item_id ) :
			/// Conservation du statut original
			if( $this->db()->meta() && ( $original_status = $this->db()->select()->cell_by_id( $item_id, 'status' ) ) )
				$this->db()->meta()->update( $item_id, '_trash_meta_status', $original_status );					
			/// Modification du statut
			$this->db()->handle()->update( $item_id, array( 'status' => 'trash' ) );
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
		if( ! $this->db()->isCol( 'status' ) )
			return;
		
		// Traitement de l'élément
		foreach( (array) $item_ids as $item_id ) :
			/// Récupération du statut original
			$original_status = ( $this->db()->meta() && ( $_original_status = $this->db()->meta()->get( $item_id, '_trash_meta_status', true ) ) ) ? $_original_status : $this->db()->getColAttr( 'status', 'default' );				
			if( $this->db()->meta() ) $this->db()->meta()->delete( $item_id, '_trash_meta_status' );
			/// Mise à jour du statut
			$this->db()->handle()->update( $item_id, array( 'status' => $original_status ) );
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
		return Helpers::getStatus( $status, $singular, $this->Statuses );
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
			return Helpers::RowActionLink( 'edit', $args );
	}
	
	/** == Arguments d'édition d'un élément == **/
	public function get_item_edit_args( $item, $args = array(), $label, $class = '' ) 
	{
		if( $base_uri = $this->EditBaseUri )
			return array(
				'label'			=> $label,
				'class'			=> $class,
				'base_uri'		=> $base_uri,
				'query_args'	=> array_merge( $args, array( $this->db()->Primary => $item->{$this->db()->Primary} ) ),
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
		$views = apply_filters( "views_{$this->Screen->id}", $views );

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
		
		$col_type = strtoupper( $this->db()->getColAttr( $column_name, 'type' ) );

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
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->db()->Primary, $item->{$this->db()->Primary} );
    }
    
    /** == Rendu de la page  == **/
    public function render()
    {
    ?>
		<div class="wrap">
    		<h2>
    			<?php echo $this->PageTitle;?>
    			
    			<?php if( $this->EditBaseUri ) : ?>
    				<a class="add-new-h2" href="<?php echo $this->EditBaseUri;?>"><?php echo $this->label( 'add_new' );?></a>
    			<?php endif;?>
    		</h2>
    		
    		<?php $this->views(); ?>
    		
    		<form method="get" action="">
    			<?php parse_str( parse_url( $this->BaseUri, PHP_URL_QUERY ), $query_vars ); ?>
    			<?php foreach( (array) $query_vars as $name => $value ) : ?>
    				<input type="hidden" name="<?php echo $name;?>" value="<?php echo $value;?>" />
    			<?php endforeach;?>
    		
    			<?php $this->search_box( $this->label( 'search_items' ), $this->template()->getID() );?>
    			<?php $this->display();?>
			</form>
    	</div>
    <?php
    }
}