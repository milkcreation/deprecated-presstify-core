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
        foreach(glob(self::tFyAppDirname() . '/*/', GLOB_ONLYDIR) as $filename) :
            $id = basename($filename);

            self::register($id, "tiFy\\Core\\Control\\{$id}\\{$id}");
        endforeach;

        // Déclaration des controleurs d'affichage natifs dépréciés
        foreach(glob(self::tFyAppRootDirname() . '/bin/deprecated/app/core/Control/*/', GLOB_ONLYDIR) as $filename) :
            $id = basename($filename);

            self::register($id, "tiFy\\Core\\Control\\{$id}\\{$id}");
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

        // Auto-chargement de l'initialisation globale des champs
        foreach (self::$Factory as $id => $instance) :
            if (!$classname = get_class($instance)) :
                continue;
            endif;

            // Définition des classes d'aide à la saisie
            $_id = join('_', array_map('lcfirst', preg_split('#(?=[A-Z])#', $id)));
            $instance->addDisplayHelper('tify_control' . $_id, 'display');

            if (is_callable([$classname, 'init'])) :
                call_user_func([$classname, 'init']);
            endif;
        endforeach;
    }
        
    /**
     * CONTROLEURS
     */
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
    final public static function __callStatic($id, $args)
    {
        if (!isset(static::$Factory[$id])) :
            return trigger_error(sprintf(__('Le controleur d\'affichage %s n\'est pas disponible.', 'tify'), $id));
        elseif ($classname = get_class(static::$Factory[$id])) :
            $callable = [$classname, 'display'];
        else :
            $callable = static::$Factory[$id];
        endif;
        if (!is_callable($callable)) :
            return trigger_error(sprintf(__('La méthode d\'affichage du controleur d\'affichage %s ne peut être appelée.', 'tify'), $id));
        endif;

        $echo = isset($args[1]) ? $args[1] : false;
        $attrs = reset($args);

        if ($echo) :
            call_user_func_array($callable, compact('attrs'));
        else :
            ob_start();
            call_user_func_array($callable, compact('attrs'));
            return ob_get_clean();
        endif;
    }

    /**
     * Déclaration d'un controleur d'affichage
     *
     * @param string $id Identifiant de qualification du controleur
     * @param string $callback classes ou méthodes ou fonctions de rappel
     *
     * @return null|\tiFy\Core\Control\Factory
     */
    final public static function register($id, $callback)
    {
        if (class_exists($callback)) :
            return self::$Factory[$id] = self::loadOverride($callback);
        else :
            return self::$Factory[$id] = (string)$callback;
        endif;

    }

    /**
     * Appel d'une méthode helper de contrôleur
     *
     * @param string $id Identifiant de qualification du controleur
     * @param string $method Nom de qualification de la méthode à appeler
     *
     * @return static
     */
    final public static function call($id, $method)
    {
        $id = join('', array_map('ucfirst', preg_split('#_#', $id)));

        $classname = get_class(static::$Factory[$id]);

        if (!isset(static::$Factory[$id])) :
            return trigger_error(sprintf(__('Le controleur d\'affichage %s n\'est pas disponible.', 'tify'), $id));
        elseif (!$classname && ($method !== 'display')) :
            return trigger_error(sprintf(__('Le controleur d\'affichage %s n\'a pas de méthode %s disponible.', 'tify'), $id, $method));
        elseif ($classname) :
            $callable = [$classname, $method];
        else :
            $callable = static::$Factory[$id];
        endif;

        $args = array_slice(func_get_args(), 2);

        if (!is_callable($callable)) :
            return trigger_error(sprintf(__('Le controleur d\'affichage %s n\'a pas de méthode %s disponible.', 'tify'), $id, $method));
        endif;

        return call_user_func_array($callable, $args);
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