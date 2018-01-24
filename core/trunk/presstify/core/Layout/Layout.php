<?php

namespace tiFy\Core\Layout;

class Layout extends \tiFy\App\Core
{
    /**
     * Liste des noms de qualification des controleurs d'affichage déclarés
     * @var array
     */
    private static $Containers = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des controleurs d'affichage natifs
        foreach(glob(self::tFyAppDirname() . '/*/', GLOB_ONLYDIR) as $filename) :
            $name = basename($filename);

            self::register($name, "tiFy\\Core\\Layout\\{$name}\\{$name}");
        endforeach;

        // Déclaration des événements
        $this->appAddAction('init');
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
        do_action('tify_layout_register');

        // Initialisation de la déclaration des layout
        foreach (self::$Containers as $name) :
            if(!self::has($name)) :
                continue;
            endif;

            $callable = self::get($name);

            if (!is_object($callable)) :
                continue;
            endif;

            if (!method_exists($callable, 'init')) :
                continue;
            endif;

            $callable->init();
        endforeach;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration d'un controleur d'affichage
     *
     * @param string $name Nom de qualification du controleur d'affichage
     * @param mixed $callable classe ou méthode ou fonction de rappel
     *
     * @return null|callable|\tiFy\Core\Layout\Factory
     */
    final public static function register($name, $callable)
    {
        if (is_callable($callable)) :
            self::tFyAppAddContainer($name, $callable);
        elseif (class_exists($callable)) :
            self::tFyAppAddContainer($name, $callable);
        else :
            return null;
        endif;

        array_push(self::$Containers, $name);
    }

    /**
     * Vérification d'existance d'un controleur d'affichage
     *
     * @param string $name Nom de qualification du controleur d'affichage
     *
     * @return bool
     */
    final public static function has($name)
    {
        return self::tFyAppHasContainer($name);
    }

    /**
     * Récupération d'un controleur d'affichage
     *
     * @param string $name Nom de qualification du controleur d'affichage
     *
     * @return mixed
     */
    final public static function get($name, $attrs = [])
    {
        if (self::has($name)) :
            return self::tFyAppGetContainer($name, $attrs);
        endif;
    }

    /**
     * Affichage ou récupération du contenu d'un controleur natif
     *
     * @param string $name Nom de qualification du controleur d'affichage
     * @param array $args {
     *      Liste des attributs de configuration
     *
     *      @var array $attrs Attributs de configuration du champ
     *      @var bool $echo Activation de l'affichage du champ
     *
     * @return null|callable
     */
    final public static function __callStatic($name, $arguments)
    {
        if(!self::has($name)) :
            return null;
        endif;

        return self::get($name, $arguments);
    }

    /**
     * Affichage d'un controleur
     *
     * @param string $name Nom de qualification du controleur d'affichage
     * @param array $args Liste des attributs de configuration
     *
     * @return null|callable
     */
    final public static function display($name, $args = [])
    {
        if(!self::has($name)) :
            return null;
        endif;

        echo self::get($name, [$args]);
    }

    /**
     * Mise en file des scripts d'un controleur
     *
     * @param string $id Identifiant de qualification du controleur d'affichage
     * @param array $args Liste des attributs de configuration
     *
     * @return null|callable
     */
    final public static function enqueue_scripts($name, $args = [])
    {
        if(!self::has($name)) :
            return null;
        endif;

        $callable = self::get($name);

        if (!is_object($callable)) :
            return null;
        endif;

        if (!method_exists($callable, 'enqueue_scripts')) :
            return null;
        endif;

        return $callable->enqueue_scripts($args);
    }
}