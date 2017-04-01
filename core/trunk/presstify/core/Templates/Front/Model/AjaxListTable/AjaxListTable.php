<?php 
namespace tiFy\Core\Templates\Front\Model\AjaxListTable;

class AjaxListTable extends \tiFy\Core\Templates\Front\Model\Table
{				
	/* = ARGUMENTS = */
	// Paramètres
	///
	protected $Endpoint				= null;
		
	/// Cartographie des paramètres
	protected $ParamsMap			= array(
		'BaseUri', 'EditBaseUri', 'Plural', 'Singular', 'Notices', 'Statuses', 'FilteredViewLinks', 
		'ItemIndex', 'Columns', 'PrimaryColumn', 'SortableColumns', 'HiddenColumns', 'PerPage', 'PerPageOptionName',
		'QueryArgs', 'NoItems', 'BulkActions', 'RowActions',	
		'Endpoint', 'MapColumns'
	);
	
	/* = PARAMETRES = */
	/** == == **/
	public function set_endpoint()
	{
		return '';
	}
	
	/** == == **/
	public function set_columns_map()
	{
		return array();
	}
	
	/* = PARAMETRAGE = */
	/** == == **/
	public function initEndpoint()
	{
		return $this->Endpoint = $this->set_endpoint();
	}
	
	/** ==  == **/
	public function init_param_MapColumns()
	{
		if( $this->set_columns_map() )
			$this->MapColumns = (array) $this->set_columns_map();
		else 
			$this->MapColumns = $this->getConfig( 'columns_map', $this->Name );
	}
	
	
	/* = DECLENCHEURS = */
	/** == Récupération Ajax des la liste des éléments  == **/
	final public function wp_ajax_get_items()
	{
		// Initialisation des paramètres de configuration de la table
		$this->init_params();
	
		$this->prepare_items();
		
		// Traitement des erreurs
		if( \is_wp_error( $this->items ) )
			wp_send_json_error( $this->items->get_error_message() );

		ob_start();
		$this->pagination( 'ajax' );
		$pagination = ob_get_clean();
				
		$data = array();
		foreach ( (array) $this->items as $i => $item ) :
			foreach( (array) $this->get_columns() as $column_name => $column_label ) :
				if ( 'cb' === $column_name ) :
					$data[$i][$column_name] = $this->column_cb( $item );
				elseif ( method_exists( $this, '_column_' . $column_name ) ) :
					$data[$i][$column_name] = call_user_func(
						array( $this, '_column_' . $column_name ),
						$item,
						$classes,
						//$data,
						$this->PrimaryColumn
					);
				elseif ( method_exists( $this, 'column_' . $column_name ) ) :
					$data[$i][$column_name]  = call_user_func( array( $this, 'column_' . $column_name ), $item );
					$data[$i][$column_name] .= $this->handle_row_actions( $item, $column_name, $this->PrimaryColumn );
				else :
					$data[$i][$column_name]  = $this->column_default( $item, $column_name );
					$data[$i][$column_name] .= $this->handle_row_actions( $item, $column_name, $this->PrimaryColumn );
				endif;
			endforeach;
		endforeach;

		$response =  array(
			'pagenum'			=> $this->get_pagenum(),
			'draw'				=> $_REQUEST['draw'],
			'recordsTotal'		=> $this->_pagination_args['total_items'],
			'recordsFiltered'	=> $this->_pagination_args['total_items'],
			'pagination'		=> $pagination,
			'data'				=> $data
		);

		wp_send_json( $response );
	}
	
	/* = TRAITEMENT = */
	/** == Récupération des éléments == **/
	public function prepare_items()
	{
		$endpoint = $this->Endpoint;
		
		// Traitement des paramètres de requête
		if( $item_id = (int) $this->current_item() ) :
			$endpoint .= "/{$item_id}";
		else :
			$params 	= $this->parse_query_args();
			$_params	= http_build_query( $params );
			$endpoint .= "/?{$_params}";
		endif;
		
		$response = wp_remote_get( $endpoint );
		
		// Traitement des erreurs
		if( \is_wp_error( $response ) )
			return $this->items = $response;
		if( ! $body = wp_remote_retrieve_body( $response ) ) :
			return $this->items = new \WP_Error( 'empty_body', __( 'Aucun résultat ne correspond à la requête', 'tify' ) );
		endif;
				
		// Pagination
		$this->set_pagination_args(
			array(
				'total_items' => wp_remote_retrieve_header( $response, 'x-wp-total' ),
				'per_page'    => $this->PerPage,
				'total_pages' => wp_remote_retrieve_header( $response, 'x-wp-totalpages' )
			)
		);
		
		// Traitement de la réponse
		if( $this->current_item() ) :
			$results[] = json_decode( $body, true );
		else :	
			$results = json_decode( $body, true );
		endif;
		
		$items = array();
		foreach( $results as $key => $attrs ) :
			$items[$key] = new \stdClass;
			foreach( $attrs as $prop => $value ) :
				$cn = ( $res = array_search ( $prop, $this->MapColumns ) ) ? $res : $prop;
				$items[$key]->{$cn} = $value;
			endforeach;
		endforeach;
		
		return $this->items = $items;		
	}
		
	/* = CONFIGURATION DE DATATABLES = */
	/** == == **/
	public function getDatatablesData()
	{
		return array(
			'action'		=> $this->template()->getID() .'_get_items'
		);
	}

	/** == Définition du fichier de traduction == **/
	private function getDatatablesLanguageUrl()
	{
		if( ! function_exists( 'wp_get_available_translations' ) )
			require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
		
		$AvailableTranslations 	= wp_get_available_translations();		
		$version				= tify_script_get_attr( 'datatables', 'version' );
		$language_url 			= "//cdn.datatables.net/plug-ins/{$version}/i18n/English.json";
		
		if( isset( $AvailableTranslations[ get_locale() ] ) ) :
			$file = preg_replace( '/\s\(.*\)/', '', $AvailableTranslations[ get_locale() ]['english_name'] );
			if( curl_init( "//cdn.datatables.net/plug-ins/{$version}/i18n/{$file}.json" ) ) :
				$language_url = "//cdn.datatables.net/plug-ins/{$version}/i18n/{$file}.json";
			endif;
		endif;
		
		return $language_url;
	}
	
	/** == Définition des propriétés de colonnes de la table == **/
	private function getDatatablesColumns()
	{
		$columns = array();

		foreach( $this->get_columns() as $name => $title ) :
			array_push( 
				$columns, 
				array( 
					'data'		=> $name,
					'name'		=> $name,	
					'title'		=> $title,
					'orderable'	=> false,
					'visible'	=> ! in_array( $name, $this->HiddenColumns ),
					'className'	=> "{$name} column-{$name}". ( $this->get_primary_column() === $name ? ' has-row-actions column-primary' : '' )
				)
			);
		endforeach;
		
		return $columns;
	}
}