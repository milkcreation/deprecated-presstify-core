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
     * Liste des identifiants d'accroche déclarés
     * @var array
     */
    private static $Hooknames           = [];

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

        foreach (self::tFyAppConfig() as $object => $hooknames) :
            if (!in_array($object, ['post', 'taxonomy', 'user', 'option'])) :
                continue;
            endif;

            foreach ($hooknames as $hookname => $args) :
                $object_type = $hookname;

                if ($object === 'taxonomy') :
                    $hookname = 'edit-' . $hookname;
                endif;

                if (!empty($args['box'])) :
                    $args['box']['object'] = $object;
                    $args['box']['object_type'] = $object_type;

                    self::registerBox($hookname, $args['box']);
                endif;

                if (!empty($args['nodes'])):
                    foreach ((array)$args['nodes'] as $id => $attrs) :
                        $attrs['id'] = $id;
                        $attrs['object'] = $object;
                        $attrs['object_type'] = $object_type;

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
        /*
         foreach( (array) self::$HooknameMap as $hookname ) :
            if( !  preg_match( '/::/', $hookname ) )
                continue;
            @list( $menu_slug, $parent_slug ) = preg_split( '/::/', $hookname, 2 );
                
            $screen_id = get_plugin_page_hookname( $menu_slug, $parent_slug );
            self::$ScreenHooknameMap[$screen_id] = $hookname;
        endforeach;
        */

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

        $hookname = $current_screen->id;

        if (!($box = self::getBox($hookname)) || !($nodes = self::getNodeList($hookname))) :
            return;
        endif;

        // Définition des attributs de configuration de la classe d'affichage
        $attrs = [
            'screen'       => $current_screen,
            'hookname'     => $hookname,
            'box'          => $box,
            'nodes'        => $nodes
        ];

        // Initialisation de la classe de l'écran courant
        self::$Display = new Display($attrs);

        // Déclenchement de l'événement de chargement de l'écran courant des greffons.
        if($nodes = self::getNodeList()) :
            foreach ($nodes as $hookname => $node_ids) :
                foreach ($node_ids as $node_id => $node) :
                    $node->current_screen($current_screen);
                endforeach;
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
        if($nodes = self::getNodeList()) :
            foreach ($nodes as $hookname => $node_ids) :
                foreach ($node_ids as $node_id => $node) :
                    $node->admin_enqueue_scripts();
                endforeach;
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
            $object = func_get_arg(1);
            $attrs = func_get_arg(2);
            $attrs['object'] =  $object;
        elseif(is_string($attrs)) :
            $attrs = [];
            $attrs['object'] = $attrs;
        endif;

        if (!isset($attrs['object']) || !in_array($attrs['object'], ['post', 'taxonomy', 'option', 'user'])) :
            $attrs['object'] = 'post';
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
     *      @var string $object post_type|taxonomy|user|option
     *      @var string $object_type
     *      @var string|string[] $helpers Liste des classes de rappel des méthodes d'aide à la saisie. Chaine de caractères séparés par de virgules|Tableau indexé.
     * }
     * 
     * @return \tiFy\Core\Taboox\Node
     */
    final public static function registerNode($hookname, $attrs = [])
    {
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
        return in_array($hookname, self::$Hooknames);
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
}