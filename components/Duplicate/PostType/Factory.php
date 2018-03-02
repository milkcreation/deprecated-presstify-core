<?php
namespace tiFy\Components\Duplicate\PostType;

use \tiFy\Lib\File;

class Factory
{
    /* = ARGUMENTS = */
    // Contenu d'origine
    private $Input;
    
    // ID du Blog d'origine
    private $InputBlogID;
    
    // Contenu de sortie 
    private $Output;
    
    // ID du Blog de sortie
    private $OutputBlogID;
    
    /* = CONSTRUCTEUR = */
    public function __construct(){}    
    
    /* = CONTROLEURS = */
    /** == Définition du contenu original == **/
    final public function setInput( $post, $meta_keys = array() )
    {
        if( is_int( $post ) ) :
            $post = get_post( $post, ARRAY_A );
        elseif( $post instanceof WP_Post ) :
            $post = $post = get_post( $post, ARRAY_A );
        else :
            $post = (array) $post;
        endif;
        
        // Bypass
        if( empty( $post['ID'] ) ) :
            return new \WP_Error( 'tiFyDuplicatePost-InvalidInput', __( 'Impossible de récupérer le contenu original', 'tify' ) );
        endif;
        
        // Prétraitement des données
        $this->Input = array();
        $this->InputBlogID = (int) get_current_blog_id();  
        
        foreach( $post as $field_name => $field_value ) :
            if( method_exists( $this, 'set_field_' . $field_name ) ) :
                $this->Input[$field_name] = call_user_func( array( $this, 'set_field_' . $field_name ), $field_value );
            else :
                $this->Input[$field_name] = call_user_func( array( $this, '_set_field_default' ), $field_value, $field_name );
            endif;
        endforeach;        
        
        // Metadonnées    
        $this->Input['_meta'] = array();
        if( ! empty( $meta_keys ) ) :
            $metadatas = get_post_meta( $this->Input['ID'] );
            if( ! empty( $meta_keys ) && is_array( $meta_keys ) ) :
                if( count( array_filter( array_keys( $meta_keys ), 'is_string' ) ) > 0 ) :
                    $meta_keys = array_keys( $meta_keys );
                endif;
                $metadatas = array_intersect_key( $metadatas, array_flip( $meta_keys ) );
            endif;
            
            // Prétraitement des métadonnées            
            foreach( $metadatas as $meta_key => $meta_values ) :
                foreach( $meta_values as $i => $meta_value ) :
                    if( method_exists( $this, 'set_meta_' . $meta_key ) ) :
                        $this->Input['_meta'][$meta_key][$i] = call_user_func( array( $this, 'set_meta_' . $meta_key ), $meta_value );
                    else :
                        $this->Input['_meta'][$meta_key][$i] = call_user_func( array( $this, '_set_meta_default' ), $meta_value, $meta_key );
                    endif;
                endforeach;
            endforeach;
         endif; 
         
         return $this->Input;
    }
    
    /** == Récupération du contenu original == **/
    final public function getInput()
    {
        return $this->Input;
    }
    
    /** == Récupération de l'ID du blog d'origine == **/
    final public function getInputBlogID()
    {
        return (int) $this->InputBlogID;
    }
    
    /** == Récupération des métadonnées du contenu original == **/
    final public function getInputMeta()
    {
        return $this->Input['_meta'];
    }    
        
    /** == Récupération du contenu de sortie == **/
    final public function getOutput( $args = array() )
    {
        $this->Output = array();
        
        // Données principales
        foreach( $this->getInput() as $field_name => $field_value ) :
            if( method_exists( $this, 'field_' . $field_name ) ) :
                $this->Output[$field_name] = call_user_func( array( $this, 'field_' . $field_name ), $field_value );
            else :
                $this->Output[$field_name] = call_user_func( array( $this, '_field_default' ), $field_value, $field_name );
            endif;
        endforeach;
        
        // Métadonnées
        $this->Output['_meta'] = array();
        if( ! empty( $args['meta'] ) ) :
            $this->Output['_meta'] = $this->getInputMeta();           
        endif;        
                
        return $this->Output;
    }
    
    /** == Récupération de l'ID du blog de sortie == **/
    final public function getOutputBlogID()
    {
        return (int) $this->OutputBlogID;
    }
    
    /** == Duplication == **/
    final public function duplicate( $args = array() )
    {                         
        extract( $args );
        
        if( is_string( $blog ) ) :
            $blog_ids = array_map( 'absint', explode( ',', $blog ) );
        elseif( is_int( $blog ) ) :
            $blog_ids = array( $blog );
        else :
            $blog_ids = array( (int) get_current_blog_id() );
        endif;
        
        $results = array();          
        foreach( $blog_ids as $blog_id ) :
            if( $blog_id !== get_current_blog_id() ) :
                if( ! switch_to_blog( $blog_id ) ) :
                    continue;
                endif;
            endif;
            
            $this->OutputBlogID = $blog_id;
            
            // Récupération des données d'enregistrement
            $datas = $this->getOutput( $args );
            
            // Enregistrement des données
            $post_id = 0;
            if( $post_id = wp_insert_post( $datas ) ) :
                // Traitement des métadonnées
                foreach( $datas['_meta'] as $meta_key => $meta_values ) :
                    foreach( $meta_values as $meta_value ) :
                        $prev_value = $meta_value;
            
                        if( method_exists( $this, 'meta_' . $meta_key ) ) :
                            $meta_value = call_user_func( array( $this, 'meta_' . $meta_key ), $meta_value, $post_id );
                        else :
                            $meta_value = call_user_func( array( $this, '_meta_default' ), $meta_value, $meta_key, $post_id );
                        endif;
                        if( isset( $meta[$meta_key] ) && $meta[$meta_key] ) :
                            add_post_meta( $post_id, $meta_key, $meta_value );
                        else :
                            update_post_meta( $post_id, $meta_key, $meta_value );
                        endif;
                    endforeach;
                endforeach; 
                
                // Traitement au moment de la duplication de l'élément  
                $this->onDuplicateItem( $post_id );
                
                $results[$blog_id] = $post_id;
            endif;
                  
            restore_current_blog();            
            
            // Traitement après la duplication de l'élément
            $this->afterDuplicateItem( $post_id );
            
            $this->OutputBlogID = 0;
        endforeach;
       
        return $results;
    }
    
    /** == Action au moment du traitement de l'élément duplique == **/
    public function onDuplicateItem( $post_id ){} 
    
    /** == Action après la duplication de l'élément == **/
    public function afterDuplicateItem( $post_id ){}
    
    /* = DEFINITION DES DONNEES ORIGINALES = */
    /** == Définition des données originales == **/
    final private function _set_field_default( $field_value, $field_name )
    {
        return $field_value;
    }
    
    /** == Définition des metadonnées originales == **/
    final private function _set_meta_default( $meta_value, $meta_key )
    {
        return $meta_value;
    }
            
    /** == Définition de l'image à la une originale == **/
    public function set_meta__thumbnail_id( $meta_value )
    {
        return self::setInputMetaMedia( (int) $meta_value );
    }    
        
    /* = DEFINITION DES DONNEES D'ENREGISTREMENT = */
    /** == Définition des données d'enregistrement par défaut == **/
    final private function _field_default( $value, $name )
    {
        return $value;
    }
    
    /** == == **/
    public function field_ID( $value )
    {
        return null;
    }
    
    /** == == **/
    public function field_post_author( $value )
    {
        return get_current_user_id();
    }
    
    /** == == **/
    public function field_post_date( $value )
    {
        return current_time( 'mysql' );
    }
    
    /** == == **/
    public function field_post_date_gmt( $value )
    {
        return current_time( 'mysql', true );
    }
    
    /** == Définition des metadonnées d'enregistrement par défaut == **/
    final private function _meta_default( $meta_value, $meta_key, $post_id )
    {
        return $meta_value;
    }
    
    /** == Définition de l'image à la une à enregistrer == **/
    public function meta__thumbnail_id( $meta_value, $post_id )
    {
        return self::setOutputMetaMedia( $meta_value, $post_id );
    }
        
    /* = HELPERS = */
    /** == Récupération des métadonnées du contenu original == **/
    final public static function setInputMetaMedia( $attachment_id = 0 )
    {
        if( $attachment_id && ( $post = get_post( $attachment_id ) ) ) :
            return array(
                'url'           => wp_get_attachment_url( $attachment_id ),
                'post_title'    => $post->post_title,
                'post_content'  => $post->post_content,
                'post_excerpt'  => $post->post_excerpt
            );
        else :
            return 0;
        endif;
    }
    
    /** == Récupération des métadonnées du contenu original == **/
    final public static function setOutputMetaMedia( $attrs = array(), $post_id )
    {
        if( empty( $attrs['url'] ) )
            return $attrs;
        
        $attachment_id = File::importAttachment( 
            $attrs['url'], 
            array(
                'post_parent'       => $post_id,
                'post_title'        => ! empty( $attrs['post_title'] )   ? $attrs['post_title'] : '',
                'post_content'      => ! empty( $attrs['post_content'] ) ? $attrs['post_content'] : '',
                'post_excerpt'      => ! empty( $attrs['post_excerpt'] ) ? $attrs['post_excerpt'] : ''
            )
        );
        if( ! is_wp_error( $attachment_id ) )
            return $attachment_id;
    }
}