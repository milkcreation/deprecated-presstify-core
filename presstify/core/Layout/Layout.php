<?php

namespace tiFy\Core\Layout;

use tiFy\App\Core;

/**
 * @method \tiFy\Components\Layouts\Breadcrumb\Breadcrumb Breadcrumb(array $attrs = [])
 * @method \tiFy\Components\Layouts\MetaTitle\MetaTitle MetaTitle(array $attrs = [])
 * @method \tiFy\Components\Layouts\Notice\Notice Notice(array $attrs = [])
 * @method \tiFy\Components\Layouts\Tag\Tag Tag(array $attrs = [])
 */
final class Layout extends Core
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des controleurs d'affichage natifs
        foreach(glob($this->appAbsDir() . '/components/Layouts/*/', GLOB_ONLYDIR) as $filename) :
            $name = basename($filename);

            self::register($name, "tiFy\\Components\\Layouts\\{$name}\\{$name}::make");
        endforeach;

        // Déclaration des événements
        $this->appAddAction('init');
    }

    /**
     * Initialisation globale
     *
     * @return void
     */
    public function init()
    {
        // Déclaration des controleurs d'affichage personnalisés
        do_action('tify_layout_register');
    }

    /**
     * Déclaration d'un controleur d'affichage
     *
     * @param string $name Nom de qualification du controleur d'affichage
     * @param mixed $callable classe ou méthode ou fonction de rappel
     *
     * @return null|callable|\tiFy\Core\Layout\AbstractFactory
     */
    public static function register($name, $callable)
    {
        if (self::has($name)) :
            return null;
        elseif (is_callable($callable)) :
            self::tFyAppAddContainer($name, $callable);
        elseif (class_exists($callable)) :
            self::tFyAppAddContainer($name, $callable);
        else :
            return null;
        endif;

        $return = self::get($name);

        return $return;
    }

    /**
     * Vérification d'existance d'un controleur d'affichage
     *
     * @param string $name Nom de qualification du controleur d'affichage
     *
     * @return bool
     */
    public static function has($name)
    {
        return self::tFyAppHasContainer($name);
    }

    /**
     * Récupération d'un controleur d'affichage
     *
     * @param string $name Nom de qualification du controleur d'affichage
     *
     * @return mixed|\tiFy\Core\Layout\AbstractFactory
     */
    public static function get($name)
    {
        if (self::has($name)) :
            return self::tFyAppGetContainer($name);
        endif;

        return null;
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
    public static function __callStatic($name, $arguments)
    {
        if(!$callable = self::get($name)) :
            return null;
        endif;

        return call_user_func_array($callable, $arguments);
    }

    /**
     * Mise en file des scripts d'un controleur
     *
     * @param string $name Identifiant de qualification du controleur d'affichage
     * @param array $args Liste des attributs de configuration
     *
     * @return null|callable
     */
    public static function enqueue($name, $args = [])
    {
        if(!$callable = self::get($name)) :
            return null;
        endif;

        if (!is_object($callable) || !method_exists($callable, 'enqueue_scripts')) :
            return null;
        endif;

        return $callable->enqueue_scripts($args);
    }
}