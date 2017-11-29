<?php
namespace tiFy\Core\Taboox;

use tiFy\Core\Taboox\Display\Display;
use tiFy\Core\Control\Tabs\Tabs;

class Taboox extends \tiFy\App\Core
{
    /**
     * Liste des boites à onglets déclarées
     * @var \tiFy\Core\Taboox\Box[]
     */
    private static $Boxes                = [];

    /**
     * Liste des greffons déclarés
     * @var \tiFy\Core\Taboox\Node
     */
    private static $Nodes                = [];

    /**
     * Identifiant d'accroche de la page d'affichage courante
     * @var string
     */
    protected static $Hookname         = null;

    /**
     * Liste des identifiants d'accroche déclarés
     * @var array
     */
    private static $Hooknames           = [];

    /**
     * Liste des translation d'identifiants d'accroche déclarés
     * @var array
     */
    private static $HooknameMap           = [];

    /**
     * Classe de rappel d'affichage
     * @var \tiFy\Core\Taboox\Display
     */
    private static $Display             = null;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des événements de déclenchement
        $this->tFyAppActionAdd('after_setup_tify', null, 11);
        $this->tFyAppActionAdd('init', null, 25);
        $this->tFyAppActionAdd('admin_init', null, 25);
        $this->tFyAppActionAdd('current_screen');
    }
    
    /**
     * DECLENCHEURS
     */
    /**
     * A l'issue de la configuration de PresstiFy
     *
     * @return void
     */
    final public function after_setup_tify()
    {
        // Bypass
        if (!self::tFyAppConfig()) :
            return;
        endif;

        foreach (self::tFyAppConfig() as $object_type => $hooknames) :
            if (!in_array($object_type, ['post_type', 'taxonomy', 'options', 'user', /** Rétrocompatibilité : post + option */'post', 'option'])) :
                continue;
            endif;

            foreach ($hooknames as $hookname => $args) :
                $object_name = $hookname;

                if ($object_type === 'taxonomy') :
                    $hookname = 'edit-' . $hookname;
                elseif (in_array($object_type, ['options', 'option'])) :
                    switch($hookname) :
                        default :
                            $hookname = 'settings_page_' . $hookname;
                            break;
                        case 'general' :
                        case 'writing' :
                        case 'reading' :
                        case 'media' :
                        case 'permalink' :
                            $hookname = 'options-' . $hookname;
                            break;
                    endswitch;
                elseif ($object_type === 'user') :
                    switch($hookname) :
                        default :
                        case 'edit' :
                            $hookname = 'user-edit';
                            break;
                        case 'profile' :
                            $hookname = 'profile';
                            break;
                    endswitch;
                endif;

                if (!empty($args['box'])) :
                    $args['box']['object_type'] = $object_type;
                    $args['box']['object_name'] = $object_name;
                    self::registerBox($hookname, $args['box']);
                endif;

                if (!empty($args['nodes'])):
                    foreach ((array)$args['nodes'] as $id => $attrs) :
                        if (!isset($attrs['id'])) :
                            $attrs['id'] = $id;
                        endif;
                        $attrs['object_type'] = $object_type;
                        $attrs['object_name'] = $object_name;

                        self::registerNode($hookname, $attrs);
                    endforeach;
                endif;
            endforeach;
        endforeach;
    }

    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        // Déclaration des boîtes à onglets
        do_action('tify_taboox_register_box');

        // Déclaration des sections de boîtes à onglets
        do_action('tify_taboox_register_node');

        // Déclaration des helpers
        do_action('tify_taboox_register_helpers');

        // Déclenchement de l'événement d'initialisation globale des greffons.
        if($nodes = self::getNodeList()) :
            foreach ($nodes as $hookname => $node_ids) :
                foreach ($node_ids as $node_id => $node) :
                    $node->init();
                endforeach;
            endforeach;
        endif;
    }

    /**
     * Initialisation de l'interface d'administration
     */
    final public function admin_init()
    {
        // Déclaration des translations des pages d'accroche
        if ($hooknames = self::$Hooknames) :
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            foreach ($hooknames as $hookname) :
                if(!preg_match('/::/', $hookname)) :
                    continue;
                endif;

                @list($menu_slug, $parent_slug) = preg_split('/::/', $hookname, 2);
                
                $map = \get_plugin_page_hookname($menu_slug, $parent_slug);
                self::$HooknameMap[$map] = $hookname;
            endforeach;
        endif;

        // Déclenchement de l'événement d'initialisation de l'interface d'administration des greffons.
        if($nodes = self::getNodeList()) :
            foreach ($nodes as $hookname => $node_ids) :
                foreach ($node_ids as $node_id => $node) :
                    $node->admin_init();
                endforeach;
            endforeach;
        endif;
    }

    /**
     * Chargement de l'écran courant
     *
     * @param \Wp_Screen $current_screen
     *
     * @return void
     */
    final public function current_screen($current_screen)
    {
        // Bypass
        if (!self::isHookname($current_screen->id)) :
            return;
        endif;

        self::$Hookname = isset(self::$HooknameMap[$current_screen->id]) ? self::$HooknameMap[$current_screen->id] : $current_screen->id;

        if (!($box = self::getBox(self::$Hookname)) || !($nodes = self::getNodeList(self::$Hookname))) :
            return;
        endif;

        // Définition des attributs de configuration de la classe d'affichage
        $attrs = [
            'screen'       => $current_screen,
            'hookname'     => self::$Hookname,
            'box'          => $box,
            'nodes'        => $nodes
        ];

        // Initialisation de la classe de l'écran courant
        self::$Display = new Display($attrs);

        // Déclenchement de l'événement de chargement de l'écran courant des greffons.
        if($nodes = self::getNodeList(self::$Hookname)) :
            foreach ($nodes as $node) :
                $node->current_screen($current_screen);
            endforeach;
        endif;

        // Déclaration de l'événement de mise en file des scripts de l'interface d'administration
        $this->tFyAppActionAdd('admin_enqueue_scripts');
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    final public function admin_enqueue_scripts()
    {
        // Déclenchement de l'événement de mise en file des scripts de l'interface d'administration des greffons.
        if($nodes = self::getNodeList(self::$Hookname)) :
            foreach ($nodes as $node) :
                $node->admin_enqueue_scripts();
            endforeach;
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration de boîte à onglets
     *
     * @param string $hookname Identifiant d'accroche de la page d'affichage
     * @param string $attrs {
     *      Attributs de configuration de la boîte à onglets.
     * }
     *
     * @return \tiFy\Core\Taboox\Box
     */
    final public static function registerBox($hookname, $attrs = [])
    {
        // Rétro-compatibilité
        if (func_num_args() === 3) :
            $object_type = func_get_arg(1);
            $attrs = func_get_arg(2);
            $attrs['object_type'] =  $object_type;
        elseif(is_string($attrs)) :
            $_attrs = [];
            $_attrs['object_type'] = $attrs;
            $attrs = $_attrs;
        endif;

        if (!isset($attrs['object_type']) || !in_array($attrs['object_type'], ['post_type', 'taxonomy', 'options', 'user', /** Rétrocompatibilité : post + option */'post', 'option'])) :
            $attrs['object_type'] = 'post_type';
        // Rétrocompatibilité : post + option
        elseif ($attrs['object_type'] === 'post') :
            $attrs['object_type'] = 'post_type';
        elseif ($attrs['object_type'] === 'option') :
            $attrs['object_type'] = 'options';
        endif;

        self::$Boxes[$hookname] = new Box($hookname, $attrs);
    }
    
    /**
     * Déclaration de section de boîte à onglets
     * 
     * @param string $hookname Identifiant d'accroche de la boîte à onglet
     * @param array $attrs {
     *      Attributs de configuration du greffon
     *
     *      @var string $id Identifiant du greffon.
     *      @var string $title Titre du greffon.
     *      @var string $cb Fonction ou méthode ou classe de rappel d'affichage du greffon.
     *      @var mixed $args Liste des arguments passé à la fonction, la méthode ou la classe de rappel.
     *      @var string $parent Identifiant du greffon parent.
     *      @var string $cap Habilitation d'accès au greffon.
     *      @var bool $show Affichage/Masquage du greffon.
     *      @var int $position Ordre d'affichage du greffon.
     *      @var string $object_type post_type|taxonomy|user|options
     *      @var string $object_name (post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile)
     *      @var string|string[] $helpers Liste des classes de rappel des méthodes d'aide à la saisie. Chaine de caractères séparés par de virgules|Tableau indexé.
     * }
     * 
     * @return \tiFy\Core\Taboox\Node
     */
    final public static function registerNode($hookname, $attrs = [])
    {
        if (isset($attrs['object_type']) &&  ($attrs['object_type'] === 'post')) :
            $attrs['object_type'] = 'post_type';
        endif;

        return self::$Nodes[$hookname][] = new Node($hookname, $attrs);
    }

    /**
     * Récupération de la liste des boites à onglets déclarées
     *
     * @return \tiFy\Core\Taboox\Box[]
     */
    final public static function getBoxList()
    {
        return self::$Boxes;
    }

    /**
     * Récupération d'une boite à onglets déclarée selon son identifiant d'accroche
     *
     * @param string $hookname Identifiant d'accroche de la page d'affichage
     *
     * @return \tiFy\Core\Taboox\Box[]
     */
    final public static function getBox($hookname)
    {
        if (isset(self::$Boxes[$hookname])) :
            return self::$Boxes[$hookname];
        endif;
    }

    /**
     * Récupération de la liste des greffons; complète ou selon un identifiant d'accroche
     *
     * @param null|string $hookname
     *
     * @return \tiFy\Core\Taboox\Nodes[]
     */
    final public static function getNodeList($hookname = null)
    {
        if (!$hookname) :
            return self::$Nodes;
        elseif (isset(self::$Nodes[$hookname])) :
            return self::$Nodes[$hookname];
        endif;
    }

    /**
     * Vérification d'existance d'un identifiant d'accroche dans la liste des identifiants déclarés
     *
     * @param string $hookname
     *
     * @return bool
     */
    final public static function isHookname($hookname)
    {
        if (in_array($hookname, self::$Hooknames)) :
            return true;
        elseif(isset(self::$HooknameMap[$hookname])) :
            return true;
        endif;

        return false;
    }

    /**
     * Définition d'un identifiant d'accroche
     *
     * @param string $hookname
     *
     * @return void
     */
    final public static function setHookname($hookname)
    {
        if (!self::isHookname($hookname)) :
            array_push(self::$Hooknames, $hookname);
        endif;
    }

    /**
     * Récupération de la classe de rappel d'affichage
     *
     * @return \tiFy\Core\Taboox\Display\Display
     */
    final public static function display()
    {
        return self::$Display;
    }
}