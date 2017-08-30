<?php
namespace tiFy\Core\Templates;

use tiFy\Core\Db\Db;
use tiFy\Core\Labels\Labels;

abstract class Factory extends \tiFy\App\Factory
{
    /**
     * Contexte d'execution
     */
    protected static $Context                       = null;
    
    /**
     * Liste des modèles prédéfinis
     */
    protected static $Models                        = array();
    
    /**
     * Cartographie des classes de modèle prédéfini
     * @var
     */
    protected static $ModelsMap                     = array();
    
    /**
     * Identifiant du template
     * @var string
     */
    protected $TemplateID                           = null;
    
    /**
     * Classe de rappel du template
     * @var object
     */
    protected $Template                             = null;
       
    /**
     * Attributs de configuration du template
     * @var object
     */
    protected $Attrs                                = array();
    
    /**
     * Modèle de base du template
     * @var string
     */
    protected $Model                                = null; 
        
    /**
     * Classe de rappel de la base de donnée
     */
    protected $Db                                   = null;

    /**
     * Classe de rappel des intitulés
     */
    protected $Label                                = null;    
    
    /**
     * CONSTRUCTEUR
     * @param string $id identifiant du template
     * @param array $attrs attributs de configuration du template
     */
    public function __construct( $id, $attrs = array() )
    {
        parent::__construct();
        
        // Définition de la cartographie des modèles prédéfinis
        if( ! empty( static::$Models ) && empty( static::$ModelsMap ) ) :
            $context = $this->getContext();
            static::$ModelsMap = array_map( 
                function( $model ) use ( $context ) {
                    return "tiFy\\Core\\Templates\\". ucfirst( $context ) ."\\Model\\{$model}\\{$model}";
                },
                static::$Models
            );
        endif;
        
        // Définition de l'identifiant
        $this->TemplateID = $id;
        
        // Initialisation des attributs
        $this->Attrs = $attrs;    
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Récupération de l'identifiant du template
     */
    final public function getID()
    {
        return $this->TemplateID;
    }
    
    /**
     * Récupération du contexte d'execution
     */
    final public function getContext()
    {        
        return static::$Context;
    }
    
    /**
     * Récupération de la classe de rappel du template
     */
    final public function getTemplate()
    {
        return $this->Template;
    }
    
    /**
     * Récupération de la liste des attributs de configuration
     */
    final public function getAttrs()
    {
        return $this->Attrs;
    }
    
    /**
     * Récupération de la valeur d'un attribut
     */
    final public function getAttr( $attr, $default = '' )
    {
        if( isset( $this->Attrs[$attr] ) )
            return $this->Attrs[$attr];
        
        return $default;
    }
    
    /**
     * Définition de la valeur d'un attribut
     */
    final public function setAttr( $attr, $value = '' )
    {
        return $this->Attrs[$attr] = $value;
    }    
    
    /**
     * Récupération du modèle de base du template
     */
    final public function getModel()
    {
        return $this->Model;    
    }
    
    /**
     * Définition du modèle de base du template
     */
    final public function setModel( $classname )
    {        
        $context = $this->getContext();        
        
        if( $this->isModel( $classname ) ) :
            $parts = explode( '\\', $classname );
            $model = end( $parts );
            return $this->Model = $model;
        elseif( $parent = get_parent_class( $classname ) ) :
            return $this->setModel( $parent );
        endif;
        
        return '';     
    }
    
    /**
     * Vérifie si un nom de classe correspond à un modèle prédéfini
     * @param string $classname
     * @return boolean
     */
    final public function isModel( $classname )
    {
        return in_array( ltrim( $classname, '\\' ), static::$ModelsMap );
    }
    
    /**
     * Récupération de la classe de rappel de la base de donnée
     */
    final public function getDb()
    {
        if( ! is_null( $this->Db ) )
            return $this->Db;
                    
        if( $this->Db = Db::Get( $this->getAttr( 'db', $this->getID() ) ) ) :        
        else :
            $this->Db = Db::Get( 'posts' );
        endif;

        return $this->Db;
    }
    
    /**
     * Récupération d'intitulé
     * 
     * @param $label clé d'indexe de l'intitulé à récupéré - Si vide, récupère l'ensemble des intitulés
     * 
     * @return mixed string valeur de l'intitlulé | array liste des intitulés
     */
    final public function getLabel( $label = '' )
    {
        if( ! is_null( $this->Label ) )
            return $this->Label->Get( $label );
        
        if( $this->Label = Labels::Get( $this->getAttr( 'labels', $this->getID() ) ) ) :    
        else :
            $this->Label = Labels::Register( $this->getID() );
        endif;

        return $this->Label->Get( $label );
    }
    
    /**
     * Rendu du template
     */
    final public function render()
    {
        $render_cb = $this->getAttr( 'render_cb' );
        if( method_exists( $this->Template, $render_cb ) )
            return call_user_func( array( $this->Template, $render_cb ) );    
    }
}