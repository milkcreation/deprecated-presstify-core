<?php
namespace tiFy\Core\Db;

class Db extends \tiFy\App\Core
{
    /**
     * Liste des tables de bases de données déclarées
     * @var \tiFy\Core\Db\Factory[]
     */
    private static $Factories = [];

    /**
     * Classe de rappel
     * @var unknown
     */
    public static $Query = null;

    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();
                
        foreach( (array) self::tFyAppConfig() as $id => $args ) :
            self::register($id, $args);
        endforeach;

        // Définition des éléments de déclenchement
        $this->tFyAppActionAdd('init', null, 9);
    }
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    final public function init()
    {
        do_action('tify_db_register');
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
     *
     * @param string $id Identifiant unique de qualification de la table de base de données
     * @param array $attrs Attributs de configuration de la base de données
     *
     * @return \tiFy\Core\Db\Factory
     */
    public static function register($id, $attrs = [])
    {
        if (isset($attrs['cb'])) :
            self::$Factories[$id] = new $attrs['cb']($id, $attrs);
        else :
            self::$Factories[$id] = new Factory($id, $attrs);
        endif;

        if (self::$Factories[$id] instanceof Factory) :
            return self::$Factories[$id];
        endif;
    }

    /**
     * Vérification d'existance
     */
    public static function has($id)
    {
        return isset(self::$Factories[$id]);
    }

    /**
     * Récupération
     *
     * @return null|\tiFy\Core\Db\Factory
     */
    public static function get($id)
    {
        if (isset(self::$Factories[$id])) :
            return self::$Factories[$id];
        endif;
    }
}