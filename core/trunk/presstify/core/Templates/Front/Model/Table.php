<?php
namespace tiFy\Core\Templates\Front\Model;
    
abstract class Table 
{
    use \tiFy\Core\Templates\Traits\Factory;
    use \tiFy\Core\Templates\Traits\Table\Actions;
    use \tiFy\Core\Templates\Traits\Table\Notices;
    use \tiFy\Core\Templates\Traits\Table\Params;
    use \tiFy\Core\Templates\Traits\Table\Views;

    /**
     * Url de la page d'affichage de l'interface d'administration
     * @var string
     */
    protected $BaseUri;

    /**
     * Url de la page d'affichage d'édition d'un élément de l'interface d'administration
     * @var string
     */
    protected $EditBaseUri;

    /**
     * Intitulé de multiples éléments
     * @var string
     */
    protected $Plural;

    /**
     * Intitulé d'un élement unique
     * @var string
     */
    protected $Singular ;

    /**
     * Liste des message de notification
     * @var array
     */
    protected $Notices                  = array();

    /**
     * Liste des statuts possibles des éléments
     * @var array
     */
    protected $Statuses                 = array();

    /**
     * Liens de vue filtrées
     * @var array
     */
    protected $FilteredViewLinks        = array();

    /**
     * Classes de la table
     * @var array
     */
    protected $TableClasses             = array();

    /**
     * Colonnes de la table
     * @var array
     */
    protected $Columns                  = array();

    /**
     * Colonne principale de la table
     * @var string
     */
    protected $PrimaryColumn;

    /**
     * Liste des colonnes selon lesquelles les éléments peuvent être triés
     * @var array
     */
    protected $SortableColumns          = array();

    /**
     * Listes des colonnes masquées
     * @var array
     */
    protected $HiddenColumns            = array();

    /**
     * Nombre d'éléments affichés par page
     * @var int
     */
    protected $PerPage                = 20;
    
    /// 
    protected $PerPageOptionName    = null;

    /**
     * Liste des arguments de requête des récupération des éléments à afficher
     * @var array
     */
    protected $QueryArgs            = array();

    /**
     * Intitulé affiché lorsque la table est vide
     * @var array
     */
    protected $NoItems;

    /**
     * Liste des actions groupées
     * @var array
     */
    protected $BulkActions            = array();

    /**
     * Liste des actions sur un élément
     * @var array
     */
    protected $RowActions            = array();

    /**
     * Carographie des paramètres autorisés
     * @var string[]
     */
    protected $ParamsMap            = array( 
        'BaseUri',
        'EditBaseUri',
        'Plural',
        'Singular',
        'Notices',
        'Statuses',
        'FilteredViewLinks',
        'TableClasses',
        'ItemIndex',
        'Columns',
        'PrimaryColumn',
        'SortableColumns',
        'HiddenColumns',
        'PerPage',
        'PerPageOptionName',
        'QueryArgs',
        'NoItems',
        'BulkActions',
        'RowActions',
        'PageTitle'
    );
        
    /* = CONSTRUCTEUR = */
    public function __construct(){}
    
    /* = METHODES MAGIQUES = */
    /** == Appel des méthodes dynamiques == **/
    final public function __call( $name, $arguments )
    {
        if( in_array( $name, array( 'template', 'db', 'label', 'getConfig' ) ) ) :
            return call_user_func_array( $this->{$name}, $arguments );
        endif;
    }
    
    /* = PARAMETRES = */
    /** == Définition l'url de la page d'édition d'un élément == **/
    public function set_edit_base_url()
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
    
    /** == Définition des classes de la table == **/
    public function set_table_classes()
    {
        return array( 'table-striped', 'table-bordered', 'table-hover', 'table-condensed' );
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
            
    /* = DECLENCHEURS = */
    /** == Affichage de l'écran courant == **/
    final public function _current_screen()
    {                
        // Initialisation des paramètres de configuration de la table
        $this->initParams();
                        
        // Traitement
        /// Exécution des actions
        $this->process_bulk_actions();
        
        /// Affichage des messages de notification
        foreach( (array) $this->Notices as $nid => $nattr ) :
            if( ! isset( $_REQUEST[ $nattr['query_arg'] ] ) || ( $_REQUEST[ $nattr['query_arg'] ] !== $nid ) )
                continue;

            add_action( 'alert_notices', function() use( $nattr ){
            ?>
                <div class="alert alert-<?php echo $nattr['notice'];?><?php echo $nattr['dismissible'] ? ' is-dismissible':'';?>">
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
        if ( ! empty( $_REQUEST[$this->db()->getPrimary()] ) ) :
            if( is_array( $_REQUEST[$this->db()->getPrimary()] ) )
                return array_map('intval', $_REQUEST[$this->db()->getPrimary()] );
            else 
                return array( (int) $_REQUEST[$this->db()->getPrimary()] );
        endif;
        
        return 0;
    }
    
    /** == Récupération de l'action courante == **/
    public function current_action() 
    {        
        if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) )
            return false;
        
        if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
            return $_REQUEST['action'];
        if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
            return $_REQUEST['action2'];

        return false;
    }
    
    /** == Récupération des éléments == **/
    public function prepare_items() 
    {
        $query = $this->db()->query( $this->parse_query_args() );
        $this->items = $query->items;
        
        // Pagination
        $total_items     = $query->found_items;
        $per_page         = $this->get_items_per_page( $this->PerPageOptionName, $this->PerPage );
        $this->set_pagination_args( 
            array(
                'total_items'         => $total_items,                  
                'per_page'            => $per_page,                    
                'total_pages'         => ceil( $total_items / $per_page )
            ) 
        );
    }
    
    /** == == **/
    protected function set_pagination_args( $args ) 
    {
        $args = wp_parse_args( $args, array(
            'total_items' => 0,
            'total_pages' => 0,
            'per_page' => 0,
        ) );

        if ( ! $args['total_pages'] && $args['per_page'] > 0 )
            $args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );
        
        if ( ! headers_sent() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && $args['total_pages'] > 0 && $this->get_pagenum() > $args['total_pages'] ) {
            wp_redirect( add_query_arg( 'paged', $args['total_pages'] ) );
            exit;
        }            

        $this->_pagination_args = $args;
    }

    /** == == **/
    public function get_pagination_arg( $key ) {
        if ( 'page' === $key ) {
            return $this->get_pagenum();
        }

        if ( isset( $this->_pagination_args[$key] ) ) {
            return $this->_pagination_args[$key];
        }
    }
        
    /** == == **/
    public function has_items() 
    {
        return ! empty( $this->items );
    }
    
    /** == Traitement des arguments de requête == **/
    public function parse_query_args()
    {
        // Récupération des arguments        
        $per_page     = $this->get_items_per_page( $this->PerPageOptionName, $this->PerPage );
        $paged         = $this->get_pagenum();

        // Arguments par défaut
        $query_args = array(                        
            'per_page'     => $per_page,
            'paged'        => $paged,
            'order'        => ! empty( $_REQUEST['order'] ) ? strtoupper( $_REQUEST['order'] ) : 'DESC',
            'orderby'    => ! empty( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : $this->db()->getPrimary()
        );
            
        // Traitement des arguments
        foreach( (array) $_REQUEST as $key => $value ) :
            if( method_exists( $this, 'parse_query_arg_' . $key ) ) :
                 call_user_func_array( array( $this, 'parse_query_arg_' . $key ), array( &$query_args, $value ) );
            elseif( $this->db()->isCol( $key ) ) :
                $query_args[$key] = $value;
            endif;
        endforeach;

        return $this->QueryArgs = wp_parse_args( $this->QueryArgs, $query_args );
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
                $args = $this->row_action_item_parse_args( $item, $action, $this->RowActions[$action] );
                $row_actions[$action] = $this->row_action_link( $action, $args );
            endif;
        endforeach;        
                
        return $row_actions;        
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
    
    /* = HELPERS = */
    /** == Récupération de l'intitulé d'un statut == **/
    public function get_status( $status, $singular = true )
    {
        return \tiFy\Core\Templates\Admin\Helpers::getStatus( $status, $singular, $this->Statuses );
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
                    
    /* = PARAMETRES D'AFFICHAGE = * /
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
    protected function get_table_classes() 
    {
        return $this->TableClasses;
    }
        
    /** == == **/
    public function no_items() 
    {
        echo $this->NoItems;
    }
    
    /** == == **/
    public function search_box( $text, $input_id ) {
        if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
            return;

        $input_id = $input_id . '-search-input';

        if ( ! empty( $_REQUEST['orderby'] ) )
            echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
        if ( ! empty( $_REQUEST['order'] ) )
            echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
?>
<div class="search-box">
    <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
    <input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php echo isset($_REQUEST['s']) ? esc_attr( wp_unslash( $_REQUEST['s'] ) ) : '';?>" />
    <input type="submit" name="" id="search-submit" class="btn btn-default" value="<?php echo esc_attr( $text );?>"/>
</div>
<?php
    }
    
    /** == == **/
    protected function row_actions( $actions, $always_visible = false)
    {
        $action_count = count( $actions );
        $i = 0;

        if ( !$action_count )
            return '';

        $out = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';
        foreach ( $actions as $action => $link ) {
            ++$i;
            ( $i == $action_count ) ? $sep = '' : $sep = ' | ';
            $out .= "<span class='$action'>$link$sep</span>";
        }
        $out .= '</div>';

        return $out;
    }
                    
    /** == Ajout automatique des actions sur l'élément de la colonne principal == **/
    public function handle_row_actions( $item, $column_name, $primary ) 
    {
        if ( ( $primary !== $column_name ) || ! $this->set_handle_row_actions() )
            return;

        return $this->get_row_actions( $item, array_keys( $this->RowActions ) );
    }
    
    /** == == **/
    protected function get_column_headers()
    {
        return $this->get_columns();
    }
    
    /** == == **/
    protected function get_default_primary_column_name() 
    {
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
    
    /** == == **/
    public function get_primary_column() 
    {
        return $this->get_primary_column_name();
    }

    /** == == **/
    protected function get_primary_column_name() 
    {
        $columns = $this->get_column_headers();
        $default = $this->get_default_primary_column_name();

        $column  = apply_filters( 'list_table_primary_column', $default );

        if ( empty( $column ) || ! isset( $columns[ $column ] ) ) {
            $column = $default;
        }

        return $column;
    }
    
    /** == == **/
    protected function get_column_info() 
    {
        if ( isset( $this->_column_headers ) && is_array( $this->_column_headers ) ) {
            $column_headers = array( array(), array(), array(), $this->get_primary_column_name() );
            foreach ( $this->_column_headers as $key => $value ) {
                $column_headers[ $key ] = $value;
            }

            return $column_headers;
        }

        $columns = $this->get_column_headers();
        $hidden = //$this->get_hidden_columns( $this->screen );

        $sortable_columns = $this->get_sortable_columns();

        $sortable = array();
        foreach ( $sortable_columns as $id => $data ) {
            if ( empty( $data ) )
                continue;

            $data = (array) $data;
            if ( !isset( $data[1] ) )
                $data[1] = false;

            $sortable[$id] = $data;
        }

        $primary = $this->get_primary_column_name();
        $this->_column_headers = array( $columns, $hidden, $sortable, $primary );

        return $this->_column_headers;
    }
    
    /** == == **/
    public function get_pagenum() 
    {
        $pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
            $pagenum = $this->_pagination_args['total_pages'];

        return max( 1, $pagenum );
    }
    
    /** == == **/
    protected function get_items_per_page( $option, $default = 20 ) 
    {
        $per_page = (int) get_user_option( $option );
        if ( empty( $per_page ) || $per_page < 1 )
            $per_page = $default;

        return (int) apply_filters( $option, $per_page );
    }
    
    /* = AFFICHAGE = */        
    /** == == **/
    protected function pagination( $which ) 
    {
        if ( empty( $this->_pagination_args ) ) {
            return;
        }

        $total_items = $this->_pagination_args['total_items'];
        $total_pages = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current = $this->get_pagenum();
        $removable_query_args = wp_removable_query_args();

        $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

        $current_url = remove_query_arg( $removable_query_args, $current_url );

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

         if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev = true;
         }
        if ( $current == 2 ) {
            $disable_first = true;
        }
         if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
         }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf( "<a class='first-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( remove_query_arg( 'paged', $current_url ) ),
                __( 'First page' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf( "<a class='prev-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[] = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf( "<a class='next-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
                __( 'Next page' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf( "<a class='last-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
                __( 'Last page' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class = ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }
    
    
    /** == == **/
    public function print_column_headers( $with_id = true ) 
    {
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
    public function display() {
        //$singular = $this->_args['singular'];
                
        $this->display_tablenav( 'top' );

        //$this->screen->render_screen_reader_content( 'heading_list' );
?>
<div class="table-responsive">
    <table class="table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
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
</div>
<?php
        $this->display_tablenav( 'bottom' );
    }
        
    /** == == */
    protected function display_tablenav( $which ) {
        ?>
    <div class="tablenav <?php echo esc_attr( $which ); ?>">

        <?php if ( $this->has_items() ): ?>
        <div class="alignleft actions bulkactions">
            <?php $this->bulk_actions( $which ); ?>
        </div>
        <?php endif;
        $this->extra_tablenav( $which );
        $this->pagination( $which );
?>

        <br class="clear" />
    </div>
<?php
    }

    /** == == **/
    protected function extra_tablenav( $which ) {}
    
    
    /** == == **/
    public function display_rows_or_placeholder() {
        if ( $this->has_items() ) {
            $this->display_rows();
        } else {
            echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
            $this->no_items();
            echo '</td></tr>';
        }
    }

    /** == == **/
    public function display_rows() {
        foreach ( $this->items as $item )
            $this->single_row( $item );
    }

    /** == == **/
    public function single_row( $item ) {
        echo '<tr>';
        $this->single_row_columns( $item );
        echo '</tr>';
    }

    /** == == **/
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
    
    
    /** == Vues filtrées == **/
    public function views() 
    {
        $views = $this->get_views();

        if( empty( $views ) )
            return;
        
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
    
    /** == Notification == **/
    public function notices()
    {
        do_action( 'alert_notices' );
    }
                
    /** == Contenu des colonnes par défaut == **/
    public function column_default( $item, $column_name )
    {
        if( isset( $item->{$column_name} ) )
            return $item->{$column_name};
        
    }
    
    /** == Contenu de la colonne Case à cocher == **/
    public function column_cb( $item )
    {
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->db()->getPrimary(), $item->{$this->db()->getPrimary()} );
    }
    
    /** == Rendu de la page  == **/
    public function render()
    {
        get_header();
    ?>
        <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="page-header">
                            <h1>
                                <?php echo $this->PageTitle;?>
                                
                                <?php if( $this->EditBaseUri ) : ?>
                                <a class="btn btn-default" href="<?php echo $this->EditBaseUri;?>"><?php echo $this->label( 'add_new' );?></a>
                                <?php endif;?>
                            </h1>
                        </div>    
                    </div>                        
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <?php $this->notices();?>
                    
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><?php _e( 'Liste des éléments', 'tify' );?></h3>
                            </div>
                            <div class="panel-body">
                                <?php $this->views(); ?>
            
                                <form method="get" action="<?php echo $this->getConfig( 'base_url' );?>">
                                    <div class="pull-right">
                                        <?php $this->search_box( $this->label( 'search_items' ), $this->template()->getID() );?>
                                    </div>
                                    <?php $this->display();?>
                                </form>                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    <?php
        get_footer();
    }
}