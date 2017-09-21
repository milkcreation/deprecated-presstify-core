<?php
namespace tiFy\Lib\Importer;

class Post extends \tiFy\Lib\Importer\Importer
{
    /**
     * Types de données pris en charge
     * @var array {
     *      data|meta|tax|opt
     *
     *      @var string $data Données principales
     *      @var string $met Métadonnées
     *      @var string $tax Taxonomies
     *      @var string $opt Options
     * }
     */
    protected $Types     = [
        'data',
        'meta',
        'tax'
    ];

    /**
     * Cartographie des données principales permises. Permet de limiter le mapping des données principal au colonne de la table en base de données par exemple.
     * @var array
     */
    protected $AllowedDataMap  = [
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
    ];

    /**
     * Insertion des données principales
     */
    public function insert_datas($postarr, $insert_id)
    {
        if(!empty($postarr['ID'])) :
            $post_id = \wp_update_post($postarr, true);
        else :
            $post_id = \wp_insert_post($postarr, true);
        endif;

        if(\is_wp_error($post_id)) :
            $this->Notices->addError($post_id->get_error_message(), $post_id->get_error_code(), $post_id->get_error_data());
            $this->setSuccess(false);
            $post_id = 0;
        else :
            $this->Notices->addSuccess(__('Le contenu a été importé avec succès', 'tify'), 'tFyLibImportInsertDatasSuccess');
            $this->setInsertId($post_id);
            $this->setSuccess(true);
        endif;
        
        return $post_id;
    }
    
    /**
     * Insertion d'une métadonnée
     */
    public function insert_meta($meta_key, $meta_value, $post_id)
    {
        return \update_post_meta($post_id, $meta_key, $meta_value);
    }
    
    /**
     * Insertion des termes d'une taxonomie
     */
    public function insert_tax($taxonomy, $terms, $post_id)
    {
        return \wp_set_post_terms($post_id, $terms, $taxonomy, false);
    }
}