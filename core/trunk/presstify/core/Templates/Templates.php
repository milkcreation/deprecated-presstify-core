<?php
namespace tiFy\Core\Templates;

final class Templates extends \tiFy\Environment\Core
{
    /**
     * Liste des actions à déclencher
     */
    protected $tFyAppActions              = array(
        'init'    
    );
    
    /**
     * Ordres de priorité d'exécution des actions
     */
    protected $tFyAppActionsPriority   = array(
        'init'                              => 9
    );
    
    /**
     * Classe de rappel des templates déclarés
     */
    private static $Factory             = array();
    
    /**
     * Classe de rappel courante
     */
    public static $Current              = null;
    
    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();
        
        // Instanciation des contrôleurs
        new Admin\Admin;
        new Front\Front;
    }    
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    public function init()
    {
        do_action('tify_templates_register');
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration d'un gabarit
     * 
     * @param string $id identifiant unique
     * @param array $attrs attributs de configuration
        array(
            // Identifiant de base de données - posts par défaut
            // @see PresstiFy/Core/Db
            'db'                => '',
            
            // Identifiant des intitulés
            // @see PresstiFy/Core/Labels
            'label'             => '', 
            
            // Menu d'administration (contexte admin uniquement)            
            'admin_menu'        => array(       
                // Identifiant du menu - Identifiant du template par défaut
                'menu_slug'         => '',
                // Identifiant du menu parent (sous-menu uniquement) 
                'parent_slug'       => null,
                // Titre de la page    
                'page_title'        => '',
                // Intitulé du menu - Intitulé du modèle prédéfini si vide 
                'menu_title'        => '', 
                // Habiltation d'affichage du menu
                'capability'        => 'manage_options', 
                // Icone de menu (hors sous-menu : 'parent_slug' => null )
                'icon_url'          => null, 
                // Ordre d'affichage de l'entrée de menu
                'position'          => 99,
                // Fonction d'affichage de la page - Factory::render() par défaut 
                'function'          => null
            ),
            
            // Attributs spécifiques aux modèles hérités 
            // @see PresstiFy/Core/Templates/Traits/[MODEL]/Params pour la liste complète   
            /// Form
            //// Identifiant du template d'affichage de la liste des éléments
            'list_template'     => ''
             
            /// Table
            //// Identifiant du template d'édition d'un élément
            'list_template'     => '',            
        );
     * 
     * @param string $context 'admin' | 'front'
     * 
     * @return object $Factory
     */
    public static function register( $id, $attrs = array(), $context )
    {
        switch( strtolower( $context ) ) :
            case 'admin' :
                if( ! isset( self::$Factory['admin'][$id] ) )
                    return self::$Factory['admin'][$id] = new \tiFy\Core\Templates\Admin\Factory( $id, $attrs );
                break;
            case 'front' :
                if( ! isset( self::$Factory['front'][$id] ) )
                    return self::$Factory['front'][$id] = new \tiFy\Core\Templates\Front\Factory( $id, $attrs );
                break;
        endswitch;
    }
     
    /**
     * Liste des templates de l'interface d'administration
     */
    public static function listAdmin()
    {
        if( isset( self::$Factory['admin'] ) )
            return self::$Factory['admin'];
    }
    /**
     * Liste des template de l'interface utilisateur
     */
    public static function listFront()
    {
        if( isset( self::$Factory['front'] ) )
            return self::$Factory['front'];
    }
    
    /**
     * Récupération d'un template de l'interface d'administation
     * @param string $id
     * @return mixed
     */
    public static function getAdmin( $id )
    {
        if( isset( self::$Factory['admin'][$id] ) )
            return self::$Factory['admin'][$id];
    }
    
    /**
     * Récupération d'un template de l'interface utilisateur
     * @param string $id
     * @return mixed
     */
    public static function getFront( $id )
    {
        if( isset( self::$Factory['front'][$id] ) )
            return self::$Factory['front'][$id];
    }
}