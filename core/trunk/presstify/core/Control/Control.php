<?php
namespace tiFy\Core\Control;

class Control extends \tiFy\App\Core
{
    /**
     * Liste des classes de rappel des controleurs
     * @var \tiFy\Core\Control\Factory[]
     */ 
    public static $Factory = [];

    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des controleurs d'affichage natifs
        foreach(glob(self::tFyAppDirname() .'/*/', GLOB_ONLYDIR) as $filename) :
            $Name = basename($filename);
            $ClassName    = "tiFy\\Core\\Control\\{$Name}\\{$Name}";
         
            self::register($ClassName);
        endforeach;

        // Déclaration des controleurs d'affichage personnalisés
        do_action('tify_control_register');

        // Déclaration des événement de déclenchement
        $this->tFyAppAddAction('init');
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public function init()
    {
        // Auto-chargement de l'initialisation globale des contrôleurs d'affichage
        foreach (self::$Factory as $Name => $factory) :
            call_user_func([$factory, 'init']);
        endforeach;
    }
        
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration d'un controleur d'affichage
     *
     * @param string $classname
     *
     * @return void
     */
    final public static function register($classname)
    {
        // Bypass
        if(!class_exists($classname)) :
            return;
        endif;

        // Initialisation de la classe
        $Instance = self::loadOverride($classname);

        if(!empty($Instance->ID) && !isset(self::$Factory[$Instance->ID])) :
            self::$Factory[$Instance->ID] = $Instance;
        endif;
    }

    /**
     * Appel d'un controleur d'affichage
     *
     * @param string $name Identifiant de qualification du controleur d'affichage
     * @param array $args {
     *      Liste des attributs de configuration
     *
     *      @var array $attrs Attributs de configuration du champ
     *      @var bool $echo Activation de l'affichage du champ
     *
     * return null|callable
     */
    final public static function __callStatic($name, $args)
    {
        if (!isset(self::$Factory[$name])) :
            return;
        endif;

        $echo = isset($args[1]) ? $args[1] : true;

        $id = null;
        if (!isset($args[0])) :
            $attrs = [];
        else :
            $attrs = $args[0];
        endif;

        return call_user_func_array([self::$Factory[$name], 'display'], compact('attrs', 'echo'));
    }

    /**
     * Mise en file des scripts
     *
     * @param string $name Identifiant de qualification du controleur d'affichage
     *
     * @return void
     */
    final public static function enqueue_scripts($name)
    {
        if (!isset(self::$Factory[$name])) :
            return;
        endif;

        $args = array_slice(func_get_args(), 1);

        return call_user_func_array([self::$Factory[$name], 'enqueue_scripts'], $args);
    }
}