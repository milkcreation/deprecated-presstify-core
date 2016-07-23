<?php
namespace tiFy\Core\View\Admin\ListTable;

/** 
 * @see https://codex.wordpress.org/Class_Reference/WP_List_Table
 */
if( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH .'wp-admin/includes/class-wp-list-table.php' );
	
class ListTable extends \WP_List_Table
{
	use \tiFy\Environment\Traits\Path;
	
	/* = ARGUMENTS = */
	// Classe de la vue
	protected $View		= null;
	
	/// Arguments de la table liste Wordpress
	public	$plural		= '',
			$singular	= '',
			$ajax		= false,
	
			/// Nombre d'éléments par page
			$per_page				=	20,
			/// Vues Filtrées
			$views					= array(),
			/// Actions sur un élément
			$actions				= array(),
			
			// PARAMETRES
			/// Liste des argument des éléments
			$items,
			/// Vues filtrées à afficher
			$_views					= array(),
			/// Notifications
			$notifications,
			/// Arguments de requête de récupération
			$prepare_query_args;
	
	/* = CONSTRUCTEUR = */			
	public function __construct( \tiFy\Core\View\Factory $viewObj )
	{
		if( is_null( $this->View ) )
			$this->View = $viewObj;
	}
			
	/* = DECLENCHEURS = */
	/** == Initialisation global de l'interface d'administration == **/
	final public function _admin_init()
	{
		if( method_exists( $this, 'admin_init' ) ) :
			call_user_func( array( $this, 'admin_init' ) );
		endif;
	}
	
	/** == Affichage de l'écran courant == **/
	final public function _current_screen( $current_screen )
	{
		$this->_wp_list_table_init();
		
		$this->process_bulk_actions();
		
		if( method_exists( $this, 'current_screen' ) ) :
			call_user_func( array( $this, 'current_screen' ), $current_screen );
		endif;
		
		// Définition des notifications
		$this->set_notifications();
		
		// Définition des vues filtrées
		$this->set_views();
	}
	
	/** == Mise en file des scripts de l'interface d'administration == **/
	final public function _admin_enqueue_scripts()
	{
		if( method_exists( $this, 'admin_enqueue_scripts' ) ) :
			call_user_func( array( $this, 'admin_enqueue_scripts' ) );
		endif;
	}
	
	/** == Initialisation de la classe table liste Wordpress == **/
	final public function _wp_list_table_init( $args = array() )
	{
		parent::__construct(
			wp_parse_args(
				$args,
				array(
					'plural' 	=> $this->plural 	? $this->plural : '',
					'singular' 	=> $this->singular 	? $this->singular : '',
					'ajax' 		=> $this->ajax 		? true : false,
					'screen' 	=> null
				)
			)			 
		);
	}
		
	/* = TRAITEMENT DES ARGUMENTS DE REQUETE = */
	/** == Récupération de l'élément à traité == **/
	public function current_item() 
	{
		if ( ! empty( $_REQUEST[$this->View->getDb()->Primary] ) ) :
			return (int) $_REQUEST[$this->View->getDb()->Primary];
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
	
	/** == Récupération de la notification courante == **/
	public function current_notification()
	{
		if( ! empty( $_REQUEST['message'] ) && isset( $this->notifications[$_REQUEST['message']] ) )
			return array( wp_parse_args( $this->notifications[$_REQUEST['message']], array( 'message' => '', 'type' => 'error', 'dismissible' => false ) ) );		
	}
	
	/** == Traitement des arguments de requête == **/
	public function parse_request()
	{
		// Récupération des arguments		
		$s 			= isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
		$per_page 	= $this->get_items_per_page( $this->View->getDb()->Name, $this->per_page );
		$paged 		= $this->get_pagenum();

		// Arguments par défaut
		$args = array(						
			'per_page' 	=> $per_page,
			'paged'		=> $paged,
			's' 		=> $s,
			'order'		=> 'DESC',
			'orderby'	=> $this->View->getDb()->Primary
		);
			
		// Traitement des arguments
		foreach( $_REQUEST as $key => $value )
			$args[$key] = $value;

		return wp_parse_args( $this->prepare_query_args, $args );
	} 
	
	/* = PARAMETRAGE = */
	/** == Définition des notifications == **/
	public function set_notifications()
	{
		/// Définition des notifications prédéfines
		$defaults = array(
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
		return $this->notifications = wp_parse_args( $this->notifications, $defaults );		
	}
	
	/** == Définition des vues filtrées == **/
	public function set_views()
	{
		foreach( $this->views as $i => $view ) :
			$this->_views[$i] = $this->_parse_view( $view, $i );
		endforeach;
	
		return $this->_views;
	}
	
	/*** === Liste des actions prédéfinies affectant un seul élément === ***/
	final public function default_single_actions( $item, $action = array() )
	{
		// Edition		
		$edit 				= "<a href=\"".
									$this->get_edit_uri( $item->{$this->View->getDb()->Primary} )
									."\" title=\"". __( 'Éditer l\'élément', 'tify' ) ."\">". 
									__( 'Éditer', 'tify' ) 
								."</a>";		
		// Suppression définitive					
		$delete				= "<a href=\"". 
		        					$this->get_single_action_uri( $item->{$this->View->getDb()->Primary}, 'delete', array( '_wp_http_referer' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) 
								."\" title=\"". __( 'Suppression définitive de l\'élément', 'tify' ) ."\">". 
								__( 'Supprimer définitivement', 'tify' ) 
								."</a>";
		
		// Mise à la corbeille																				
		$trash					= "<a href=\"". 
		        					$this->get_single_action_uri( $item->{$this->View->getDb()->Primary}, 'trash', array( '_wp_http_referer' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) 
								."\" title=\"". __( 'Mise à la corbeille de l\'élément', 'tify' ) ."\">". 
								__( 'Mettre à la corbeille', 'tify' ) 
								."</a>";
		// Sortie de la corbeille
		$untrash				= "<a href=\"". 
		        					$this->get_single_action_uri( $item->{$this->View->getDb()->Primary}, 'untrash', array( '_wp_http_referer' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) 
								."\" title=\"". __( 'Rétablissement de l\'élément', 'tify' ) ."\">". 
								__( 'Rétablir', 'tify' ) 
								."</a>";		
		// Duplication
		$duplicate				= "<a href=\"". 
		        					$this->get_single_action_uri( $item->{$this->View->getDb()->Primary}, 'duplicate', array( '_wp_http_referer' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) 
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
	
	/** == Récupération des éléments == **/
	public function prepare_items() 
	{				
		// Récupération des items
		$query = $this->View->getDb()->query( $this->parse_request() );
		$this->items = $query->items;
		
		// Pagination
		$total_items = $query->found_items;
		$this->set_pagination_args( 
			array(
            	'total_items' => $total_items,                  
            	'per_page'    => $this->per_page,                    
            	'total_pages' => ceil( $total_items / $this->per_page )
			) 
		);
	}
	
	/** == Définition de la liste des colonnes == **/
	public function get_columns() 
	{
		$c = array(
			'cb' => "<input type=\"checkbox\" />"
		);
		foreach( (array) $this->View->getDb()->ColNames as $name )
			$c[$name] = $name;
		return $c;
	}
	
	/** == Définition de l'ordonnancement par colonne == **/
	public function get_sortable_columns()
	{
		return array();
	}
	
	/** == Agrégations des actions aux éléments de la colonne primaire == **/
	public function handle_row_actions( $item, $column_name, $primary ) 
	{
		if ( $primary !== $column_name )
			return;
		
		return $this->row_actions( $this->get_item_actions( $item, array( 'edit', 'delete' ) ) );
	}
	
	/* = TRAITEMENT DES DONNÉES = */		
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
		$item_id = $this->current_item();
		check_admin_referer( $this->View->getDb()->ID . $this->current_action() . $item_id );
		
		// Traitement de l'élément
		$this->View->getDb()->handle()->delete_by_id( $item_id );
		if( $this->View->getDb()->meta() )
			$this->View->getDb()->meta()->delete_all( $item_id );
		
		// Traitement de la redirection
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'deleted', $sendback );	
		
		wp_redirect( $sendback );
		exit;
	}
	
	/** == Éxecution de l'action - mise à la corbeille == **/
	protected function process_bulk_action_trash()
	{
		$item_id = $this->current_item();
		check_admin_referer( $this->View->getDb()->ID . $this->current_action() . $item_id );
			
		// Traitement de l'élément				
		/// Conservation du statut original
		if( $this->View->getDb()->meta() && ( $original_status = $this->View->getDb()->select()->cell( $item_id, 'status' ) ) )
			$this->View->getDb()->meta()->update( $item_id, '_trash_meta_status', $original_status );					
		/// Modification du statut
		$this->View->getDb()->handle()->update( $item_id, array( 'status' => 'trash' ) );
		
		// Traitement de la redirection
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'trashed', $sendback );
											
		wp_redirect( $sendback );
		exit;
	}
	
	/** == Éxecution de l'action - restauration d'élément à la corbeille == **/
	protected function process_bulk_action_untrash()
	{
		$item_id = $this->current_item();		
		check_admin_referer( $this->View->getDb()->ID . $this->current_action() . $item_id );
		
		// Traitement de l'élément				
		/// Récupération du statut original
		$original_status = ( $this->View->getDb()->meta() && ( $_original_status = $this->View->getDb()->meta()->get( $item_id, '_trash_meta_status', true ) ) ) ? $_original_status : 'draft';				
		if( $this->View->getDb()->meta() ) $this->View->getDb()->meta()->delete( $item_id, '_trash_meta_status' );
		/// Mise à jour du statut
		$this->View->getDb()->handle()->update( $item_id, array( 'status' => $original_status ) );
		
		// Traitement de la redirection
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'untrashed', $sendback );
			
		wp_redirect( $sendback );
		exit;
	}

	/* = CONTROLEUR = */
	/** == Récupération des actions affectées à un élément == **/
	public function get_item_actions( $item, $actions = array() )
	{
		$defaults 	= $this->default_single_actions($item, $actions );
		$_actions 	= wp_parse_args( $this->actions, $defaults );
		$item_actions = array();
		foreach( $actions as $action )
			if( isset( $_actions[$action] ) )
				$item_actions[$action] = $_actions[$action]; 
		return $item_actions;		
	}

	/** == Lien vers l'édition d'un élément == **/
	public function get_edit_uri( $item_id )
	{
		return esc_attr( add_query_arg( array( $this->View->getDb()->Primary => $item_id ), $this->View->getAdminViewAttrs( 'menu_page_url', 'EditForm' ) ) );
	}
	
	/** == Lien vers une action appliquée à un élément == **/
	public function get_single_action_uri( $item_id, $action, $args = array(), $nonce = true )
	{		
		$args = wp_parse_args( $args, array( $this->View->getDb()->Primary => $item_id, 'action' => $action ) );
		$uri = add_query_arg( $args );		
		if( $nonce )
			$uri = wp_nonce_url( $uri, ( is_bool( $nonce ) ? $this->View->getDb()->ID . $action . $item_id : $nonce ) );
		
		return esc_attr( $uri );
	}
	
	/** === Traitement des arguments d'une vue filtrée == **/
	private function _parse_view( $args, $index = 0 )
	{
		$defaults = array(
			'label'					=> sprintf( __( 'Filtre #%d', 'tify' ), $index ),
			'current'				=> false,
			'class'					=> '',
			'link_attrs'			=> array(),
			'uri'					=> $this->View->getAdminViewAttrs( 'base_url' ), //set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ),
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
		$view .= " <span class=\"count\">(". call_user_func( array( $this->View->getDb()->select(), 'count' ), $count_query_args ) .")</span>";
		$view .= "</a>";	
		
		return $view;
	}	
	
	/* = AFFICHAGE = */
	/** == Affichage des messages de notifications == **/
	public function notifications()
	{
		$output = "";
		if ( $notifications = $this->current_notification() )
			foreach( $notifications as $i => $n )
				$output .= "<div id=\"{$n['type']}-{$i}\" class=\"notice notice-{$n['type']}". ( $n['dismissible'] ? ' is-dismissible' : '' ) ."\"><p>{$n['message']}</p></div>";
			
		echo $output;
	}
	
	/** == Récupération des filtres de la vue filtrée == **/
	public function get_views( )
	{		
		return $this->_views;
	}
	
	/** == == **/
	public function no_items() 
	{
		echo $this->View->getLabel( 'not_found' );
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
				return $item->{$column_name};
				break;
			case 'DATETIME' :
				return mysql2date( get_option( 'date_format') .' @ '.get_option( 'time_format' ), $item->{$column_name} );
				break;
		endswitch;
    }
	
	/** == Contenu de la colonne Case à cocher == **/
	public function column_cb( $item )
	{
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->{$this->View->getDb()->Primary} );
    }
    
    /** == Rendu == **/
    public function Render()
    {
 		$this->prepare_items();
    ?>
		<div class="wrap">
    		<h2>
    			<?php echo $this->View->getLabel( 'all_items' );?>
    			
    			<?php if( $this->View->getAdminViewAttrs( 'base_url', 'EditForm' ) ) : ?>
    				<a class="add-new-h2" href="<?php echo $this->View->getAdminViewAttrs( 'base_url', 'EditForm' );?>"><?php echo $this->View->getLabel( 'add_new' );?></a>
    			<?php endif;?>
    			
    			<?php if( $this->View->getAdminViewAttrs( 'base_url', 'Import' ) ) : ?>
    				<a class="add-new-h2" href="<?php echo $this->View->getAdminViewAttrs( 'base_url', 'Import' );?>"><?php echo $this->View->getLabel( 'import_items' );?></a>
    			<?php endif;?>
    		</h2>
    		<?php $this->notifications();?>
    		
    		<?php $this->views(); ?>
    		
    		<form method="post" action="<?php echo $this->View->getAdminViewAttrs( 'base_url' );?>">
    			<?php $this->search_box( $this->View->getLabel( 'search_items' ), $this->View->getID() );?>
    			<?php $this->display();?>
			</form>
    	</div>
    <?php
    }
}