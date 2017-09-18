<?php
namespace tiFy\Core\Templates\Admin\Model\FileImport;

use tiFy\Inherits\Csv;

class FileImport extends \tiFy\Core\Templates\Admin\Model\Import\Import
{                
    /**
     * Fichier d'import interne
     */
    protected $Filename; 
    
    /**
     * Colonnes du fichier d'import
     */
    protected $FileColumns      = array();
    
    /**
     * Délimiteur de colonnes du fichier CSV
     */
    protected $Delimiter        = ',';    
    
    /**
     * Habilitation de téléchargement de fichier externe
     */
    protected $Uploadable; 
    
    /**
     * Répertoire d'upload
     */
    protected $UploadDir;

    /**
     * Données complémentaires d'import
     */
    protected $ImportMisc       = [];

    /**
     * PARAMETRAGE
     */    
    /** 
     * Définition de la cartographie des paramètres autorisés
     */
    public function set_params_map()
    {
        $params = parent::set_params_map();
        array_push( $params, 'Filename', 'FileColumns', 'Delimiter', 'Utf8Encode', 'Uploadable', 'UploadDir', 'ImportMisc' );
        
        return $params;
    }
    
    /**
     * Définition de la clé primaire d'un élément
     */
    public function set_item_index()
    {
        return '_import_row_index';
    }
        
    /**
     * Définition du fichier d'import interne
     */ 
    public function set_filename()
    {
        return '';
    }
        
    /**
     * Définition des colonnes du fichier d'import
     */ 
    public function set_file_columns()
    {
        return [];
    }
    
    /**
     * Définition du délimiteur de colonnes du fichier d'import
     */ 
    public function set_delimiter()
    {
        return ',';
    }
    
    /**
     * Définition du repertoire d'upload
     */ 
    public function set_upload_dir()
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
     * Définition des données complémentaires d'import
     */
    public function set_import_misc()
    {
        return [];
    }
        
    /**
     * Initialisation du fichier d'import externe
     */
    public function initParamFilename()
    {               
        return $this->Filename = $this->set_filename();
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
     * Initialisation de l'habilitation à télécharger un fichier externe
     */
    public function initParamUploadable()
    {               
        return $this->Uploadable = $this->set_uploadable();
    }
    
    /**
     * Données passées dans la requête de récupération Ajax de Datatables
     */
    public function getAjaxDatatablesData()
    {
        return wp_parse_args(
            array(
                'filename' => $this->QueryArgs['filename']
            ),
            parent::getAjaxDatatablesData()
        );
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
        add_action( 'wp_ajax_'. $this->template()->getID() .'_'. self::classShortName() .'_fileimport_upload', array( $this, 'wp_ajax_upload' ) );  
    }
    
    /**
     * Mise en file des scripts de l'interface d'administration
     * {@inheritDoc}
     * @see \tiFy\Core\Templates\Admin\Model\AjaxListTable::_admin_enqueue_scripts()
     */
    public function _admin_enqueue_scripts()
    {
        parent::_admin_enqueue_scripts();

        // Chargement des scripts
        wp_enqueue_style( 'tiFyTemplatesAdminFileImport', self::tFyAppAssetsUrl('FileImport.css', get_class()), array( ), 150607 );
        wp_enqueue_script( 'tiFyTemplatesAdminFileImport', self::tFyAppAssetsUrl('FileImport.js', get_class()), array( 'jquery' ), 150607 );
    } 
    
    /**
     * Affichage la page courante
     */
    public function _current_screen( $current_screen = null )
    {
        //$_REQUEST['_import_row_index'] = 0;
        
        parent::_current_screen($current_screen);
        
        // DEBUG - Tester la fonctionnalité d'import > Décommenter $_REQUEST['_import_row_index'] et commenter le return (ligne suivante)
        return;

        if (! $this->Importer)
            return;

        if (isset($this->items[$_REQUEST['_import_row_index']])) :
            $res = call_user_func($this->Importer .'::import', (array) $this->items[$_REQUEST['_import_row_index']]);
        elseif ($this->items) :
            $res = call_user_func($this->Importer .'::import', (array) current($this->items));
        else :
            $res = array('insert_id' => 0, 'errors' => new \WP_Error( 'tiFyTemplatesAdminImport-UnavailableContent', __( 'Le contenu à importer est indisponible', 'Theme' ) ) );
        endif;
            
        if (! empty($res['errors']) && is_wp_error($res['errors'])) :
            wp_send_json_error( array( 'message' => $res['errors']->get_error_message() ) );
        else :
            wp_send_json_success( array( 'message' => __( 'Le contenu a été importé avec succès', 'tify' ), 'insert_id' => $res['insert_id'] ) );
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
        $file         = current($_FILES);
        $filename     = sanitize_file_name( basename( $file['name'] ) );
    
        $response = array();
        if( ! @ move_uploaded_file( $file['tmp_name'],  $this->UploadDir . "/" . $filename  ) ) :
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
     * TRAITEMENT
     */
    /**
     * Traitement des arguments de requête
     */
    public function parse_query_args()
    {
        // Arguments par défaut
        parent::parse_query_args();
        
        if( isset( $_REQUEST['filename'] ) ) :
            $this->QueryArgs['filename'] = $_REQUEST['filename'];
        elseif( $this->Filename ) :
            $this->QueryArgs['filename'] = is_array( $this->Filename ) ? current( $this->Filename ) : (string) $this->Filename;
        else :
            $this->QueryArgs['filename'] = '';
        endif;        
        
        return $this->QueryArgs;
    }
    
    /**
     * Récupération de la réponse
     */
    protected function getResponse()
    {
        $params = $this->parse_query_args();
        
        if (empty( $params['filename']))
            return;

        // Attributs de récupération des données CSV
        if ($this->current_item()) :
            $attrs = array(
                'filename'      => $params['filename'],
                'columns'       => $this->FileColumns,
                'delimiter'     => $this->Delimiter
            );
            $Csv = Csv::getRow(current($this->current_item()), $attrs);
        else :
            $attrs = array(
                'filename'      => $params['filename'],
                'columns'       => $this->FileColumns,
                'delimiter'     => $this->Delimiter,
                'query_args'    => array(
                    'paged'         => isset( $params['paged'] ) ? (int) $params['paged'] : 1,
                    'per_page'      => $this->PerPage
                ),            
            );
            
            /// Trie
            if (! empty($params['orderby'])) :
                $attrs['orderby'] = $params['orderby'];
            endif;
            
            /// Recherche
            if (! empty($params['search'])) :
                $attrs['search'] = array(
                    array(
                        'term'      => $params['search']
                    )
                );
            endif;
            // Traitement du fichier d'import
            $Csv = Csv::getResults( $attrs );
        endif;
                
        $items = array();
        
        foreach($Csv->getItems() as $import_index => $item) :
            $item['_import_row_index'] = $import_index;
            $items[] = (object) $item;
        endforeach;
                
        $this->TotalItems = $Csv->getTotalItems();
        $this->TotalPages = $Csv->getTotalPages();

        return $items;
    }
            
    /**
     * AFFICHAGE
     */        
    /**
     * Vues
     */
    public function views()
    {   
        // Import de fichier personnel
        if( $this->Uploadable ) :        
?>
<form class="tiFyTemplatesFileImport-Form tiFyTemplatesFileImport-Form--upload" method="post" action="" enctype="multipart/form-data" data-id="<?php echo $this->template()->getID() .'_'. self::classShortName();?>">              
    <strong><?php _e( 'Import de fichier personnel :', 'tify' );?></strong><br>
    
    <input class="tiFyTemplatesFileImportUploadForm-FileInput" type="file" autocomplete="off" />
    <span class="spinner tiFyTemplatesFileImportUploadForm-Spinner"></span>
</form> 
<?php
        endif;
        
        // Liste de fichiers à traiter
        if( is_array( $this->Filename ) ) :
?>
<form method="post" action="">
    <strong><?php _e( 'Choix du fichier à traiter :', 'tify' );?></strong><br>
    
    <select name="filename">
    <?php foreach( $this->Filename as $filename ) :?>
        <option value="<?php echo $filename;?>" <?php selected( $filename, $this->QueryArgs['filename'] );?>><?php echo $filename;?></option>        
    <?php endforeach;?>
    </select>
    <button type="submit" class="button-secondary"><?php _e('Traiter', 'tify');?></button>
</form>
<?php   endif;
        
        // Indication de fichier en traitement
        if ($this->QueryArgs['filename']) :
?>
<div class="tiFyTemplatesFileImport-handleFilename">
    <strong class="tiFyTemplatesFileImport-handleFilenameLabel"><?php _e( 'Fichier en cours de traitement :', 'tify' );?></strong>
    <div class="tiFyTemplatesFileImport-handleFilenameValue"><?php echo $this->QueryArgs['filename'];?></div>
</div>
<?php         
        endif;
?>
<div class="tiFyTemplatesFileImport-options">
    <strong class="tiFyTemplatesFileImport-optionsLabel"><?php _e( 'Options d\'import :', 'tify' );?></strong>
    <div class="tiFyTemplatesFileImport-optionsForm">


    </div>
</div>
<?php
        parent::views();
    }
    
    /**
     * Champs cachés
     */
    public function hidden_fields()
    {        
        parent::hidden_fields();
        
?><input type="hidden" id="ajaxActionFileImport" value="<?php echo $this->template()->getID() .'_'. self::classShortName() .'_fileimport_upload';?>" /><?php
    }
}
