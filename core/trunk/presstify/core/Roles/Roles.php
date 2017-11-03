<?php
/**
 * @see https://codex.wordpress.org/Roles_and_Capabilities
 */
namespace tiFy\Core\Roles;

class Roles extends \tiFy\App\Core
{
    /**
     * Liste des classe de rappel d'un rôle déclaré
     * @var \tiFy\Core\Roles\Factory[]
     */
    private static $Factory = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        if (self::tFyAppConfig()) :
            foreach ((array)self::tFyAppConfig() as $id => $attrs) :
                self::register($id, $attrs);
            endforeach;
        endif;

        // Définition des événements de déclenchement
        $this->tFyAppActionAdd('init', 'init', 0);
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        do_action('tify_roles_register');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
     *
     * @param string $id Identifiant unique de qualification du role
     * @param array $attrs {
     *      Liste des attributs de configuration.
     *
     *      @param string $display_name Nom d'affichage.
     *      @param string $desc Texte de description.
     *      @param array $capabilities {
     *          Liste des habilitations Tableau indexés des habilitations permises ou tableau dimensionné
     *
     *          @var string $cap Nom de l'habilitation => @var bool $grant privilege
     *      }
     * }
     *
     * @return \tiFy\Core\Roles\Factory
     */
    public static function register($id, $attrs = [])
    {
        return self::$Factory[$id] = new Factory($id, $attrs);
    }

    /**
     * Récupération de la listes des objets route déclarés
     *
     * @return void|\tiFy\Core\Roles\Factory[]
     */
    public static function getList()
    {
        return self::$Factory;
    }

    /**
     * Récupération d'un objet route déclaré
     *
     * @param string $id Ientifiant unique de qualification de la route
     *
     * @return null|\tiFy\Core\Roles\Factory
     */
    public static function get($id)
    {
        if (isset(self::$Factory[$id])) :
            return self::$Factory[$id];
        endif;
    }
}