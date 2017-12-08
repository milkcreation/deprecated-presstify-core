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
     * Liste des fonctions d'aide à la saisie avec incrémentation automatique
     * @var array
     */
    private static $InstanceHelpers = [];

    /**
     * Liste des methodes de la classe pouvant être appelées depuis la méthode tiFy\Core\Control\Control::call()
     * @var string
     */
    private static $CallableMethods = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Instanciation de la liste des fonctions d'aide à la saisie
        self::$InstanceHelpers[get_called_class()] = [];

        // Instanciation de la liste des méthodes pouvant être appelées depuis la méthode tiFy\Core\Control\Control::call()
        self::$CallableMethods[get_called_class()] = [];

        // Déclaration de la fonction d'aide à la saisie d'affichage principal du controleur
        $tag = 'tify_control_'. $this->ID;
        $method = 'display';
        self::$InstanceHelpers[get_called_class()][$tag] = $method;
        self::tFyAppAddHelper($tag, $method);

        // Déclaration de la fonction d'aide à la saisie de mise en file des scripts
        self::tFyAppAddHelper('tify_control_'. $this->ID .'_enqueue_scripts', 'enqueue_scripts');
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
     * @return null|static
     */
    final public static function __callStatic($name, $arguments)
    {
        if (in_array($name, self::$InstanceHelpers[get_called_class()])) :
            // Incrémentation du nombre d'instance
            static::$Instance++;
        elseif (!in_array($name, self::$CallableMethods[get_called_class()])) :
            return trigger_error(sprintf(__('La méthode %s du controleur d\'affichage ne peut être appelée de cette manière.', 'tify'), $name));
        endif;

        // Appel de la méthode statique du contrôleur
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

    /**
     * Ajout d'une fonction d'aide à la saisie avec incrémentation d'instance automatique
     *
     * @param string $tag Nom de qualification de la fonction
     * @param string $method Nom de qualification de la méthode du controleur
     *
     * @return void
     */
    final protected function addInstanceHelper($tag, $method)
    {
        if (isset(self::$InstanceHelpers[get_called_class()][$tag])) :
            return;
        endif;

        self::$CallableMethods[get_called_class()][] = $method;

        self::$InstanceHelpers[get_called_class()][$tag] = $method;
        self::tFyAppAddHelper($tag, $method);
    }
}