<?php
namespace tiFy\Core\Templates\Admin\Model;

use tiFy\Apps;
use tiFy\App\Traits\App as TraitsApp;
use tiFy\Core\Templates\Admin\Helpers;

/** 
 * @see https://codex.wordpress.org/Class_Reference/WP_List_Table
 */
if( ! class_exists( 'WP_List_Table' ) )
    require_once( ABSPATH .'wp-admin/includes/class-wp-list-table.php' );
    
abstract class Table extends \WP_List_Table
{
    use TraitsApp;
    use \tiFy\Core\Templates\Traits\Table\Actions;
    use \tiFy\Core\Templates\Traits\Table\Notices;
    use \tiFy\Core\Templates\Traits\Table\Params;
    use \tiFy\Core\Templates\Traits\Table\Views;

    /* = ARGUMENTS = */
    // Écran courant
    protected $Screen                   = null;

    // Configuration
    /// Url de la page d'administration
    protected $BaseUri                  = null;

    /// Url de la page d'édition d'un élément
    protected $EditBaseUri              = null;

    // Intitulé des objets traités
    protected $Plural                   = null;

    // Intitulé d'un objet traité
    protected $Singular                 = null;

    /// Message de notification
    protected $Notices                  = array();

    /// Liste des statuts des objets traités
    protected $Statuses                 = array();

    /// Liens de vue filtrées
    protected $FilteredViewLinks        = array();    

    /// Indic de clé primaire d'un élément
    protected $ItemIndex                = array();

    /// Colonnes de la table
    protected $Columns                  = array();

    /// Colonne principale de la table
    protected $PrimaryColumn            = null;

    /// Colonnes selon lesquelles les éléments peuvent être triés
    protected $SortableColumns          = array();

    /// Colonnes Masquées
    protected $HiddenColumns            = array();
    
    /// Colonnes de prévisualisation
    protected $PreviewColumns           = array();
    
    /// Mode de prévisualisation
    protected $PreviewMode              = 'dialog';
    
    /// Données Ajax passées dans la requête de prévisualisation
    protected $PreviewAjaxDatas         = array();

    /// Nombre d'éléments affichés par page
    protected $PerPage                  = null;

    /// 
    protected $PerPageOptionName        = null;

    /// Arguments de requête
    protected $QueryArgs                = array();

    /// Intitulé affiché lorsque la table est vide
    protected $NoItems                  = '';

    /// Actions groupées
    protected $BulkActions              = array();

    /// Actions sur un élément
    protected $RowActions               = array();
        
    /// Titre de la page
    protected $PageTitle                = null;

    /// Cartographie des paramètres
    protected $ParamsMap                = array( 
        'BaseUri', 'EditBaseUri', 'Plural', 'Singular', 'Notices', 'Statuses', 'FilteredViewLinks', 
        'ItemIndex', 'Columns', 'PrimaryColumn', 'SortableColumns', 'HiddenColumns', 'PreviewColumns', 'PreviewMode', 'PreviewAjaxDatas',
        'PerPage', 'PerPageOptionName',
        'QueryArgs', 'NoItems', 'BulkActions', 'RowActions',
        'PageTitle'
    );
    
    protected $compat_fields            = array( 
        '_args', '_pagination_args', 'screen', '_actions', '_pagination',  
        'template', 'db', 'label', 'getConfig' 
    );
    
    /* = CONSTRUCTEUR = */
    /** == ! IMPORTANT : court-circuitage du constructeur natif de WP_List_Table == **/
    public function __construct()
    {
        self::_tFyAppRegister($this);
    }
    
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
                            
    /** == Initialisation de la classe table native de Wordpress == **/
    final public function _wp_list_table_init( $args = array() )
    {
        parent::__construct(
            wp_parse_args(
                $args,
                array(
                    'plural'     => $this->Plural,
                    'singular'     => $this->Singular,
                    'ajax'         => true,
                    'screen'     => null
                )
            )             
        );
    }
    
    /* = DECLENCHEURS = */
    /** == Initialisation globale == **/
    public function _init()
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
        add_action( 'wp_ajax_'. $this->template()->getID() .'_'. self::classShortName() . '_inline_preview', array( $this, 'wp_ajax_inline_preview' ) );
    }
    
    /** == Initialisation de l'interface d'administration == **/
    public function _admin_init(){}
    
    /** == Affichage de l'écran courant == **/
    public function _current_screen( $current_screen = null )
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
    
    /** == Mise en file des scripts de l'interface d'administration == **/
    public function _admin_enqueue_scripts()
    {
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style( 'wp-jquery-ui-dialog');
    }    
    
    /** == == **/
    public function _admin_print_footer_scripts()
    { 
?><script type="text/javascript">/* <![CDATA[ */
jQuery(document).ready( function($){
    $( document ).on( 'click', '#the-list .row-actions .previewinline a', function(e){
        e.preventDefault();

        var index = $(this).data( 'index' );
            $closest = $(this).closest( 'tr' );

        if( $closest.next().attr('id') != 'inline-preview-'+ index ){
            // Création de la zone de prévisualisation
            $preview = $( '#inline-previewer' ).clone(true);
            var id      = 'inline-preview-'+ index,
                data    = $.extend( 
                    {
                        action: '<?php echo $this->template()->getID() .'_'. self::classShortName() . '_inline_preview';?>', 
                        '<?php echo $this->ItemIndex?>': index
                    },
                    JSON.parse( decodeURIComponent( $( '#previewAjaxData' ).val() ) )
                );
                
            $preview
                .attr( 'id', id )
                .hide();
            $closest.after( $preview );            
            
            <?php if( $this->PreviewMode === 'dialog') : ?>
            $( '#'+ id ).dialog({
               autoOpen: false,
               draggable: false,
               width: 'auto',
               modal: true,
               resizable: false,
               closeOnEscape: true,
               position: 
               {
                   my: "center",
                   at: "center",
                   of: window
               },
               open: function(){
                   $('.ui-widget-overlay').bind('click', function(){
                       $( '#'+ id ).dialog('close');
                   });
               },
               create: function () {
                   $('.ui-dialog-titlebar-close').addClass('ui-button');
               },
            });            
            <?php endif;?>
            // Récupération de l'élément à prévisualiser
            $.post( 
                tify_ajaxurl, 
                data, 
                function( resp ){
                    $( '.content', $preview ).html(resp);
                    <?php if( $this->PreviewMode === 'dialog') : ?>
                    $( '#'+ id ).dialog( 'open' );            
                    <?php endif;?>
                }
            );                 
        } else {
            $preview = $closest.next();
        }   
            
        $preview.toggle();    
                
        return false;
    });
});/* ]]> */</script><?php        
    }    
    
    /** == Action ajax de récupération de la prévisualisation en ligne == **/
    public function wp_ajax_inline_preview()
    {        
        check_ajax_referer( 'tiFyCoreTemplatesAdminTablePreview' );
        
        $this->initParams();     
        $this->prepare_items();
        $item = current( $this->items );
        $this->preview($item);
        die();
    }    
    
    /* = TRAITEMENT = */
    /** == Récupération de l'élément à traité == **/
    public function current_item() 
    {
        if (isset($_REQUEST[$this->ItemIndex])) :
            if (is_array($_REQUEST[$this->ItemIndex])) :
                return array_map('intval', $_REQUEST[$this->ItemIndex]);
            else :
                return [(int)$_REQUEST[$this->ItemIndex]];
            endif;
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
        $total_items    = $query->found_items;
        $per_page       = $this->get_items_per_page( $this->db()->Name, $this->PerPage );
        $this->set_pagination_args( 
            array(
                'total_items'         => $total_items,                  
                'per_page'            => $per_page,                    
                'total_pages'         => ceil( $total_items / $per_page )
            ) 
        );
    }
    
    /** == Traitement des arguments de requête == **/
    public function parse_query_args()
    {
        // Récupération des arguments        
        $per_page   = $this->get_items_per_page( $this->db()->Name, $this->PerPage );
        $paged      = $this->get_pagenum();

        // Arguments par défaut
        $query_args = array(                        
            'per_page'      => $per_page,
            'paged'         => $paged,
            'order'         => 'DESC',
            'orderby'       => $this->db()->Primary
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
    
    /**
     * AFFICHAGE
     */
    /**
     * Contenu par défaut des colonnes
     */
    public function column_default($item, $column_name)
    {
        // Bypass 
        if (!isset($item->{$column_name})) :
            return;
        endif;

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
    
    /**
     * Contenu de la colonne - Case à cocher
     */
    public function column_cb( $item )
    {
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->db()->Primary, $item->{$this->db()->Primary} );
    }
       
    /**
     * Rendu de la page
     */
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
        <?php $this->hidden_fields();?>
    
        <?php $this->search_box( $this->label( 'search_items' ), $this->template()->getID() );?>
        <?php $this->display();?>
        <?php $this->inline_preview();?>
    </form>
</div>
<?php
    }
    
    /**
     * Champs cachés
     */
    public function hidden_fields()
    {
        if( $this->PreviewMode ) :
?><input type="hidden" id="previewAjaxData" value="<?php echo urlencode( json_encode( $this->PreviewAjaxDatas ) );?>" /><?php
        endif;
    }
    
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
    
    /** == == **/
    public function preview( $item )
    {        
        if( ! $columns = $this->PreviewColumns ) :
            $columns = $this->Columns;
            unset( $columns['cb'] );
        endif;
?>
<table class="form-table">
    <tbody>
    <?php foreach( $columns as $column_name => $column_label ) :?>
        <tr valign="top">
            <th scope="row">
                <label><strong><?php echo $column_label;?></strong></label>
            </th>
            <td>
            <?php
            if( method_exists( $this, 'preview_' . $column_name ) ) :
                echo call_user_func( array( $this, 'preview_' . $column_name ), $item );
            else :
                echo $this->preview_default( $item, $column_name );
            endif;
            ?>
            </td>
        </tr>
    <?php endforeach;?>
    </tbody>
</table>
<div class="clear"></div>
<?php
    }
    
    /** == Contenu de l'aperçu par défaut == **/
    public function preview_default( $item, $column_name )
    {
        if ( method_exists( $this, '_column_' . $column_name ) ) :
            return call_user_func( array( $this, '_column_' . $column_name ), $item );
        elseif( method_exists( $this, 'column_' . $column_name ) ) :
            return call_user_func( array( $this, 'column_' . $column_name ), $item );
        else :
            return $this->column_default($item, $column_name);
        endif;        
    }
    
    /** == Aperçu en ligne == **/
    public function inline_preview()
    {
        switch( $this->PreviewMode ) :
            case 'dialog' :
?>
<div id="inline-previewer" class="hidden" style="max-width:800px; min-width:800px;">
	<div class="content"></div>
</div>
<?php
                break;
            case 'row' :
?>
<table class="hidden">
    <tbody>
        <tr id="inline-previewer">
            <td class="content" colspan="<?php echo count( $this->get_columns() );?>">
                <h3><?php _e( 'Chargement en cours ...', 'tify' );?></h3>
            </td>
        </tr>    
    </tbody>
</table>
<?php
                break;
        endswitch;
    }
}