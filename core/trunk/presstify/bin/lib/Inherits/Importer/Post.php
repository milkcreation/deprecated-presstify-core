<?php
namespace tiFy\Inherits\Importer;

class Post extends \tiFy\Inherits\Importer\Importer
{        
    /**
     * Liste exhaustive des données autorisées
     * @var array
     */
    protected $AllowedDataMap  = array(
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
        'tax_input',
        'meta_input'
    );
    
    /**
     * Type de données prises en charge
     */
    protected $DataType     = array( 'data', 'metadata', 'taxonomy' );    

    /**
     * Insertion des données principales
     */
    final public function insert_datas( $postarr )
    {        
        if( ! empty( $postarr['ID'] ) ) :
            $post_id = wp_update_post( $postarr, true );
        else :
            $post_id = wp_insert_post( $postarr, true );
        endif;
        
        return $post_id;
    }
    
    /**
     * Insertion d'une métadonnée
     */
    final public function insert_meta( $post_id, $meta_key, $meta_value )
    {
        return update_post_meta( $post_id, $meta_key, $meta_value );       
    }
    
    /**
     * Insertion des termes d'une taxonomie
     */
    final public function insert_taxonomy_terms( $post_id, $terms, $taxonomy )
    {
        return wp_set_post_terms( $post_id, $terms, $taxonomy, false );       
    }
}