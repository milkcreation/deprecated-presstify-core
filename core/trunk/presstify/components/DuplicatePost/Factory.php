<?php
namespace tiFy\Components\DuplicatePost;

use \tiFy\Lib\File;

class Factory
{
    /* = ARGUMENTS = */
    // Contenu d'origine
    private $Source;
    
    // Contenu de sortie 
    private $Output;   
    
    /* = CONTROLEURS = */
    /** == Définition du contenu original == **/
    final public function setSource( $post, $meta_keys = array() )
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
            return new \WP_Error( 'tiFyDuplicatePost-InvalidSource', __( 'Impossible de récupérer le contenu original', 'tify' ) );
        endif;
        
        // Prétraitement des données
        $this->Source = array();
        foreach( $post as $field_name => $field_value ) :
            if( method_exists( $this, 'set_field_' . $field_name ) ) :
                $this->Source[$field_name] = call_user_func( array( $this, 'set_field_' . $field_name ), $field_value );
            else :
                $this->Source[$field_name] = call_user_func( array( $this, '_set_field_default' ), $field_value, $field_name );
            endif;
        endforeach;        
        
        // Metadonnées    
        $this->Source['_meta'] = array();
        if( ! empty( $meta_keys ) ) :
            $metadatas = get_post_meta( $this->Source['ID'] );
            if( ! empty( $meta_keys ) && is_array( $meta_keys ) ) :
                $metadatas = array_intersect_key( $metadatas, array_flip( $meta_keys ) );
            endif;            
            
            // Prétraitement des métadonnées            
            foreach( $metadatas as $meta_key => $meta_values ) :
                foreach( $meta_values as $i => $meta_value ) :
                    if( method_exists( $this, 'set_meta_' . $meta_key ) ) :
                        $this->Source['_meta'][$meta_key][$i] = call_user_func( array( $this, 'set_meta_' . $meta_key ), $meta_value );
                    else :
                        $this->Source['_meta'][$meta_key][$i] = call_user_func( array( $this, '_set_meta_default' ), $meta_value, $meta_key );
                    endif;
                endforeach;
            endforeach;
         endif; 
         
         return $this->Source;
    }
    
    /** == Récupération du contenu original == **/
    final public function getSource()
    {
        return $this->Source;
    }
    
    /** == Récupération des métadonnées du contenu original == **/
    final public function getSourceMeta()
    {
        return $this->Source['_meta'];
    }
        
    /** == Récupération du contenu de sortie == **/
    final public function getOutput( $args = array() )
    {
        $this->Output = array();
        
        // Données principales
        foreach( $this->getSource() as $field_name => $field_value ) :
            if( method_exists( $this, 'field_' . $field_name ) ) :
                $this->Output[$field_name] = call_user_func( array( $this, 'field_' . $field_name ), $field_value );
            else :
                $this->Output[$field_name] = call_user_func( array( $this, '_field_default' ), $field_value, $field_name );
            endif;
        endforeach;
        
        // Métadonnées
        $this->Output['_meta'] = array();
        if( ! empty( $args['meta'] ) ) :
            $this->Output['_meta'] = $this->getSourceMeta();           
        endif;        
                
        return $this->Output;
    }
    
    /** == Duplication == **/
    final public function duplicate( $args = array() )
    {                         
        extract( $args );
        
        $results = array();
          
        foreach( $blog as $blog_id ) :
            if( $blog_id !== get_current_blog_id() ) :
                if( ! switch_to_blog( $blog_id ) ) :
                    continue;
                endif;
            endif;
            
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
                        
                        update_post_meta( $post_id, $meta_key, $meta_value ); 
                    endforeach;
                endforeach; 
                            
                // Traitement au moment de la duplication de l'élément  
                $this->onDuplicateItem( $post_id );
                
                $results[$blog_id] = $post_id;
            endif;
                  
            restore_current_blog();            
            
            // Traitement après la duplication de l'élément
            $this->afterDuplicateItem( $post_id );
        endforeach;
       
        return $results;
    }
    
    /** == Action au moment du traitement de l'élément duplique == **/
    public function onDuplicateItem()
    {
        return;
    } 
    
    /** == Action après la duplication de l'élément == **/
    public function afterDuplicateItem()
    {
        return;
    }
    
    /* = DEFINITION DES DONNEES SOURCE = */
    /** == Définition des données par défaut de la source == **/
    final private function _set_field_default( $field_value, $field_name )
    {
        return $field_value;
    }
    
    /** == Définition des metadonnées de la source == **/
    final private function _set_meta_default( $meta_value, $meta_key )
    {
        return $meta_value;
    }
    
    /** == Définition de l'image à la une de la source == **/
    public function set_meta__thumbnail_id( $meta_value )
    {
        if( $meta_value && $post = get_post( $meta_value ) ) :
            $_meta = array(
                'url'           => wp_get_attachment_url( $meta_value ),
                'post_title'    => $post->post_title,
                'post_content'  => $post->post_content,
                'post_excerpt'  => $post->post_excerpt
            );
            return $_meta;
        else :
            return 0;
        endif;
        
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
        if( empty( $meta_value['url'] ) )
            return $meta_value;
        
        $attachment_id = File::importAttachment( 
            $meta_value['url'], 
            array(
                'post_parent'       => $post_id,
                'post_title'        => ! empty( $meta_value['post_title'] )   ? $meta_value['post_title'] : '',
                'post_content'      => ! empty( $meta_value['post_content'] ) ? $meta_value['post_content'] : '',
                'post_excerpt'      => ! empty( $meta_value['post_excerpt'] ) ? $meta_value['post_excerpt'] : ''
            )
        );
        if( ! is_wp_error( $attachment_id ) )
            return $attachment_id;
    }
}