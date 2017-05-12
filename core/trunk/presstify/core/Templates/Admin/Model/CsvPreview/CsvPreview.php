<?php
namespace tiFy\Core\Templates\Admin\Model\CsvPreview;

use tiFy\Inherits\Csv;

class CsvPreview extends \tiFy\Core\Templates\Admin\Model\AjaxListTable\AjaxListTable
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
     * PARAMETRAGE
     */
    /** 
     * Définition de la cartographie des paramètres autorisés
     */
    public function set_params_map()
    {
        $params = parent::set_params_map();        
        array_push( $params, 'Filename', 'FileColumns', 'Delimiter' );
        
        return $params;
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
     * AFFICHAGE
     */    
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
        $datatablesAjaxData = ! empty( $filename ) ? array( 'filename' => $filename ) : array();        
?>
<input type="hidden" id="datatablesAjaxData" value="<?php echo  urlencode( json_encode( $datatablesAjaxData ) );?>" />
<?php
    }
}