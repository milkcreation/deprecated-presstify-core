<?php
namespace tiFy\Lib\Importer;

class Taxonomy extends \tiFy\Lib\Importer\Importer
{        
    /**
     * Taxonomie à traiter
     * @var string
     */
    protected $Taxonomy            = '';
    
    /**
     * Liste exhaustive des données autorisées
     * @var array
     */
    protected $AllowedDataMap   = [
        'term_id',
        'name',
        'slug',
        'term_group',
        'term_taxonomy_id',
        'taxonomy',
        'description',
        'parent',
        'count'
    ];
    
    /**
     * Type de données prises en charge
     */
    protected $Types        = [
        'data',
        'meta'
    ];
    
    /**
     * Traitement des attributs d'import
     */
    public function parseAttrs($attrs = [])
    {
        if (isset($attrs['taxonomy'])) :
            $this->Taxonomy = $attrs['taxonomy'];
        elseif ($taxonomy = $this->setTaxonomy()) :
            $this->Taxonomy = $taxonomy;
        endif;

        if (empty($this->Taxonomy)) :
            $this->Notices->addError('tiFyImporterTaxonomy_Empty',  __('Taxonomie de traitement manquante', 'tify'));
        endif;
    }
    
    /**
     * Définition de la taxonomy de traitement
     */
    public function setTaxonomy()
    {
        return;
    }
    
    /**
     * Insertion des données principales
     */
    public function insert_datas($datas, $term_id)
    {
        if (empty($datas['term_id'])) :
            $term = \wp_insert_term($datas['name'], $this->Taxonomy, $datas);
        else :
            $term = \wp_update_term($datas['term_id'], $this->Taxonomy, $datas);
        endif;
        
        if(\is_wp_error($term)) :
            $this->Notices->addError($term->get_error_message(), $term->get_error_code(), $term->get_error_data());
            $this->setSuccess(false);
        else :
            $term_id = $term['term_id'];
            $this->Notices->addSuccess(__('La catégorie a été importé avec succès', 'tify'), 'tFyLibImportInsertDatasSuccess');
            $this->setInsertId($term_id);
            $this->setSuccess(true);
        endif;
    }
    
    /**
     * Insertion d'une métadonnée
     */
    public function insert_meta($meta_key, $meta_value, $term_id)
    {
        return \update_term_meta($term_id, $meta_key, $meta_value);
    }   
}