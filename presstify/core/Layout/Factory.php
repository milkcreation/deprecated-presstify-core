<?php

namespace tiFy\Core\Layout;

abstract class Factory extends \tiFy\App\FactoryConstructor
{
    /**
     * Indicateur de déclaration
     * @var bool
     */
    private $Registred = false;

    /**
     * Compteur d'instance d'affichage
     * @var int
     */
    private static $Index = 0;

    /**
     * Numéro d'instance d'affichage courant
     * @var int
     */
    private $CurrentIndex = 0;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        // Définition de l'indicateur d'instance courant
        $this->CurrentIndex = self::$Index++;

        // Définition de l'indentifiant de qualification de la classe
        $id = "tiFyCore-layout". (new \ReflectionClass($this))->getShortName() . "--" . $this->getIndex();

        // Définition des attributs de configuration
        $attrs= func_num_args() ? func_get_arg(0) : [];

        // Initialisation de la classe parente
        parent::__construct($id, $attrs);
    }

    /**
     * Initialisation globale
     *
     * @return void
     */
    public function init()
    {

    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    public function enqueue_scripts()
    {

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
     * Affichage
     *
     * @return string
     */
    protected function display()
    {
        echo '';
    }

    /**
     *
     */
    final public function __toString()
    {
        return $this->display();
    }
}