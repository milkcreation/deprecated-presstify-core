<?php
namespace tiFy\Core\Options;

use tiFy\Core\Taboox\Taboox;

class Options extends \tiFy\App\Core
{
    /**
     * Liste des attributs de configuration
     * @var array
     */
    protected static $Attrs = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Traitement des attributs de configuration
        $this->parseAttrs(self::tFyAppConfig());

        // Déclaration des événements de déclenchement
        $this->tFyAppActionAdd('init', null, 24);
        $this->tFyAppActionAdd('tify_taboox_register_box');
        $this->tFyAppActionAdd('tify_taboox_register_node');
        $this->tFyAppActionAdd('admin_menu');
        $this->tFyAppActionAdd('admin_enqueue_scripts');
        $this->tFyAppActionAdd('admin_bar_menu');
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    final public function init()
    {
        do_action('tify_options_register_node');
    }

    /**
     * Déclaration du menu d'administration
     *
     * @return void
     */
    final public function admin_menu()
    {
        // Bypass - Vérification d'existance de greffons
        if (!self::getNodes()) :
            return;
        endif;

        if (!$attrs = self::getAttr('admin_page')) :
            return;
        endif;

        if ($attrs['parent_slug']) :
            \add_submenu_page(
                $attrs['parent_slug'],
                $attrs['page_title'],
                $attrs['menu_title'],
                $attrs['capability'],
                $attrs['menu_slug'],
                $attrs['function']
            );
        else :
            \add_menu_page(
                $attrs['page_title'],
                $attrs['page_title'],
                $attrs['capability'],
                $attrs['menu_slug'],
                $attrs['function'],
                $attrs['icon_url'],
                $attrs['position']
            );
        endif;
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     */
    public function admin_enqueue_scripts()
    {
        if (get_current_screen()->id !== self::getAttr('hookname')) :
            return;
        endif;

        if (self::getAttr('render') === 'metaboxes') :
            wp_enqueue_style('tiFyCoreOptionsTemplateMetaboxes', self::tFyAppUrl() . '/assets/css/metaboxes.css', [], 171030);
        endif;
    }


    /**
     * Modification de la barre d'administration
     *
     * @param \WP_Admin_Bar $wp_admin_bar
     *
     */
    final public function admin_bar_menu(&$wp_admin_bar)
    {
        // Bypass - Vérification d'existance de greffons
        if (!self::getNodes()) :
            return;
        endif;

        // Bypass - La modification n'est effective que sur l'interface utilisateurs
        if (\is_admin()) :
            return;
        endif;

        // Bypass - L'utilisateur doit être habilité
        if (!\current_user_can(self::getAttr('cap'))) :
            return;
        endif;

        if (!$attrs = self::getAttr('admin_bar')) :
            return;
        endif;

        // Déclaration du lien d'accès à l'interface
        $wp_admin_bar->add_node($attrs);
    }

    /**
     * Déclaration de la boîte à onglets
     *
     * @return void
     */
    final public function tify_taboox_register_box()
    {
        // Bypass - Vérification d'existance de greffons
        if (!self::getNodes()) :
            return;
        endif;

        if ($attrs = $this->getAttr('box')) :
            Taboox::registerBox(
                self::getHookname(),
                $attrs
            );
        endif;
    }

    /**
     * Déclaration des greffons de la boite à onglet
     *
     * @return void
     */
    final public function tify_taboox_register_node()
    {
        if ($nodes = self::getNodes()) :
            foreach ($nodes as $attrs) :
                Taboox::registerNode(self::getHookname(), $attrs);
            endforeach;
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Traitement des attributs de configuration
     *
     * @param array $attrs {
     *      Liste des attributs de configuration
     *
     *      @var string $hookname Identifiant de qualification de la page d'accroche d'affichage
     *      @var string $menu_slug Identifiant de qualification du menu
     *      @var string $cap Habilitation d'accès
     *      @var string $page_title Intitulé global de la page
     *      @var string $menu_title Intitulé global de l'entrée de menu
     *      @var array $admin_page Attributs de configuration de la page des options
     *      @var array $admin_bar Attributs de configuration de la barre d'administration
     *      @var array $box Attributs de configuration de la boite à onglet
     *      @var array $nodes Liste des greffons
     *      @var string $render Style d'affichage de la page (standard|metaboxes|@todo méthode personnalisée|@todo function personnalisée)
     * }
     *
     * @return array
     */
    private function parseAttrs($attrs = [])
    {
        // Pré-traitement des attributs de configuration
        $defaults = [
            'hookname'          => 'settings_page_tify_options',
            'menu_slug'         => 'tify_options',
            'cap'               => 'manage_options',
            'page_title'        => __('Réglages des options du site', 'tify'),
            'menu_title'        => __('Options du site', 'tify'),
            'admin_page'        => [],
            'admin_bar'         => [],
            'box'               => [],
            'nodes'             => [],
            'render'            => 'metaboxes'
        ];
        self::$Attrs = \wp_parse_args($attrs, $defaults);

        self::$Attrs['admin_page'] = $this->parseAdminPage(self::$Attrs['admin_page']);
        self::$Attrs['admin_bar'] = $this->parseAdminBar(self::$Attrs['admin_bar']);
        self::$Attrs['box'] = $this->parseBox(self::$Attrs['box']);
        self::$Attrs['nodes'] = $this->parseNodes(self::$Attrs['nodes']);

        return self::$Attrs;
    }

    /**
     * Traitement des attributs de configuration de la page des options
     *
     * @param $attrs Liste des attributs de configuration
     *
     * @return array
     */
    private function parseAdminPage($attrs = [])
    {
        $defaults = [
            'parent_slug'   => 'options-general.php',
            'page_title'    => self::getAttr('page_title'),
            'menu_title'    => self::getAttr('menu_title'),
            'capability'    => self::getAttr('cap'),
            'menu_slug'     => self::getAttr('menu_slug'),
            'function'      => [$this, 'render'],
            'icon_url'      => '',
            'position'      => null
        ];

        return \wp_parse_args($attrs, $defaults);
    }

    /**
     * Traitement des attributs de configuration de la barre d'administration
     *
     * @param $attrs Liste des attributs de configuration
     *
     * @return array
     */
    private function parseAdminBar($attrs = [])
    {
        $defaults = [
            'id'     => self::getAttr('menu_slug'),
            'title'  => self::getAttr('menu_title'),
            'parent' => 'site-name',
            'href'   => admin_url('/options-general.php?page=' . $this->getAttr('menu_slug')),
            'group'  => false,
            'meta'   => []
        ];

        return \wp_parse_args($attrs, $defaults);
    }

    /**
     * Traitement des attributs de boite à onglet
     *
     * @param $attrs Liste des attributs de configuration
     *
     * @return array
     */
    private function parseBox($attrs = [])
    {
        $defaults = [
            'title'       => self::getAttr('page_title'),
            'object_type' => 'option',
            'object_name' => self::getAttr('menu_slug')
        ];

        return \wp_parse_args($attrs, $defaults);
    }

    /**
     * Traitement de la liste des greffons
     *
     * @param array $nodes
     *
     * @return $nodes
     */
    private function parseNodes($nodes = [])
    {
        $_nodes = [];

        foreach ($nodes as $id => $attrs) :
            // Rétrocompatibilité
            if (is_int($id) && isset($attrs['id'])):
            else :
                $args['id'] = $id;
            endif;

            $_nodes[] = $attrs;
        endforeach;

        return $_nodes;
    }

    /**
     * Récupération de la liste de attributs de configuration
     *
     * @return array
     */
    final public static function getAttrList()
    {
        return self::$Attrs;
    }

    /**
     * Récupération d'un attribut de configuration
     *
     * @param string $name Nom de l'attribut de configuration
     * @param mixed $default Valeur de retour par défaut
     *
     * @return mixed
     */
    final public static function getAttr($name, $default = '')
    {
        if (!isset(self::$Attrs[$name])) :
            return $default;
        endif;

        return self::$Attrs[$name];
    }

    /**
     * Récupération de l'identifiant d'accroche de la page d'affichage
     *
     * @return string
     */
    final public static function getHookname()
    {
        return self::getAttr('hookname');
    }

    /**
     * Récupération de la liste des greffons de boite à onglets
     *
     * @return null|array
     */
    final public static function getNodes()
    {
        return self::getAttr('nodes');
    }

    /**
     * Déclaration d'une section de boîte à onglets
     *
     * @param array $args {
     *      Attributs de configuration de la section de boîte à onglets
     *
     *      @var string $id Requis. Identifiant de la section.
     *      @var string $title Requis. Titre de la section.
     *      @var string $parent Identifiant de la section parente
     *      @var string $cb Classe de rappel d'affichage de la section.
     *      @var mixed $args Argument passé à la classe de rappel
     *      @var string $cap Habilitation d'accès à la section
     *      @var bool $show Affichage de la section
     *      @var int $order Ordre d'affichage
     *      @var string|string[] $helpers Chaine de caractères séparés par de virgules|Tableau indexé des classes de rappel d'aides à la saisie
     * }
     *
     * @return void
     */
    final public static function registerNode($attrs)
    {
        self::$Attrs['nodes'][] = $attrs;
    }

    /**
     * Rendu de l'interface d'administration
     *
     * return string
     */
    public function render()
    {
        self::tFyAppGetTemplatePart(
            self::getAttr('render'),
            null,
            [
                'page_title'   => self::getAttr('page_title'),
                'option_group' => self::getAttr('menu_slug')
            ]
        );
    }
}