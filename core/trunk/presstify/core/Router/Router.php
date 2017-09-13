<?php
namespace tiFy\Core\Router;

use tiFy\Core\Options\Options;

class Router extends \tiFy\App\Core
{
    /**
     * Liste des actions à déclencher
     * @var string[]
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference
     */
    protected $tFyAppActions = ['tify_options_register_node'];

    /**
     * Liste des objets route déclarés
     * @var \tiFy\Core\Router\Factory[]
     */
    private static $Registered          = [];

    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();

        foreach ((array)self::tFyAppConfig() as $id => $attrs) :
            self::register($id, $attrs);
        endforeach;

        do_action('tify_router_register');
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Déclaration de sections de boîte à onglet de l'interface d'administration des options
     */
    public function tify_options_register_node()
    {
        if (! self::$Registered)
            return;

        Options::registerNode(
            array(
                'id'        => 'tifyCoreRouter-optionsNode',
                'title'     => __('Routes', 'tify')
            )
        );

        Options::registerNode(
            array(
                'id'        => 'tifyCoreRouter-optionsNode--contentHook',
                'parent'    => 'tifyCoreRouter-optionsNode',
                'title'     => __('Pages de contenu spéciales', 'tify'),
                'cb'        => 'tiFy\Core\Router\Taboox\Admin\ContentHook',
                'helpers'   => 'tiFy\Core\Router\Taboox\Helpers\ContentHook'
            )
        );
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
     *
     * @param string $id Ientifiant unique de qualification de la route
     * @param array $attrs {
     *      Liste des attributs de configuration.
     *
     *      @param string $title Intitulé de la route
     *      @param string $desc Texte de descritpion de la route
     *      @param string object_type Type d'objet (post|taxonomy) en relation avec la route
     *      @param string object_name Nom de qualification de l'objet en relation (ex: post, page, category, tag ...)
     *      @param string option_name Clé d'index d'enregistrement en base de données
     *      @param string list_order Ordre d'affichage de la liste de selection de l'interface d'administration
     *      @param string show_option_none Intitulé de la liste de selection de l'interface d'administration lorsqu'aucune relation n'a été établie
     * }
     */
    public static function register($id, $attrs = [])
    {
        return self::$Registered[$id] = new Factory($id, $attrs);
    }

    /**
     * Récupération de la listes des objets route déclarés
     */
    public static function getList()
    {
        return self::$Registered;
    }

    /**
     * Récupération d'un objet route déclaré
     *
     * @param string $id Ientifiant unique de qualification de la route
     *
     * @return null|\tiFy\Core\Router\Factory
     */
    public static function get($id)
    {
        if (isset(self::$Registered[$id]))
            return self::$Registered[$id];
    }
}