<?php
namespace tiFy\Components\NavMenu;

class NavMenu extends \tiFy\App\Component
{
    /**
     * Classe de rappel d'affichage des menus déclarés
     */
    protected static $Walkers   = array();
    
    /**
     * Liste des greffons
     */
    protected static $Nodes     = array();

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des menus
        foreach ((array)self::tFyAppConfig() as $id => $attrs) :
            self::register($id, $attrs);
        endforeach;

        do_action('tify_register_nav_menu');

        // Chargement des fonctions d'aide à la saisie
        require_once self::tFyAppDirname() . '/Helpers.php';
    }
   
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration d'un menu
     *
     * @param string $id Ientifiant unique de qualification
     * @param array $attrs Attributs de configuration
     *
     * @return
     */
    final public static function register($id, $attrs)
    {
        $path = [];
        if (isset($attrs['walker'])) :
            $path[] = $attrs['walker'];
        endif;
        $path[] = "\\" . self::getOverrideNamespace() . "\\Components\\NavMenu\\" . self::sanitizeControllerName($id) . "\\Walker";
        foreach (self::getOverrideNamespaceList() as $namespace) :
            $path[] = $namespace . "\\Components\\NavMenu\\Walker";
        endforeach;
        self::$Walkers[$id] = self::loadOverride("\\tiFy\\Components\\NavMenu\\Walker", $path);

        if (isset($attrs['nodes'])) :
            foreach ($attrs['nodes'] as $node_id => $node_attrs) :
                self::addNode($id, $node_attrs);
            endforeach;
        endif;

        return self::$Walkers[$id];
    }

    /**
     * Ajout d'une entrée de menu
     * @param string $id
     * @param mixed $attrs
     *
     * @return void
     */
    final public static function addNode($id, $attrs)
    {
        // Bypass
        if (!isset(self::$Walkers[$id])) :
            return;
        endif;

        if (!isset(self::$Nodes[$id])) :
            self::$Nodes[$id] = [];
        endif;

        array_push(self::$Nodes[$id], $attrs);
    }

    /**
     * Affichage d'un menu
     *
     * @param array $attrs Attributs de configuration
     * @param string $echo Activation de l'affichage
     *
     * @return null|string
     */
    final public static function display($attrs = [], $echo = true)
    {
        $defaults = [
            'id'              => current(array_keys(self::$Walkers)),
            /**
             * @todo
             */
            'container'       => 'nav',
            'container_id'    => '',
            'container_class' => '',
            'menu_id'         => '',
            'menu_class'      => 'menu',
            'depth'           => 0
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        /**
         * @var string $id
         */
        extract($attrs);

        if (!$id) :
            return;
        endif;
        if (!isset(self::$Walkers[$id])) :
            return;
        endif;
        if (!isset(self::$Nodes[$id])) :
            return;
        endif;

        $nodes_path[] = "\\" . self::getOverrideNamespace() . "\\Components\\NavMenu\\" . self::sanitizeControllerName($id) . "\\Nodes";
        $nodes_path[] = "\\" . self::getOverrideNamespace() . "\\Components\\NavMenu\\Nodes";
        $Nodes = self::loadOverride('\tiFy\Components\NavMenu\Nodes');
        $nodes = $Nodes->customs(self::$Nodes[$id]);

        $Walker = self::$Walkers[$id];
        $output = $Walker::output($nodes);

        if ($echo) :
            echo $output;
        endif;

        return $output;
    }
}