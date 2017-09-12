<?php
namespace tiFy\Inherits\Importer;

use tiFy\Core\Db\Db;

class tiFyDb extends \tiFy\Inherits\Importer\Importer
{            
    /**
     * Base de données
     */
    protected $Db           = null;
    
    /**
     * Type de données prises en charge
     */
    protected $DataType     = ['data', 'metadata'];
    
    /**
     * CONSTRUCTEUR
     */
    public function __construct( $inputdata = array(), $attrs = array() )
    {
        $this->initDb( $attrs );
        parent::__construct( $inputdata, $attrs );
    }
    
    /**
     * Initialisation de la base de données
     * @param array $attrs
     */
    final public function initDb( $attrs = array() )
    {
        $id = ! empty( $attrs['db'] ) ? $attrs['db'] : $this->setDb();  
        if( ! $id ) :
            return $this->addError( 'tiFyInheritsImport_AnyDb', __( 'Aucune base de donnée d\'import n\'a été définie', 'tify' ) );
        elseif( ! $this->Db = Db::get( $id ) ) :
            return $this->addError( 'tiFyInheritsImport_InvalidDb', __( 'La base de donnée de données fournie semble invalide', 'tify' ) );       
        endif;        
    }    
    
    /**
     * Définition de la base de données
     */
    public function setDb()
    {
        return null;
    }    
    
    /**
     * Insertion des données principales
     */
    final public function insert_datas( $dbarr )
    {                
        return $this->Db->handle()->record( $dbarr );
    }
    
    /**
     * Insertion d'une métadonnée
     */
    final public function insert_meta( $db_id, $meta_key, $meta_value )
    {
        return $this->Db->meta()->update( $db_id, $meta_key, $meta_value );     
    }
}