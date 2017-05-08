<?php
namespace tiFy\Core\Templates\Admin\Model\Import;

use tiFy\Inherits\Csv;

class Import extends \tiFy\Core\Templates\Admin\Model\AjaxListTable\AjaxListTable
{            
    /**
     * Répertoire d'upload
     */
    protected $UploadDir; 
    
    /**
     * Fichier d'import interne
     */
    protected $Filename; 
    
    /**
     * Habilitation de téléchargement de fichier externe
     */
    protected $Uploadable; 
    
    /**
     * Colonnes du fichier d'import
     */
    protected $FileColumns      = array();
    
    /**
     * Délimiteur de colonnes du fichier CSV
     */
    protected $Delimiter        = ',';    
    
    /**
     * Cartographie des données d'import
     * 
     * Ex :
        array(
            // Données de la table principale
            // [db_col]     => [file_col]             
            'ID'            => 'import_id'
            'post_title'    => 'import_product_title'
            
            // Metadonnées
            // [db_meta_key]    => [file_col]             
            'input_meta' => array(
                '_thumbnail_id' => 'import_thumbnail_url'                
            )
        )
     */
    protected $ImportMap        = array();
    
    /**
     * Classe de l'importateur de données
     */
    protected $Importer         = "\\tiFy\\Inherits\\Importer\\Importer";
    
    /**
     * PARAMETRAGE
     */
    /** 
     * Définition de la cartographie des paramètres autorisés
     */
    public function set_params_map()
    {
        $params = parent::set_params_map();        
        array_push( $params, 'UploadDir', 'Filename', 'Uploadable', 'FileColumns', 'Delimiter', 'Importer' );
        
        return $params;
    }
    
    /**
     * Définition du repertoire d'upload
     */ 
    public function set_upload_dir()
    {
        return '';
    } 
    
    /**
     * Définition du fichier d'import interne
     */ 
    public function set_filename()
    {
        return '';
    }
    
    /**
     * Définition si l'utilisateur est habilité à télécharger un fichier externe 
     */ 
    public function set_uploadable()
    {
        return true;
    }
    
    /**
     * Définition des colonnes du fichier d'import
     */ 
    public function set_file_columns()
    {
        return array();
    }
    
    /**
     * Définition du délimiteur de colonnes du fichier d'import
     */ 
    public function set_delimiter()
    {
        return ',';
    }
    
    /**
     * Définition de la classe de l'importateur de données
     */ 
    public function set_importer()
    {
        return "\\tiFy\\Inherits\\Importer\\Importer";
    }
    
    /**
     * Initialisation du répertoire d'upload
     */
    public function initParamUploadDir()
    {
        if( $this->UploadDir = $this->set_upload_dir() ) :
        else :
            $upload_dir = wp_upload_dir();
            $this->UploadDir = $upload_dir['basedir'];
        endif;
        
        return $this->UploadDir;
    }
    
    /**
     * Initialisation du fichier d'import externe
     */
    public function initParamFilename()
    {               
        return $this->Filename = $this->set_filename();
    }
    
    /**
     * Initialisation de l'habilitation à télécharger un fichier externe
     */
    public function initParamUploadable()
    {               
        return $this->Uploadable = $this->set_uploadable();
    }
     
    /**
     * Initialisation des colonnes du fichier d'import
     */
    public function initParamFileColumns()
    {               
        return $this->FileColumns = $this->set_file_columns();
    }
    
    /**
     * Initialisation du délimiteur du fichier d'import
     */
    public function initParamDelimiter()
    {               
        return $this->Delimiter = $this->set_delimiter();
    }   
    
    /**
     * Initialisation du délimiteur du fichier d'import
     */
    public function initParamImporter()
    {               
        return $this->Importer = $this->set_importer();
    }  
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation de l'interface d'administration privée
     * {@inheritDoc}
     * @see \tiFy\Core\Templates\Admin\Model\AjaxListTable::_admin_init()
     */
    public function _admin_init()
    {
        parent::_admin_init();       
        
        // Actions Ajax
        add_action( 'wp_ajax_'. $this->template()->getID() .'_'. self::classShortName() .'_sample', array( $this, 'wp_ajax_sample' ) );
        add_action( 'wp_ajax_'. $this->template()->getID() .'_'. self::classShortName() .'_upload', array( $this, 'wp_ajax_upload' ) );
        add_action( 'wp_ajax_'. $this->template()->getID() .'_'. self::classShortName() .'_import', array( $this, 'wp_ajax_import' ) );    
    }
    
    /**
     * Mise en file des scripts de l'interface d'administration
     * {@inheritDoc}
     * @see \tiFy\Core\Templates\Admin\Model\AjaxListTable::_admin_enqueue_scripts()
     */
    public function _admin_enqueue_scripts()
    {
        parent::_admin_enqueue_scripts();
        
        tify_control_enqueue( 'progress' );
        
        // Chargement des scripts
        wp_enqueue_style( 'tiFyTemplatesAdminImport', self::getUrl( get_class() ) .'/Import.css', array( ), 150607 );
        wp_enqueue_script( 'tiFyTemplatesAdminImport', self::getUrl( get_class() ) .'/Import.js', array( 'jquery' ), 150607 );
    } 
    
    /**
     * Affichage la page courante
     */
    public function current_screen( $current_screen )
    {
        parent::current_screen( $current_screen );
        
        // DEBUG - Tester la fonctionnalité d'import
        return;
        $attrs = array(
            'filename'      => $this->Filename,
            'columns'       => $this->FileColumns,
            'delimiter'     => $this->Delimiter            
        );
        
        $Csv = Csv::getRow( 0, $attrs );  
	    $input_data = current( $Csv->getItems() );

	    $res = call_user_func( $this->Importer .'::import', $input_data );
	    if( ! empty( $res['errors'] ) && is_wp_error( $res['errors'] ) ) :
	       echo $res['errors']->get_error_message();
	    else :
	       var_dump( $res );
        endif;	    
        exit;
    }
    
    /**
     * Traitement Ajax de téléchargement du fichier d'import
     */
    public function wp_ajax_upload()
    {                
        // Initialisation des paramètres de configuration de la table
        $this->initParams();

        // Récupération des variables de requête        
        $file         = current( $_FILES );    
        $filename     = sanitize_file_name( basename( $file['name'] ) );
    
        $response = array();
        if( ! @move_uploaded_file( $file['tmp_name'],  $this->UploadDir . "/" . $filename  ) ) :
            $response = array( 
                'success'       => false, 
                'data'          => sprintf( __( 'Impossible de déplacer le fichier "%s" dans le dossier d\'import', 'tify' ), basename( $file['name'] ) )
            );
        else :
            $response = array( 
                'success'       => false, 
                'data'          => array( 'filename' => $this->UploadDir . "/" . $filename )
            );    
        endif;
        
        wp_send_json( $response );
    }
    
    /**
     * Traitement Ajax de l'import des données
     */
	public function wp_ajax_import()
	{		
        // Initialisation des paramètres de configuration de la table
        $this->initParams();    
        
        // Attributs de récupération des données CSV
        $attrs = array(
            'filename'      => $_REQUEST['filename'],
            'columns'       => $this->FileColumns,
            'delimiter'     => $this->Delimiter            
        );		
        
        $Csv = Csv::getRow( $_REQUEST['import_index'], $attrs );  
	    $input_data = current( $Csv->getItems() );

        $res = call_user_func( $this->Importer .'::import', $input_data );
                        
        if( ! empty( $res['errors'] ) && is_wp_error( $res['errors'] ) ) :
	       wp_send_json_error( $res['errors']->get_error_messages() );
	    else :
	       wp_send_json_success( $res['insert_id'] );
        endif;
	}
	    
    /**
     * TRAITEMENT
     */    
    /**
     * Récupération de la réponse
     */
    protected function getResponse()
    {
        $params = $this->parse_query_args();
        
        if( empty( $params['filename'] ) ) 
            return;
        
        // Attributs de récupération des données CSV
        $attrs = wp_parse_args(
            array(
                'filename'      => $params['filename'],
                'columns'       => $this->FileColumns,
                'delimiter'     => $this->Delimiter,
                'query_args'    => array(
                    'paged'         => isset( $params['paged'] ) ? (int) $params['paged'] : 1,
                    'per_page'      => $this->PerPage   
                ),            
            ),
            array()
        );
        
        /// Trie
        if( ! empty( $params['orderby'] ) ) :
            $attrs['orderby'] = $params['orderby'];
        endif;
        
        /// Recherche
        if( ! empty( $params['search'] ) ) :
            $attrs['search'] = array(
                array(
                    'term'      => $params['search']
                )
            );
        endif;

        // Traitement du fichier d'import
        $Csv = Csv::getResults( $attrs );        
        $items = array();
        
        foreach( $Csv->getItems() as $import_index => $item ) :
            $item['_tiFyTemplatesImport_import_index'] = $import_index;
            $items[] = (object) $item;
        endforeach;
        
        $this->TotalItems = $Csv->getTotalItems();
        $this->TotalPages = $Csv->getTotalPages();

        return $items;
    }
    
    /**
     * Vérification d'existance d'un élément
     * @param obj $item données de l'élément
     * 
     * @return bool false l'élément n'existe pas en base | true l'élément existe en base
     */
    public function item_exists( $item )
    {
        if( ! $this->ItemIndex )
            return false;
        
        if( isset( $this->ImportMap[$this->ItemIndex] ) ) : 
            $index = $this->ImportMap[$this->ItemIndex];    
        else :
            $index = $this->ItemIndex;
        endif;
            
        if( ! isset( $item->{$index} ) )
            return false;
        
        return $this->db()->select()->has( $this->ItemIndex, $item->{$item->{$index}} );
    }    
    
    /**
     * Traitement des arguments de requête
     */
    public function parse_query_args()
    {
        // Arguments par défaut
        $query_args = parent::parse_query_args();
        
        if( isset( $_REQUEST['filename'] ) ) :
            $query_args['filename'] = $_REQUEST['filename'];
        elseif( $this->Filename ) :
            $query_args['filename'] = $this->Filename;
        endif;        
        
        return $query_args;
    }
    
    /**
     * RECUPERATION DE PARAMETRE
     */
    /**
     * Récupération des colonnes de la table
     */
    public function get_columns() 
    {
        return array( '_tiFyTemplatesImport_col_action' => __( 'Action', 'tify' ) ) + $this->Columns;
    }
    
    /**
     * AFFICHAGE
     */
    /**
     * Colonne de traitement des actions
     */
    public function column__tiFyTemplatesImport_col_action( $item )
    {
        $output = "";
        
        $output .=  "<a href=\"#\" class=\"tiFyTemplatesImport-RowImport\" data-import_index=\"". $item->_tiFyTemplatesImport_import_index ."\" >".
                        "<span class=\"dashicons dashicons-admin-generic tiFyTemplatesImport-RowImportIcon\"></span>".
                    "</a>";        
        $output .= ( $this->item_exists( $item ) ) ? "<span class=\"dashicons dashicons-paperclip tiFyTemplatesImport-ExistItem\"></span>" : "";
        
        return $output;
    }
    
    /**
     * Champs cachés
     */
    public function hidden_fields()
    {
        /**
         * Ajout dynamique d'arguments passés dans la requête ajax de récupération d'éléments
         * ex en PHP : <input type="hidden" id="datatablesAjaxData" value="<?php echo urlencode( json_encode( array( 'key' => 'value' ) ) );?>"/>
         * ex en JS : $( '#datatablesAjaxData' ).val( encodeURIComponent( JSON.stringify( resp.data ) ) );
         */ 
        $filename = isset( $query_args['filename'] ) ? $query_args['filename'] : $this->Filename;
        $ajaxActionPrefix   = $this->template()->getID() .'_'. self::classShortName();
        $datatablesAjaxData = ! empty( $filename ) ? array( 'filename' => $filename ) : array();        
?>
<input type="hidden" id="ajaxActionPrefix" value="<?php echo $ajaxActionPrefix;?>" />
<input type="hidden" id="datatablesAjaxData" value="<?php echo  urlencode( json_encode( $datatablesAjaxData ) );?>" />
<?php
    }
    
    /**
     * Rendu de la page
     */
    public function render()
    {
        tify_control_progress(
            array(
                'id'        => 'tiFyTemplatesImport-ProgressBar',
                'title'     => __( 'Progression de l\'import', 'tify' ),
                'value'     => 0
            )
        );
?>
<div class="wrap">
    <h2>
        <?php echo $this->PageTitle;?>
        
        <?php if( $this->EditBaseUri ) : ?>
            <a class="add-new-h2" href="<?php echo $this->EditBaseUri;?>"><?php echo $this->label( 'add_new' );?></a>
        <?php endif;?>
    </h2>

    <div>
        <?php if( $this->Uploadable ) :?>
        <form class="tiFyTemplatesImport-Form tiFyTemplatesImport-Form--upload" method="post" action="" enctype="multipart/form-data" data-id="<?php echo $this->template()->getID() .'_'. self::classShortName();?>">              
            <input class="tiFyTemplatesImportUploadForm-FileInput" type="file" autocomplete="off" />
            <span class="spinner tiFyTemplatesImportUploadForm-Spinner"></span>
        </form> 
        <?php endif;?>
        
        <form class="tiFyTemplatesImport-Form tiFyTemplatesImport-Form--import" method="post" action="" data-id="<?php echo $this->template()->getID() .'_'. self::classShortName();?>">              
            <button type="submit" class="tiFyTemplatesImportImportForm-Submit"><?php _e( 'Importer', 'tify' );?></button>
        </form> 
    </div>
    
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
}