<?php 
namespace tiFy\Core\View\Front\AjaxListTable;

use tiFy\Environment\App;

class AjaxListTable extends App
{	
	/* = ARGUMENTS = */
	// Classe de la vue
	protected $View		= null;
				
	// Configuration de la table
	protected $Config					= array();	
	
	// Mode de debogage
	protected $Debug					= true;
	
	/* = CONSTRUCTEUR = */			
	public function __construct( \tiFy\Core\View\Factory $viewObj )
	{
		parent::__construct();
		
		if( is_null( $this->View ) )
			$this->View = $viewObj;		
	}
	
	/* = DECLENCHEURS = */
	/** == == **/
	final public function _init()
	{
		$defaults	= array(
			// Libellés
			'singular'			=> $this->View->getLabel( 'singular' ),
			'plural'			=> $this->View->getLabel( 'plural' ),
			// 
			'ajax'				=> true,
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
		
			if( $_value =  $this->View->getFrontViewAttrs( $config, 'AjaxListTable' ) ) :
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
				
		add_action( 'wp_ajax_'. $this->View->getID() .'_get', array( $this, 'WpAjaxGetItems' ) );
		add_action( 'wp_ajax_'. $this->View->getID() .'_per_page', array( $this, 'WpAjaxPerPage' ) );
	}
				
	/** == Mise en file des scripts de l'interface d'administration == **/
	final public function _enqueue_scripts()
	{		
		// Configuration	
		wp_localize_script( 
			'tiFy_View_Front_AjaxListTable',
			'tiFy_View_Front_AjaxListTable', 
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
		$this->prepare_items();
		
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
		if( isset( $_REQUEST['length'] ) && isset( $_REQUEST['start'] )  ) :
			$params['page']			= ceil( ( $_REQUEST['start']/$_REQUEST['length'] )+1 );
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
		$endpoint 	= "http://bigben.suivi.tigreblanc.fr/wp-json/bigbenAPI/v1/bb_game/";
		
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
	
/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array();
	}

	/**
	 * Gets the name of the default primary column.
	 *
	 * @since 4.3.0
	 * @access protected
	 *
	 * @return string Name of the default primary column, in this case, an empty string.
	 */
	protected function get_default_primary_column_name() {
		$columns = $this->get_columns();
		$column = '';

		if ( empty( $columns ) ) {
			return $column;
		}

		// We need a primary defined so responsive views show something,
		// so let's fall back to the first non-checkbox column.
		foreach ( $columns as $col => $column_name ) {
			if ( 'cb' === $col ) {
				continue;
			}

			$column = $col;
			break;
		}

		return $column;
	}

	/**
	 * Public wrapper for WP_List_Table::get_default_primary_column_name().
	 *
	 * @since 4.4.0
	 * @access public
	 *
	 * @return string Name of the default primary column.
	 */
	public function get_primary_column() {
		return $this->get_primary_column_name();
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 4.3.0
	 * @access protected
	 *
	 * @return string The name of the primary column.
	 */
	protected function get_primary_column_name() {
		$columns = get_column_headers( $this->screen );
		$default = $this->get_default_primary_column_name();

		// If the primary column doesn't exist fall back to the
		// first non-checkbox column.
		if ( ! isset( $columns[ $default ] ) ) {
			$default = WP_List_Table::get_default_primary_column_name();
		}

		/**
		 * Filter the name of the primary column for the current list table.
		 *
		 * @since 4.3.0
		 *
		 * @param string $default Column name default for the specific list table, e.g. 'name'.
		 * @param string $context Screen ID for specific list table, e.g. 'plugins'.
		 */
		$column  = apply_filters( 'list_table_primary_column', $default, $this->screen->id );

		if ( empty( $column ) || ! isset( $columns[ $column ] ) ) {
			$column = $default;
		}

		return $column;
	}

	/**
	 * Get a list of all, hidden and sortable columns, with filter applied
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_column_info() {
		// $_column_headers is already set / cached
		if ( isset( $this->_column_headers ) && is_array( $this->_column_headers ) ) {
			// Back-compat for list tables that have been manually setting $_column_headers for horse reasons.
			// In 4.3, we added a fourth argument for primary column.
			$column_headers = array( array(), array(), array(), $this->get_primary_column_name() );
			foreach ( $this->_column_headers as $key => $value ) {
				$column_headers[ $key ] = $value;
			}

			return $column_headers;
		}
	}
	
	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @staticvar int $cb_counter
	 *
	 * @param bool $with_id Whether to set the id attribute or not
	 */
	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );

		if ( isset( $_GET['orderby'] ) ) {
			$current_orderby = $_GET['orderby'];
		} else {
			$current_orderby = '';
		}

		if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
			$current_order = 'desc';
		} else {
			$current_order = 'asc';
		}

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
				. '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
			$cb_counter++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			if ( in_array( $column_key, $hidden ) ) {
				$class[] = 'hidden';
			}

			if ( 'cb' === $column_key )
				$class[] = 'check-column';
			elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
				$class[] = 'num';

			if ( $column_key === $primary ) {
				$class[] = 'column-primary';
			}

			if ( isset( $sortable[$column_key] ) ) {
				list( $orderby, $desc_first ) = $sortable[$column_key];

				if ( $current_orderby === $orderby ) {
					$order = 'asc' === $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
			}

			$tag = ( 'cb' === $column_key ) ? 'td' : 'th';
			$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
			$id = $with_id ? "id='$column_key'" : '';

			if ( !empty( $class ) )
				$class = "class='" . join( ' ', $class ) . "'";

			echo "<$tag $scope $id $class>$column_display_name</$tag>";
		}
	}
		
	/** == == **/
	public function display()
	{
		//$this->display_tablenav( 'top' );
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
	
	/**
	 * Get a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
	}
	
	/**
	 * Generate the tbody element for the list table.
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function display_rows_or_placeholder() {
		if ( $this->has_items() ) {
			$this->display_rows();
		} else {
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
		}
	}

	/**
	 * Generate the table rows
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function display_rows() {
		foreach ( $this->items as $item )
			$this->single_row( $item );
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @param object $item The current item
	 */
	public function single_row( $item ) {
		echo '<tr>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}
		
	/** == Contenu des colonnes par défaut == **/
	public function column_default( $item, $column_name )
	{		
		// Bypass 
		if( ! isset( $item->{$column_name} ) )
			return;

		if( is_array( $item->{$column_name} ) ) :
			return join( ', ', $item->{$column_name} );
		else :	
			return $item->{$column_name};
		endif;
    } 
    
	/**
	 * Generates the columns for a single row of the table
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param object $item The current item
	 */
	protected function single_row_columns( $item ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$classes = "$column_name column-$column_name";
			if ( $primary === $column_name ) {
				$classes .= ' has-row-actions column-primary';
			}

			if ( in_array( $column_name, $hidden ) ) {
				$classes .= ' hidden';
			}

			// Comments column uses HTML in the display name with screen reader text.
			// Instead of using esc_attr(), we strip tags to get closer to a user-friendly string.
			$data = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';

			$attributes = "class='$classes' $data";

			if ( 'cb' === $column_name ) {
				echo '<th scope="row" class="check-column">';
				echo $this->column_cb( $item );
				echo '</th>';
			} elseif ( method_exists( $this, '_column_' . $column_name ) ) {
				echo call_user_func(
					array( $this, '_column_' . $column_name ),
					$item,
					$classes,
					$data,
					$primary
				);
			} elseif ( method_exists( $this, 'column_' . $column_name ) ) {
				echo "<td $attributes>";
				echo call_user_func( array( $this, 'column_' . $column_name ), $item );
				echo $this->handle_row_actions( $item, $column_name, $primary );
				echo "</td>";
			} else {
				echo "<td $attributes>";
				echo $this->column_default( $item, $column_name );
				echo $this->handle_row_actions( $item, $column_name, $primary );
				echo "</td>";
			}
		}
	}

	/** == Rendu de la page  == **/
	public function Render()
	{
		$this->prepare_items();		
	?>
		<div class="wrap">
    		<h2>
    			<?php echo $this->View->getLabel( 'all_items' );?>
    		</h2>
        	        	
    	    <?php //$this->search_box( $this->View->getLabel( 'search_items' ), $this->View->getID() );?>		
    		<?php $this->display();?>
    	</div>
	<?php
	}
}