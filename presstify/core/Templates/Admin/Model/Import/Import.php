<?php
namespace tiFy\Core\Templates\Admin\Model\Import;

class Import extends \tiFy\Core\Templates\Admin\Model\AjaxListTable\AjaxListTable
{                        
    /**
     * Classe de l'importateur de données
     */
    protected $Importer         = null;
    
    /**
     * Table de correspondance des données array( 'column_id' => 'input_key' )
     */ 
    protected $MapColumns                   = array();  
    
    /**
     * PARAMETRAGE
     */
    /** 
     * Définition de la cartographie des paramètres autorisés
     */
    public function set_params_map()
    {
        $params = parent::set_params_map();        
        array_push( $params, 'Importer', 'MapColumns' );
        
        return $params;
    }
    
    /**
     * Définition de la classe de l'importateur de données (désactive l'import)
     */ 
    public function set_importer()
    {
        return false;
    }  
    
    /**
     * Définition de la table de correspondance des données entre l'identifiant de colonnes et la clé des données d'import
     * ex: array( [column_id] => [input_key] );
     */
    public function set_columns_map()
    {
        return array();
    }
    
    
    /**
     * Initialisation du délimiteur du fichier d'import
     */
    public function initParamImporter()
    {               
        return $this->Importer = $this->set_importer();
    } 
    
    /**
     * Paramétrage de la table de correspondance des données entre l'identifiant de colonnes et la clé des données d'import
     */
    public function initParamMapColumns()
    {
        if( $this->set_columns_map() ) :
            $this->MapColumns = (array) $this->set_columns_map();
        else :
            $this->MapColumns = (array) $this->getConfig( 'columns_map' );
        endif;
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
        wp_localize_script( 
            'tiFyTemplatesAdminImport', 
            'tiFyTemplatesAdminImport',
            array(
                'prepare'   => __( 'Préparation de l\'import ...', 'tify' ),
                'cancel'    => __( 'Annulation en cours ...', 'tify' )
                
            )
        );
    } 
            
    /**
     * Traitement Ajax de l'import des données
     */
	public function wp_ajax_import()
	{		        
        // Initialisation des paramètres de configuration de la table
        $this->initParams(); 
        
        // Bypass 
        if( ! $this->Importer )
            return;

	    if( $input_data = $this->getResponse() ) :
            $res = call_user_func( $this->Importer .'::import', (array) current( $input_data ) );
        else :
            $res = array( 'insert_id' => 0, 'errors' => new \WP_Error( 'tiFyTemplatesAdminImport-UnavailableContent', __( 'Le contenu à importer est indisponible', 'Theme' ) ) );
        endif;
            
        if( ! empty( $res['errors'] ) && is_wp_error( $res['errors'] ) ) :
	       wp_send_json_error( array( 'message' => $res['errors']->get_error_message() ) );
	    else :
	       wp_send_json_success( array( 'message' => __( 'Le contenu a été importé avec succès', 'tify' ), 'insert_id' => $res['insert_id'] ) );
        endif;
	}
	    
    /**
     * TRAITEMENT
     */        
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
     * RECUPERATION DE PARAMETRE
     */
    /**
     * Récupération des colonnes de la table
     */
    public function get_columns() 
    {
        if( $this->Importer ) :
            return array( '_tiFyTemplatesImport_col_action' => __( 'Action', 'tify' ) ) + $this->Columns;
        else :
            return $this->Columns;
        endif;
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
        
        $output .=  "<a href=\"#\" class=\"tiFyTemplatesImport-RowImport\" data-item_index_key=\"{$this->ItemIndex}\" data-item_index_value=\"". ( isset( $item->{$this->ItemIndex} ) ? $item->{$this->ItemIndex} : 0 ) ."\" >".
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
        $ajaxActionPrefix   = $this->template()->getID() .'_'. self::classShortName();
        $datatablesAjaxData = array();        
?>
<input type="hidden" id="ajaxActionPrefix" value="<?php echo $ajaxActionPrefix;?>" />
<input type="hidden" id="datatablesAjaxData" value="<?php echo urlencode( json_encode( $datatablesAjaxData ) );?>" />
<?php
    }
    
    /**
     * Vues
     */
    public function views()
    {
?>   
<form class="tiFyTemplatesImport-Form tiFyTemplatesImport-Form--import" method="post" action="" data-id="<?php echo $this->template()->getID() .'_'. self::classShortName();?>">              
    <button type="submit" class="tiFyButton--primary tiFyTemplatesImportImportForm-Submit"><?php _e( 'Importer', 'tify' );?></button>
</form> 
<?php
        tify_control_progress(
            array(
                'id'        => 'tiFyTemplatesImport-ProgressBar',
                'title'     => __( 'Progression de l\'import', 'tify' ),
                'value'     => 0
            )
        );
        parent::views();
    }
}