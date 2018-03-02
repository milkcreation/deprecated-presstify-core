<?php 
namespace tiFy\Core\Templates\Traits\Table;

trait Params
{
    /* = CONTROLEURS = */
    /** == Formatage du nom d'un paramètre == **/
    protected function sanitizeParam( $param )
    {
        return implode( array_map( 'ucfirst', explode( '_', $param ) ) );
    }
    
    /** == Récupération de la liste de paramètres permis == **/
    protected function allowedParams()
    {
        return $this->ParamsMap;
    }
    
    /** == Définition d'un paramètre == **/
    protected function setParam( $param, $value )
    {
        $param = self::sanitizeParam( $param );
        if( in_array( $param, $this->allowedParams() ) ) :
            $this->{$param} = $value;  
        endif;
    }
    
    /** == Récupération d'un paramètre == **/
    protected function getParam( $param, $default = '' )
    {
        $param = self::sanitizeParam( $param );
        if( ! in_array( $param, $this->allowedParams() ) )
            return $default;
        
        if( method_exists( $this, 'get'. $param ) ) :
            return call_user_func( array( $this, 'get'. $param ) );
        elseif( isset( $this->{$param} ) ) :
            return $this->{$param};
        endif; 
        
        return $default;
    }
    
    /** == Initialisation des paramètres de configuration de la table == **/
    protected function initParams()
    {
        $this->ParamsMap = $this->set_params_map();

        foreach( (array) $this->allowedParams() as $param ) :
            if( ! method_exists( $this, 'initParam' . $param ) ) 
                continue;
            call_user_func( array( $this, 'initParam' . $param ) );
        endforeach;
    }
    
    /** 
     * Définition de la cartographie des paramètres autorisés
     */
    public function set_params_map()
    {
        return $this->ParamsMap;
    }
    
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
    
    /** == Définition de la clé primaire d'un élément == **/
    public function set_item_index()
    {
        return '';
    }
    
    /**
     * Définition des colonnes de la table
     */
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
    
    /** == Définition des colonnes de prévisualisation == **/
    public function set_preview_columns()
    {
        return array();
    }
    
    /** == Définition du mode de prévisualisation == **/
    public function set_preview_mode()
    {
        return 'dialog';
    }
    
    /**
     * Définition des données ajax passées dans la requête de prévisualisation
     */
    public function set_preview_ajax_datas()
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
    
    /** == Définition du préfixe des actions ajax == **/
    public function set_ajax_action_prefix()
    {
        return $this->template()->getID() .'_'. self::classShortName();
    }
    
    /** == Définition du titre de la page == **/
    public function set_page_title()
    {
        return '';
    }
    
    /** == Initialisation de l'url de la page d'administration == **/
    public function initParamBaseUri()
    {
        $this->BaseUri = $this->getConfig( 'base_url' );
    }
    
    /** == Initialisation de l'url d'édition d'un élément == **/
    public function initParamEditBaseUri()
    {
        if( $this->EditBaseUri = $this->set_edit_base_url() ) :
        elseif( $edit_template = $this->getConfig( 'edit_template' ) ) :
            $Method = ( $this->template()->getContext() === 'admin' ) ? 'getAdmin' : 'getFront';

            $this->EditBaseUri = \tiFy\Core\Templates\Templates::$Method( $edit_template )->getAttr( 'base_url' );
        elseif( $this->EditBaseUri = $this->getConfig( 'edit_base_url' ) ) :
        endif;
    }
    
    /** == Initialisation de l'intitulé des objets traités == **/
    public function initParamPlural()
    {
        if( ! $plural = $this->set_plural() )
            $plural = $this->template()->getID();
        
        $this->Plural = sanitize_key( $plural );
    }
    
    /** == Initialisation de l'intitulé d'un objet traité == **/
    public function initParamSingular()
    {
        if( ! $singular = $this->set_singular() )
            $singular = $this->template()->getID();
        
        $this->Singular = sanitize_key( $singular );
    }
        
    /** == Initialisation des notifications == **/
    public function initParamNotices()
    {
        $this->Notices = $this->parseNotices( $this->set_notices() );
    }
    
    /** == Initialisation des statuts == **/
    public function initParamStatuses()
    {
        $this->Statuses = $this->set_statuses();
    }
    
    /** == Initialisation des vues filtrées == **/
    public function initParamFilteredViewLinks()
    {            
        $this->FilteredViewLinks = $this->parseViews( $this->set_views() );
    }
    
    /** == Initialisation des classes de la table == **/
    public function initParamTableClasses()
    {
        $this->TableClasses = $this->set_table_classes();    
    }
    
    /** == Initialisation de l'indice de clé primaire d'un élément == **/
    public function initParamItemIndex()
    {
        if( $index = $this->set_item_index() ) :
        elseif( $index = $this->getConfig( 'item_index' ) ) :
        elseif( $this->db() ) :
            $index = $this->db()->getPrimary();
        else :
            $index = null;
        endif;

        if( $index )
            $this->ItemIndex = $index;
    }
    
    /** == Initialisation des colonnes de la table == **/
    public function initParamColumns()
    {    
        if( $columns = $this->set_columns() ) :
        elseif( $columns = $this->getConfig( 'columns' ) ) :
        else :
            $columns = array();
            $columns['cb'] = "<input id=\"cb-select-all-1\" type=\"checkbox\" />";
            foreach( (array)  $this->db()->ColNames as $name ) :
                $columns[$name] = $name;
            endforeach;
        endif;
        
        $this->Columns = $columns;
    }
    
    /** == Initialisation des colonnes triables == **/
    public function initParamSortableColumns()
    {
        $this->SortableColumns = $this->set_sortable_columns();
    }
    
    /** == Initialisation des colonnes masquées == **/
    public function initParamHiddenColumns()
    {
        if( $hidden_cols = $this->set_hidden_columns() ) :
        elseif( $hidden_cols = $this->getConfig( 'hidden_columns' ) ) :
        else :
            $hidden_cols = array();
        endif;

        if( $hidden_cols ) :
            $this->HiddenColumns = $hidden_cols;
            add_filter( 'hidden_columns', function( $hidden, $screen, $use_defaults ) use ( $hidden_cols ){ return $hidden_cols; }, 10, 3 );
        endif;
    }
    
    /** == Initialisation des colonnes de prévisualisation == **/
    public function initParamPreviewColumns()
    {
        if( $preview_cols = $this->set_preview_columns() ) :
        elseif( $preview_cols = $this->getConfig( 'preview_columns' ) ) :
        else :
            $preview_cols = array();
        endif;

        if( $preview_cols ) :
            $this->PreviewColumns = $preview_cols;
        endif;
    }
    
    /** == Initialisation du mode de prévisualisation == **/
    public function initParamPreviewMode()
    {
        if( in_array( $this->set_preview_mode(), array( 'dialog', 'row' ) ) ) :
             $this->PreviewMode = $this->set_preview_mode();
        elseif( in_array( $this->getConfig( 'preview_mode' ), array( 'dialog', 'row' ) ) ) :
            $this->PreviewMode = $this->getConfig( 'preview_mode' );
        else :
            $this->PreviewMode = false;
        endif;        
    }
    
    /**
     * Initialisation des données passées dans la requête Ajax de prévisualisation
     */
    public function initParamPreviewAjaxDatas()
    {
        if( $preview_ajax_datas = $this->set_preview_ajax_datas() ) :
        elseif( $preview_ajax_datas = $this->getConfig( 'preview_ajax_datas' ) ) :
        else :
            $preview_ajax_datas = array();
        endif;
        
        if( ! isset( $preview_ajax_datas['_ajax_nonce'] ) ) :
            $preview_ajax_datas['_ajax_nonce'] = wp_create_nonce( 'tiFyCoreTemplatesAdminTablePreview' );
        endif;
            
        $this->PreviewAjaxDatas = $preview_ajax_datas;
    }
    
    /** == Initialisation de la colonne principale == **/
    public function initParamPrimaryColumn()
    {
        if( $primary = $this->set_primary_column() ) :
        elseif( $primary = $this->getConfig( 'primary_column' ) ) :
        else :
            $primary = null;
        endif;
        
        if( $primary ) :
            $this->PrimaryColumn = $primary;
            add_filter( 'list_table_primary_column', function( $default ) use ( $primary ){ return $primary; }, 10, 1 );
        endif;
    }
    
    /** == Initialisation des arguments de requête == **/
    public function initParamQueryArgs()
    {
        $this->QueryArgs = wp_parse_args( $this->set_query_args(), $this->QueryArgs );
    }
    
    /** == Initialisation du nombre d'éléments affichés par page == **/
    public function initParamPerPage()
    {
        $this->PerPage = ( $per_page = (int) $this->set_per_page() ) ? $per_page : 20;    
    }
    
    /** == == **/
    public function initParamPerPageOptionName()
    {
        if( ! $per_page_option = $this->set_per_page_option_name() )
            return;
            
        $per_page_option = is_bool( $per_page_option ) ? $this->template()->getID() .'_per_page' : (string) $per_page_option;
        add_filter( 'set-screen-option', function( $none, $option, $value ) use ( $per_page_option ){ return ( $per_page_option  ===  $option ) ? $value : $none; }, 10, 3 );
        $per_page = $this->PerPage;
        add_filter( $this->PerPageOptionName, function() use ( $per_page ){ return $per_page; }, 0 );
    }
    
    /** == Initialisation de l'intitulé lorsque la table est vide == **/
    public function initParamNoItems()
    {
        $this->NoItems = ( $no_items = $this->set_no_items() ) ? $no_items :  ( ( $no_items = $this->label( 'not_found' ) ) ? $no_items : __( 'No items found.' ) );    
    }
    
    /** == Initialisation des actions groupées == **/
    public function initParamBulkActions()
    {
        $this->BulkActions = $this->set_bulk_actions();    
    }
    
    /** == Initialisation des actions sur un élément de la liste == **/
    public function initParamRowActions()
    {
        foreach( (array) $this->set_row_actions() as $action => $attr ) :
            if( is_int( $action ) ) :
                $this->RowActions[$attr] = array();
            else :
                $this->RowActions[$action] = $attr;
            endif;
        endforeach;    
    }
    
    /** == Initialisation des actions sur un élément de la liste == **/
    public function initParamPageTitle()
    {
        $this->PageTitle = ( $page_title = $this->set_page_title() ) ? $page_title : $this->label( 'all_items' );
    }
}