<?php
namespace tiFy\Inherits\Importer;

class Post extends \tiFy\Inherits\Importer\Importer
{
    /* = ARGUMENTS = */
    // Données d'entrée
    public $InputData       = array();
    
    // Cartographie des données principales
    public $DataMap         = array();
    
    // Cartographie des metadonnées
    public $MetadataMap     = array();
    
    // Cartographie des options
    public $TaxonomyMap     = array();
        
    // Donnée permises
    protected $AllowedData  = array(
        'ID',
        'post_author',
        'post_date',
        'post_date_gmt',
        'post_content',
        'post_content_filtered',
        'post_title',
        'post_excerpt',
        'post_status',
        'post_type',
        'comment_status',
        'ping_status',
        'post_password',
        'post_name',
        'to_ping',
        'pinged',
        'post_modified',
        'post_modified_gmt',
        'post_parent',
        'menu_order',
        'post_mime_type',
        'guid',
        'post_category',
        // Réservés
        //'tax_input',
        //'meta_input'
    );
    
    // Données traités
    protected $Data         = array();
    
    // Erreurs
    protected $Errors       = array();
        
    /**
     * Import d'un post
     */
    final public static function import( $inputdata = array(), $attrs = array() )
    {        
        $import = new static();
        $import->parseAttrs( $attrs );
        $import->parseInputData( $inputdata );
        $import->filterDataValue();
        $import->checkDataValue();
        
        if( $errors = $import->getErrors() ) :
            $WpError = new \WP_Error;
            foreach( $errors as $code => $message ) :
                $WpError->add( $code, $message );
            endforeach;
            
            return $WpError;
        else :
            extract( $import->getData() );
            
            if( isset( $postmeta ) ) :
                $postdata['meta_input'] = $postmeta;
            endif;

            $post_id = wp_insert_post( $postdata );    
               
            return $post_id;
        endif; 
    }
    
    /**
     * Traitement des attributs
     */
    final public function parseAttrs( $attrs = array() )
    {
        // Cartographie des données
        $this->DataMap = isset( $attrs['data_map'] ) ? $attrs['data_map'] : $this->setDataMap();
        $this->MetadataMap = isset( $attrs['metadata_map'] ) ? $attrs['metadata_map'] : $this->setMetadataMap();
        $this->TaxonomyMap = isset( $attrs['taxonomy_map'] ) ? $attrs['taxonomy_map'] : $this->setOptionMap();
    }
    
    /**
     * Traitement des données d'entrée
     */
    final public function parseInputData( $inputdata = array() )
    {
        if( empty( $inputdata ) )
            $inputdata = $this->setInputData();            
            
        // Traitement des données d'entrée principales
        $postdata = array();
        
        /// Données cartographiées        
        if( $this->DataMap ) :
            foreach( $this->DataMap as $data_key => $map_key ) :
                if( is_numeric( $data_key ) ) :
                    $data_key = $map_key;
                endif;    
                // Bypass
                if( ! in_array( $data_key, $this->AllowedData ) ) :
                    continue;
                endif;
                    
                if( isset( $inputdata[$map_key] ) ) :
                    $postdata[$data_key] = $inputdata[$map_key]; 
                elseif( method_exists( $this, 'set_' . $data_key ) ) :
                    $postdata[$data_key] = call_user_func( array( $this, 'set_' . $data_key ) );
                else :
                    $postdata[$data_key] = call_user_func( array( $this, 'set_default' ), $data_key );
                endif;
            endforeach;
            
        /// Données non cartographiées     
        else :
            foreach( $this->AllowedData as $data_key ) :
                if( $inputdata[$data_key] ) :
                    $postdata[$data_key] = $inputdata[$data_key];
                elseif( method_exists( $this, 'set_' . $data_key ) ) :
                    $postdata[$data_key] = call_user_func( array( $this, 'set_' . $data_key ) ); 
                else :
                    $postdata[$data_key] = call_user_func( array( $this, 'set_default' ), $data_key );
                endif;
            endforeach;
        endif;
        
        // Traitement des metadonnées
        $postmeta = array();
        /// Données cartographiées        
        if( $this->MetadataMap ) :
            foreach( $this->MetadataMap as $meta_key => $map_key ) :
                if( is_numeric( $meta_key ) ) :
                    $meta_key = $map_key;
                endif;

                if( isset( $inputdata[$map_key] ) ) :
                    $postmeta[$meta_key] = $inputdata[$map_key];
                elseif( method_exists( $this, 'set_meta_' . $meta_key ) ) :
                    $postmeta[$meta_key] = call_user_func( array( $this, 'set_meta_' . $meta_key ) ); 
                else :
                    $postmeta[$meta_key] = call_user_func( array( $this, 'set_meta_default' ), $meta_key );
                endif;
            endforeach;
        else :
            if( $matches = preg_grep( '/^set_meta_(.*)/', get_class_methods( $this ) ) ) :
                foreach( $matches as $method ) :
                    $meta_key = preg_replace( '/^set_meta_/', '', $method );
                    $postmeta[$meta_key] = call_user_func( array( $this, 'set_meta_' . $meta_key ) );
                endforeach;
            endif;            
        endif;
        
        $this->Data = compact( 'postdata', 'postmeta' );
    }
    
    /**
     * Filtrage de valeur des données d'entrée
     */
    final public function filterDataValue()
    {
        extract( $this->Data );

        if( isset( $postdata ) ) :
            foreach( $postdata as $data_key => &$data_value ) :
                if( method_exists( $this, 'filter_' . $data_key ) ) :
                    $data_value = call_user_func( array( $this, 'filter_' . $data_key ), $data_value );       
                endif;
            endforeach;
        endif;
        
        if( isset( $postmeta ) ) :
            foreach( $postmeta as $meta_key => &$meta_value ) :
                if( method_exists( $this, 'filter_meta_' . $meta_key ) ) :
                    $meta_value = call_user_func( array( $this, 'filter_meta_' . $meta_key ), $meta_value );       
                endif;
            endforeach;
        endif;
                
        $this->Data = compact( 'postdata', 'postmeta' );
    }
    
    /**
     * Vérification de valeur des données d'entrée
     */
    final public function checkDataValue()
    {
        extract( $this->Data );

        if( isset( $postdata ) ) :
            foreach( $postdata as $data_key => $data_value ) :
                if( method_exists( $this, 'check_' . $data_key ) ) :
                    call_user_func( array( $this, 'check_' . $data_key ), $data_value );       
                endif;
            endforeach;
        endif;
        
        if( isset( $postmeta ) ) :
            foreach( $postmeta as $meta_key => $meta_value ) :
                if( method_exists( $this, 'check_meta_' . $meta_key ) ) :
                    call_user_func( array( $this, 'check_meta_' . $meta_key ), $meta_value );       
                endif;
            endforeach;
        endif;
    }
    
    /** 
     * Récupération des erreurs de traitement
     */
    final public function getErrors()
    {
        return $this->Errors;
    }
    
    /** 
     * Ajout d'une erreur de traitement
     */
    final public function addError( $code = null, $message = '' )
    {
        if( ! $code && ! $message )
            return;
        if( $code ) :
            $this->Errors[$code] = $message;
        else :
            $this->Errors[] = $message;
        endif;
    }
    
    /** 
     * Récupération des données traitées
     */
    final public function getData()
    {
        return $this->Data;
    }    
    
    /** 
     * Définition des données d'entrée
     */
    public function setInputData()
    {
        return array();    
    }    
    
    /**
     * Définition de la cartographie des données principales
     */
    public function setDataMap()
    {
        return array();
    }
    
    /**
     * Définition de la cartographie des metadonnées
     */
    public function setMetadataMap()
    {
        return array();
    }
    
    /**
     * Définition de la cartographie des options
     */
    public function setOptionMap()
    {
        return array();
    }
    
    /**
     * Définition des valeurs par defaut des données principales
     */
    public function set_default( $data_key )
    {
        return '';
    }
    
    /**
     * Définition des valeurs par defaut des metadonnées
     */
    public function set_meta_default( $meta_key )
    {
        return '';
    }
}