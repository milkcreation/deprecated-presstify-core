<?php
namespace tiFy\Core\Db;

class Db extends \tiFy\Environment\Core
{
    /**
     * Liste des actions à déclencher
     * @var array
     */
    protected $CallActions                = array(
        'init'
    );
    
    /**
     * Ordre de priorité d'exécution des actions
     * @var array
     */
    protected $CallActionsPriorityMap    = array(
        'init'                => 9
    );
    
    /**
     * Liste des bases déclarées
     * @var \tiFy\Core\Db\Query[] An array of tiFyCoreDbQuery objects.
     */
    private static $Factories    = array();
    
    /**
     * Classe de rappel
     * @var unknown
     */
    public static $Query         = null;
    
    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();
                
        foreach( (array) self::getConfig() as $id => $args ) :
            self::register( $id, $args );
        endforeach;
    }
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    final public function init()
    {
        do_action( 'tify_db_register' );
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
     */
    public static function register( $id, $args = array() )
    {
        if( isset( $args['cb'] ) ) :
            self::$Factories[$id] = new $args['cb']( $id, $args );
        else :
            self::$Factories[$id] = new Factory( $id, $args );
        endif;
        
        if( self::$Factories[$id] instanceof Factory )
            return self::$Factories[$id];
    }
    
    /**
     * Vérification d'existance
     */
    public static function has( $id )
    {
        return isset( self::$Factories[$id] );
    }
    
    /**
     * Récupération
     */
    public static function get( $id )
    {
        if( isset( self::$Factories[$id] ) )
            return self::$Factories[$id];
    }
}