<?php

namespace tiFy\Core\Control;

abstract class Factory extends \tiFy\App
{
    /**
     * Instance
     * @var int
     */
    protected static $Instance = 0;

    /**
     * Identifiant de la classe
     * @var string
     */
    protected $ID = '';

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration de la fonction d'aide à la saisie
        self::tFyAppAddHelper('tify_control_'. $this->ID, 'display');
    }

    /**
     * Permission de récupération d'attributs de configuration
     *
     * @return null|string
     */
    public function __get($name)
    {
        if ($name === 'ID') :
            return $this->ID;
        endif;
    }

    /**
     * Permission de test d'existance d'attributs de configuration
     *
     * @return null|string
     */
    public function __isset($name)
    {
        if ($name === 'ID') :
            return $this->ID;
        endif;
    }

    /**
     * Appel des méthodes statiques et déclenchement d'événements
     *
     * @return static
     */
    final public static function __callStatic($name, $arguments)
    {
        if ($name === 'display') :
            // Incrémentation du nombre d'instance
            static::$Instance++;
        endif;

        return call_user_func_array("static::$name", $arguments);
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public static function init()
    {

    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    public static function enqueue_scripts()
    {

    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $attrs Liste des attributs de configuration
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    protected static function display($attrs = [], $echo = true)
    {

    }
}