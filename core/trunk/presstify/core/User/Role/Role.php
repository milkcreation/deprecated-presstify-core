<?php
/**
 * @see https://codex.wordpress.org/Roles_and_Capabilities
 */
namespace tiFy\Core\User\Role;

class Role extends \tiFy\App
{
    /**
     * Liste des classe de rappel d'un rôle déclaré
     * @var \tiFy\Core\User\Role\Factory[]
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
        $this->tFyAppAddAction('init', 'init', 0);
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
        do_action('tify_user_role_register');
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
     *      @var string $display_name Nom d'affichage.
     *      @var string $desc Texte de description.
     *      @var array $capabilities {
     *          Liste des habilitations Tableau indexés des habilitations permises ou tableau dimensionné
     *
     *          @var string $cap Nom de l'habilitation => @var bool $grant privilege
     *      }
     * }
     *
     * @return \tiFy\Core\User\Role\Factory
     */
    public static function register($id, $attrs = [])
    {
        return self::$Factory[$id] = new Factory($id, $attrs);
    }

    /**
     * Récupération de la listes des objets route déclarés
     *
     * @return void|\tiFy\Core\User\Role\Factory[]
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
     * @return null|\tiFy\Core\User\Role\Factory
     */
    public static function get($id)
    {
        if (isset(self::$Factory[$id])) :
            return self::$Factory[$id];
        endif;
    }
}