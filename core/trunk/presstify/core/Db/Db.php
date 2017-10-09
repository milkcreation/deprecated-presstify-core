<?php
namespace tiFy\Core\Db;

class Db extends \tiFy\App\Core
{
    /**
     * Liste des actions à déclencher
     * @var array
     */
    protected $tFyAppActions                = array(
        'init'
    );
    
    /**
     * Ordre de priorité d'exécution des actions
     * @var array
     */
    protected $tFyAppActionsPriority    = array(
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
                
        foreach( (array) self::tFyAppConfig() as $id => $args ) :
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
     *
     * {@inheritdoc}
     * @see \tiFy\Core\Db\Factory::__construct()
     *
     * @return \tiFy\Core\Db\Factory
     */
    public static function register($id, $attrs = array())
    {
        if(isset($attrs['cb'] ) ) :
            self::$Factories[$id] = new $attrs['cb']( $id, $attrs );
        else :
            self::$Factories[$id] = new Factory($id, $attrs);
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
     *
     * @return null|\tiFy\Core\Db\Factory
     */
    public static function get( $id )
    {
        if( isset( self::$Factories[$id] ) )
            return self::$Factories[$id];
    }
}