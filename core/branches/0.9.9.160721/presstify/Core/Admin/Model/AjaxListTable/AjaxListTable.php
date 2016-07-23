<?php 
namespace tiFy\Core\Admin\Model\AjaxListTable;

use tiFy\Core\Admin\Model\Table;

class AjaxListTable extends Table
{	
	// Intitulés de l'option du nombre d'éléments par page
	private $PerPageName;
	
	// Configuration de la table
	protected $Config					= array();	
	
	// Mode de debogage
	protected $Debug					= true;
	
	/// Cartographie des paramètres
	protected $ParamsMap			= array( 
		'BaseUri', 'Config', 'EditBaseUri', 'Notices', 'FilteredViewLinks', 'Columns', 'SortableColumns', 'PerPage',  
		'QueryArgs', 'NoItems', 'BulkActions', 'RowActions'	
	);
		
	/* = INITIALISATION DES PARAMETRES = */
	/** == Initialisation de l'url de la page d'administration == **/
	public function init_param_Config()
	{	
		// Activation de l'interface de gestion du nombre d'éléments par page
		$this->Screen->add_option( 
			'per_page', 
			array( 
				'option' => $this->View->getID() .'_per_page' 
			) 
		);
	}
		
	/* = DECLENCHEURS = */
	/** == == **/
	final public function _init()
	{
		$this->PerPageName = $per_page_option = $this->View->getID() .'_per_page';
		add_filter( 'set-screen-option', function( $none, $option, $value ) use ( $per_page_option ){ return ( $per_page_option  ===  $option ) ? $value : $none; }, 10, 3 );
		
		add_action( 'wp_ajax_'. $this->View->getID() .'_get', array( $this, 'WpAjaxGetItems' ) );
		add_action( 'wp_ajax_'. $this->View->getID() .'_per_page', array( $this, 'WpAjaxPerPage' ) );
	}
	
	/** == == **/
	final public function _admin_init()
	{
		$defaults	= array(
			// Url de requête
			'rest_url'			=> esc_url_raw( rest_url() ),
			// Nom de domaine d'appel de l'url de requête
			'namespace'			=> '',
			// Chemin d'appel de l'url de requête
			'route'				=> '',
			// Liste des colonnes	
			'columns'			=> array(),
			// Colonne principale
			'primary'			=> '',
			// Liste des colonnes triable
			'sortables'			=> array(),
			// Liste des colonnes masquées
			'hiddens'			=> array(),
			// Nombre d'éléments par page
			'per_page'			=> 20,
			// Données envoyées au serveur
			'transport'			=>	array(),
			// Données reçues du serveur
			'response'			=> array(),	
		);
		
		// Configuration
		foreach( (array) $defaults as $config => $default ) :
			if( isset( $this->Config[$config] ) )
				continue;
		
			if( $_value =  $this->View->getModelAttrs( $config, 'AjaxListTable' ) ) :
				switch( $config ) :
					default :
						$this->Config[$config] = $_value;
						break;
					case 'primary' :
						$this->Config[$config] = $_value;
						add_filter( 'list_table_primary_column', function( $default, $screen_id ) use ( $_value ){ return $_value; }, 10, 2 );
						break;
					case 'per_page' :
						$this->Config[$config] = $per_page = (int) $_value;
						add_filter( $this->PerPageName, function() use ( $per_page ){ return $per_page; }, 0 );
						break;
				endswitch;
			else :
				$this->Config[$config] = $default;
			endif;
		endforeach;

		$this->Config['per_page'] = $this->get_items_per_page( $this->PerPageName, $this->Config['per_page'] );
	}
		
	/** == Mise en file des scripts de l'interface d'administration == **/
	final public function _admin_enqueue_scripts()
	{		
		// Configuration	
		wp_localize_script( 
			'tiFy_Core_Admin_AjaxListTable',
			'tiFy_Core_Admin_AjaxListTable', 
			array(
				'_action'		=> $this->View->getID(),
				'_route'		=> trim( rtrim( $this->getConfig( 'route' ), '/' ), '/' ),
				'columns'		=> $this->getDatatablesColumns(),
				'language'		=> array( 
					'url' 		=> $this->getDatatablesLanguageUrl(),
				),
				'per_page'		=> $this->getConfig( 'per_page' )	
			) 
		);
	}
	
	/* = AJAX = */
	/** == Récupération des éléments == **/
	final public function WpAjaxGetItems()
	{
		if( ! $response = $this->getCurlResponse() )
	    	return;
  		
		$this->items = $this->parseResponseItems( $response );

		// Pagination
		$this->set_pagination_args( 
			array(
            	'total_items' => $response['headers']['x-wp-total'],                  
            	'per_page'    => $this->PerPage,                    
            	'total_pages' => $response['headers']['x-wp-totalpages']
			) 
		);

		ob_start();
		$this->pagination( 'ajax' );
		$pagination = ob_get_contents();
		ob_end_clean();
		
		$data = array();
		foreach ( (array) $this->items as $i => $item ) :
			foreach( $item as $column_name => $attrs ) :		
				if ( 'cb' === $column_name ) :
					$data[$i][$column_name] = $this->column_cb( $item );
				elseif ( method_exists( $this, '_column_' . $column_name ) ) :
					$data[$i][$column_name] = call_user_func(
						array( $this, '_column_' . $column_name ),
						$item,
						$classes,
						//$data,
						$this->getConfig( 'primary' )
					);
				elseif ( method_exists( $this, 'column_' . $column_name ) ) :
					$data[$i][$column_name]  = call_user_func( array( $this, 'column_' . $column_name ), $item );
					$data[$i][$column_name] .= $this->handle_row_actions( $item, $column_name, $this->getConfig( 'primary' ) );
				else :
					$data[$i][$column_name]  = $this->column_default( $item, $column_name );
					$data[$i][$column_name] .= $this->handle_row_actions( $item, $column_name, $this->getConfig( 'primary' ) );
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
	
	/** == Nombre d'éléments par page == **/
	final public function WpAjaxPerPage()
	{
		$res = update_user_meta( get_current_user_id(), $this->PerPageName, $_POST['per_page'] );
		wp_die();
	}
	
	/* = CONTROLEUR = */
	/** == Récupération de la configuration == **/
	protected function getConfig( $config = null, $default = null )
	{
		if( ! $config ) :
			return $this->Config;
		elseif( isset( $this->Config[$config] ) ) :
			return $this->Config[$config];
		else :
			return $default;
		endif;
	}
	
	/** == == **/
	private function parseResponseItems( $response )
	{
		$items = array();

		if( empty( $response['body'] ) )
			return $items;
		$results = json_decode( $response['body'], true );
		
		$return_datas = $this->getConfig( 'response', array() );
		
		foreach( $results as $key => $attrs ) :
			$items[$key] = new \stdClass;
			foreach( $attrs as $prop => $value ) :
				$cn = ( $res = array_search ( $prop, $this->getConfig( 'transport', array() ) ) ) ? $res : $prop;
				if( isset( $return_datas[$cn] ) )					
					$value = $this->getResponseData( (array) $value, $return_datas[$cn] );
				$items[$key]->{$cn} = $value;
			endforeach;
			foreach( $this->get_columns() as $col_name => $header ) :
				if( ! isset( $items[$key]->$col_name ) )
					$items[$key]->$col_name = null;
			endforeach;
		endforeach;

		return $items;
	}
	
	/** == == **/
	private function getResponseData( $value, $response_datas )
	{
		if( is_string( $response_datas ) && isset( $value[$response_datas] ) ) :
			return $value[$response_datas];
		elseif( is_array( $response_datas ) ) :
			$key = key( $response_datas );
			return $this->getResponseData( (array) $value[$key], $response_datas[$key] );
		endif;
	}
	
	/* = RECUPERATION DES ELEMENTS = */
	/** == Traitement des arguments de requête == **/
	private function parseParams( $params = array() )
	{
		// Arguments par défaut
		$defaults = array(
			'per_page' 	=> $this->getConfig( 'per_page' ),
			'page'		=> 0,
			'orderby'	=> 'id'
		);
		
		// Traitement des arguments de requête
		$params = array();
		if( isset( $_REQUEST['draw'] ) )
			$params['draw']				= $_REQUEST['draw'];
		if( isset( $_REQUEST['length'] ) )
			$params['per_page']	= $_REQUEST['length'];
		if( isset( $_REQUEST['length'] ) && isset( $_REQUEST['start'] ) ) :
			$params['page']	= ceil( ( $_REQUEST['start']/$_REQUEST['length'] )+1 );
			$_REQUEST['paged'] = $params['page'];
		endif;
		if( isset( $_REQUEST['search'] ) && isset( $_REQUEST['search']['value'] ) )
			$params['search']				= $_REQUEST['search']['value'];
		if( isset( $_REQUEST['order'] ) ) :
			$params['orderby']			= array();
			foreach( (array) $_REQUEST['order'] as $k => $v ) :
				$params['orderby'][ $_REQUEST['columns'][$v['column']]['data']] = $v['dir'];
			endforeach;
		endif;
		
		return wp_parse_args( $params, $defaults );
	}
	
	/** == == **/
	protected function getCurlResponse()
	{
		$endpoint 	= $this->getConfig( 'rest_url' ) . $this->getConfig( 'namespace' ) . $this->getConfig( 'route' );
		
		// Traitement des paramètres de requête
		$params 	= $this->parseParams();
		$_params	= http_build_query( $params );

		$Curl = new \WP_Http_Curl;
		$response = $Curl->request( 
			$endpoint. "?{$_params}", 
			array( 
				'user-agent' 	=> 'tiFy-AjaxListTable/1.0',
				'timeout'		=> 60,	
				'stream' 		=> false, 
				'filename' 		=> false, 
				'decompress' 	=> false 
			)
		);

		if( $this->Debug )
			$this->Debug = $response;
		
		return $response;
	}
	
	/** == Récupération des éléments == **/
	public function prepare_items() 
	{						
	    if( ! $response = $this->getCurlResponse() )
	    	return;
  	
		$this->items = $this->parseResponseItems( $response );

		// Pagination
		$this->set_pagination_args( 
			array(
            	'total_items' => $response['headers']['x-wp-total'],                  
            	'per_page'    => $this->PerPage,                    
            	'total_pages' => $response['headers']['x-wp-totalpages']
			) 
		);
	}
		
	/** == Définition de la liste des colonnes == **/
	public function get_columns() 
	{
		$c = array(
			//'cb' => "<input type=\"checkbox\" />"
		);
		
		if( $columns = $this->getConfig( 'columns', array() ) ) :
			foreach( (array)  $columns as $index => $name )
				$c[$index] = $name;
		else :
			foreach( (array)  $this->View->getDb()->ColNames as $name )
				$c[$name] = $name;
		endif;		
		
		return $c;
	}
	
	/* = CONFIGURATION DE DATATABLES = */
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
					'visible'	=> ! in_array( $name, $this->getConfig( 'hiddens', array() ) ),
					'className'	=> "{$name} column-{$name}". ( $this->getConfig( 'primary' ) === $name ? ' has-row-actions column-primary' : '' )
				)
			);
		endforeach;
		
		return $columns;
	}
	
	/** == == **/
	public function display()
	{
		$this->display_tablenav( 'top' );

		$this->screen->render_screen_reader_content( 'heading_list' );
	?>
		<table id="tiFy_View_Admin_AjaxListTable" class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>" data-length="<?php echo $this->_pagination_args['per_page'];?>" data-total="<?php echo $this->_pagination_args['total_items'];?>">
			<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
			</thead>
		
			<tbody id="the-list">
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>
		
			<tfoot>
			<tr>
				<?php $this->print_column_headers( false ); ?>
			</tr>
			</tfoot>
		</table>
	<?php
	
		$this->display_tablenav( 'bottom' );
	}
		
	/** == Contenu des colonnes par défaut == **/
	public function column_default( $item, $column_name )
	{		
		// Bypass 
		if( ! isset( $item->{$column_name} ) )
			return;

		if( is_array( $item->{$column_name} ) ) :
			return '';//join( ', ', $item->{$column_name} );
		else :	
			return $item->{$column_name};
		endif;
    }
}