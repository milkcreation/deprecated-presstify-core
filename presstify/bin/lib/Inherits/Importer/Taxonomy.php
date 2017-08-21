<?php
namespace tiFy\Inherits\Importer;

class Taxonomy extends \tiFy\Inherits\Importer\Importer
{        
    /**
     * Taxonomie à traiter
     */
    public $Taxonomy        = null;
    
    /**
     * Liste exhaustive des données autorisées
     * @var array
     */
    protected $AllowedDataMap  = array(
        'term_id',
        'name',
        'slug',
        'term_group',
        'term_taxonomy_id',
        'taxonomy',
        'description',
        'parent',
        'count'
    );
    
    /**
     * Type de données prises en charge
     */
    protected $DataType     = array( 'data', 'metadata' );     
    
    /**
     * Traitement des attributs d'import
     */
    public function parseAttrs( $attrs = array() )
    {
        if( isset( $attrs['taxonomy'] ) ) :
            $this->Taxonomy = $attrs['taxonomy'];
        elseif( $taxonomy = $this->setTaxonomy() ) :
            $this->Taxonomy = $taxonomy;
        endif;

        if( empty( $this->Taxonomy ) ) 
            $this->addError( 'tiFyImporterTaxonomy_Empty',  __( 'Taxonomie de traitement manquante', 'tify' ) );        
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
    final public function insert_datas( $datas )
    {
        if( empty( $datas['term_id'] ) ) :
            $res = wp_insert_term( $datas['name'], $this->Taxonomy, $datas );
        else :
            $res = wp_update_term( $datas['term_id'], $this->Taxonomy, $datas );
        endif;
        
        if( ! is_wp_error( $res ) )
            $res = $res['term_id'];
        
        return $res;
    }
    
    /**
     * Insertion d'une métadonnée
     */
    final public function insert_meta( $term_id, $meta_key, $meta_value )
    {
        return update_term_meta( $term_id, $meta_key, $meta_value );       
    }   
}