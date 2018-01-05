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
     * Liste des controleurs natifs de presstiFy
     * @var \tiFy\Core\Control\Factory[]
     */
    public static $Native = [];

    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des controleurs d'affichage natifs
        foreach(glob(self::tFyAppDirname() . '/*/', GLOB_ONLYDIR) as $filename) :
            $id = basename($filename);

            // Déclaration du controleur d'affichage natif
            if($factory = self::register("tiFy\\Core\\Control\\{$id}\\{$id}")) :
                self::$Native[$id] = $factory;
            endif;
        endforeach;

        // Déclaration des controleurs d'affichage natifs dépréciés
        foreach(glob(self::tFyAppRootDirname() . '/bin/deprecated/app/core/Control/*/', GLOB_ONLYDIR) as $filename) :
            $id = basename($filename);
            // Déclaration du controleur d'affichage natif
            if($factory = self::register("tiFy\\Core\\Control\\{$id}\\{$id}")) :
                self::$Native[$id] = $factory;
            endif;
        endforeach;

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
        // Déclaration des controleurs d'affichage personnalisés
        do_action('tify_control_register');

        // Auto-chargement de l'initialisation globale des contrôleurs d'affichage
        foreach (self::$Factory as $Name => $factory) :
            $classname = get_class($factory);

            if (is_callable([$classname, 'init'])) :
                call_user_func([$classname, 'init']);
            endif;
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
     * @return null|\tiFy\Core\Control\Factory
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
            return self::$Factory[$Instance->ID] = $Instance;
        endif;
    }

    /**
     * Affichage ou récupération du contenu d'un controleur natif
     *
     * @param string $name Identifiant de qualification du controleur d'affichage
     * @param array $args {
     *      Liste des attributs de configuration
     *
     *      @var array $attrs Attributs de configuration du champ
     *      @var bool $echo Activation de l'affichage du champ
     *
     * @return null|callable
     */
    final public static function __callStatic($name, $args)
    {
        if (in_array($name, array_keys(self::$Factory))) :
            trigger_error(sprintf(__('La possibilité d\'appeler un controleur d\'affichage en utilisant son ID est dépréciée. Pour les controleurs natifs vous devez utiliser le nom de la classe ou utilisez \tiFy\Core\Control::display(\'%s\');', 'tify'), $name));
            $factory = self::$Factory[$name];
        elseif (in_array($name, array_keys(self::$Native))) :
            $factory = self::$Native[$name];
        else :
            return trigger_error(sprintf(__('le controleur d\'affichage %1$s n\'est pas un controleur natif de presstiFy, utilisez \tiFy\Core\Control\Control::display(\'%1$s\');', 'tify'), $name));
        endif;

        $echo = isset($args[1]) ? $args[1] : true;

        $id = null;
        if (!isset($args[0])) :
            $attrs = [];
        else :
            $attrs = $args[0];
        endif;

        $classname = get_class($factory);

        return call_user_func_array([$classname, 'display'], compact('attrs', 'echo'));
    }

    /**
     * Appel d'une méthode helper de contrôleur
     *
     * @param string $ID Identifiant de qualification du controleur d'affichage
     * @param string $méthod Nom de qualification de la méthode du controleur d'affichage
     *
     * @return static
     */
    final public static function call($ID, $method)
    {
        if (!isset(self::$Factory[$ID])) :
            return trigger_error(sprintf(__('Le controleur d\'affichage %s n\'est pas disponible.', 'tify'), $ID));
        endif;

        $args = array_slice(func_get_args(), 2);

        $classname = get_class(self::$Factory[$ID]);

        if (!is_callable([$classname, $method])) :
            return trigger_error(sprintf(__('Le controleur d\'affichage %s n\'a pas de méthode %s disponible.', 'tify'), $ID, $method));
        endif;

        return call_user_func_array([$classname, $method], $args);
    }

    /**
     * Affichage d'un controleur
     *
     * @param string $id Identifiant de qualification du controleur d'affichage
     * @param array $args Liste des attributs de configuration
     *
     * @return static
     */
    final public static function display($id, $args = [])
    {
        return self::call($id, 'display', $args);
    }

    /**
     * Mise en file des scripts d'un controleur
     *
     * @param string $id Identifiant de qualification du controleur d'affichage
     * @param array $args Liste des attributs de configuration
     *
     * @return static
     */
    final public static function enqueue_scripts($id, $args = [])
    {
        return self::call($id, 'enqueue_scripts', $args);
    }
}