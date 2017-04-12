<?php
namespace tiFy\Lib\Import;

class User extends \tiFy\Lib\Import\Import
{
    /* = ARGUMENTS = */
    // Données d'entrée
    public $InputData       = array();
    
    // Cartographie des données principales
    public $DataMap         = array();
    
    // Cartographie des metadonnées
    public $MetadataMap     = array();
    
    // Cartographie des options
    public $OptionMap       = array();
        
    // Donnée permises
    protected $AllowedData  = array(
        'ID',
        'user_pass',
        'user_login',
        'user_nicename',
        'user_url',
        'user_email',
        'display_name',
        'nickname',
        'first_name',
        'last_name',
        'description',
        'rich_editing',
        'comment_shortcuts',
        'admin_color',
        'use_ssl',
        'user_registered',
        'show_admin_bar_front',
        'role',
        'locale'
    );
    
    // Données traités
    protected $Data         = array();
    
    // Erreurs
    protected $Errors       = array();
        
    /**
     * Import d'un utilisateur
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
            
            add_filter( 'send_password_change_email', '__return_false', 99, 3 );
            add_filter( 'send_email_change_email', '__return_false', 99, 3 );
            
            $user_id = wp_insert_user( $userdata );

            if( is_wp_error( $user_id ) ) :                
            else :
                if( isset( $usermeta ) ) :
                    foreach( $usermeta as $meta_key => $meta_value ) :
                        update_user_meta( $user_id, $meta_key, $meta_value );  
                    endforeach;
                endif; 
                if( isset( $useroption ) ) :
                    foreach( $useroption as $option_key => $option_value ) :
                        update_user_option( $user_id, $option_key, $option_value );  
                    endforeach;
                endif;
            endif; 
            
            return $user_id;
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
        $this->OptionMap = isset( $attrs['option_map'] ) ? $attrs['option_map'] : $this->setOptionMap();
    }
    
    /**
     * Traitement des données d'entrée
     */
    final public function parseInputData( $inputdata = array() )
    {
        if( empty( $inputdata ) )
            $inputdata = $this->setInputData();            
            
        // Traitement des données d'entrée principales
        $userdata = array();
        
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
                    $userdata[$data_key] = $inputdata[$map_key]; 
                elseif( method_exists( $this, 'set_' . $data_key ) ) :
                    $userdata[$data_key] = call_user_func( array( $this, 'set_' . $data_key ) );
                endif;
            endforeach;
            
        /// Données non cartographiées     
        else :
            foreach( $this->AllowedData as $data_key ) :
                if( $inputdata[$data_key] ) :
                    $userdata[$data_key] = $inputdata[$data_key];
                elseif( method_exists( $this, 'set_' . $data_key ) ) :
                    $userdata[$data_key] = call_user_func( array( $this, 'set_' . $data_key ) ); 
                endif;
            endforeach;
        endif;
        
        // Traitement des metadonnées
        $usermeta = array();
        /// Données cartographiées        
        if( $this->MetadataMap ) :
            foreach( $this->MetadataMap as $meta_key => $map_key ) :
                if( is_numeric( $meta_key ) ) :
                    $meta_key = $map_key;
                endif;

                if( isset( $inputdata[$map_key] ) ) :
                    $usermeta[$meta_key] = $inputdata[$map_key];
                elseif( method_exists( $this, 'set_meta_' . $meta_key ) ) :
                    $usermeta[$meta_key] = call_user_func( array( $this, 'set_meta_' . $meta_key ) ); 
                endif;
            endforeach;
        else :
            if( $matches = preg_grep( '/^set_meta_(.*)/', get_class_methods( $this ) ) ) :
                foreach( $matches as $method ) :
                    $meta_key = preg_replace( '/^set_meta_/', '', $method );
                    $usermeta[$meta_key] = call_user_func( array( $this, 'set_meta_' . $meta_key ) );
                endforeach;
            endif;            
        endif;
        
        // Traitement des options
        $useroption = array();
        if( $this->OptionMap ) :
            foreach( $this->OptionMap as $option_key => $map_key ) :
                if( is_numeric( $option_key ) ) :
                    $option_key = $map_key;
                endif;

                if( isset( $inputdata[$map_key] ) ) :
                    $useroption[$option_key] = $inputdata[$map_key]; 
                elseif( method_exists( $this, 'set_option_' . $option_key ) ) :
                    $useroption[$option_key] = call_user_func( array( $this, 'set_option_' . $option_key ) ); 
                endif;
            endforeach;
        else :
            if( $matches = preg_grep( '/^set_option_(.*)/', get_class_methods( $this ) ) ) :
                foreach( $matches as $method ) :
                    $option_key = preg_replace( '/^set_option_/', '', $method );
                    $useroption[$option_key] = call_user_func( array( $this, 'set_option_' . $option_key ) );
                endforeach;
            endif;  
        endif;
        
        $this->Data = compact( 'userdata', 'usermeta', 'useroption' );
    }
    
    /**
     * Filtrage de valeur des données d'entrée
     */
    final public function filterDataValue()
    {
        extract( $this->Data );

        if( isset( $userdata ) ) :
            foreach( $userdata as $data_key => &$data_value ) :
                if( method_exists( $this, 'filter_' . $data_key ) ) :
                    $data_value = call_user_func( array( $this, 'filter_' . $data_key ), $data_value );       
                endif;
            endforeach;
        endif;
        
        if( isset( $usermeta ) ) :
            foreach( $usermeta as $meta_key => &$meta_value ) :
                if( method_exists( $this, 'filter_meta_' . $meta_key ) ) :
                    $meta_value = call_user_func( array( $this, 'filter_meta_' . $meta_key ), $meta_value );       
                endif;
            endforeach;
        endif;
        
        if( isset( $useroption ) ) :
            foreach( $useroption as $option_key => &$option_value ) :
                if( method_exists( $this, 'filter_option_' . $option_key ) ) :
                    $option_value = call_user_func( array( $this, 'filter_option_' . $option_key ), $option_value );       
                endif;
            endforeach;
        endif;
        
        $this->Data = compact( 'userdata', 'usermeta', 'useroption' );
    }
    
    /**
     * Vérification de valeur des données d'entrée
     */
    final public function checkDataValue()
    {
        extract( $this->Data );

        if( isset( $userdata ) ) :
            foreach( $userdata as $data_key => $data_value ) :
                if( method_exists( $this, 'check_' . $data_key ) ) :
                    call_user_func( array( $this, 'check_' . $data_key ), $data_value );       
                endif;
            endforeach;
        endif;
        
        if( isset( $usermeta ) ) :
            foreach( $usermeta as $meta_key => $meta_value ) :
                if( method_exists( $this, 'check_meta_' . $meta_key ) ) :
                    call_user_func( array( $this, 'check_meta_' . $meta_key ), $meta_value );       
                endif;
            endforeach;
        endif;
        
        if( isset( $useroption ) ) :
            foreach( $useroption as $option_key => $option_value ) :
                if( method_exists( $this, 'check_option_' . $option_key ) ) :
                    call_user_func( array( $this, 'check_option_' . $option_key ), $option_value );       
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
}