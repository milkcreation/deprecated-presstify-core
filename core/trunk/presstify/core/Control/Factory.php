<?php

namespace tiFy\Core\Control;

abstract class Factory extends \tiFy\App\FactoryConstructor
{
    /**
     * Compteur d'instance d'affichage de la classe
     * @var int
     */
    private static $Index = 0;

    /**
     * Numéro d'instance d'affichage courante
     * @var int
     */
    private $CurrentIndex = 0;

    /**
     * Liste des fonctions d'aide à la saisie avec incrémentation automatique d'une instance d'affichage
     * @var array
     */
    private static $IncreaseHelpers = [];

    /**
     * CONSTRUCTEUR
     *
     * @param array $attrs Attributs de configuration
     *
     * @return void
     */
    public function __construct($attrs = [])
    {
        $this->CurrentIndex = self::$Index;

        if(isset($attrs['id'])) :
            $this->Id = $attrs['id'];
        else :
            $this->Id = "tiFyCoreControl-". (new \ReflectionClass($this))->getShortName() . "-" . $this->getIndex();
        endif;
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    protected function init()
    {

    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    protected function enqueue_scripts()
    {

    }

    /**
     * CONTROLEURS
     */
    /**
     * Appel des méthodes statiques et déclenchement d'événements
     *
     * @param string $name Nom de la méthode à appeler
     * @param array $arguments Liste des arguments passés dans l'appel de la méthode
     *
     * @return null|static
     */
    final public static function __callStatic($name, $arguments)
    {
        if (!isset(self::$IncreaseHelpers[get_called_class()])) :
            self::$IncreaseHelpers[get_called_class()] = [];
        endif;

        $attrs = [];
        if(in_array($name, self::$IncreaseHelpers[get_called_class()])) :
            ++self::$Index;

            if (isset($arguments[0])) :
                $attrs = $arguments[0];
            endif;
        endif;

        $instance = self::create($attrs);

        // Rétrocompatibilité
        if ($name === 'display') :
            if (!isset($arguments[0])) :
                $arguments[0] = [];
            endif;
            $echo = isset($arguments[0]['echo']) ? $arguments[0]['echo'] : (isset($arguments[1]) ? $arguments[1] : true);

            if ($echo) :
                call_user_func_array([$instance, $name], $arguments);
                return;
            else :
                ob_start();
                call_user_func_array([$instance, $name], $arguments);
                return ob_get_clean();
            endif;
        endif;

        if (method_exists($instance, $name)) :
            return call_user_func_array([$instance, $name], $arguments);
        endif;
    }

    /**
     * Instanciation de la classe
     *
     * @param array $attrs Attributs de configuration
     *
     * @return self
     */
    final public static function create($attrs = [])
    {
        return new static($attrs);
    }

    /**
     * Récupération de la valeur du compteur d'instance
     *
     * @return int
     */
    final public function getIndex()
    {
        return $this->CurrentIndex;
    }

    /**
     * Déclaration d'une fonction d'aide à la saisie
     *
     * @param string $tag Identification de l'accroche
     * @param string $method Méthode de la classe à executer
     *
     * @return void
     */
    final public function addIncreaseHelper($tag, $method)
    {
        self::$IncreaseHelpers[get_called_class()][$tag] = $method;
        self::tFyAppAddHelper($tag, $method);
    }

    /**
     * Affichage
     *
     * @param array $args Liste des attributs de configuration du champ
     *
     * @return string
     */
    protected function display($args = [])
    {
        echo '';
    }
}