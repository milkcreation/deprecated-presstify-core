<?php

namespace tiFy\App;

use tiFy\tiFy;
use tiFy\App;
use tiFy\Apps;
use tiFy\Environment\Traits\Old;

abstract class Factory extends App
{
    use Traits\Controllers,
        Old
        {
            Old::__construct                as private __OldConstruct;
            Old::__get                      as private __OldGet;
            Old::__isset                    as private __OldIsset;
            Old::__set                      as private __OldSet;
        }

    /**
     * Liste des actions à déclencher
     * @var string[]
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference
     * @deprecated
     */
    protected $tFyAppActions = [];

    /**
     * Cartographie des méthodes de rappel des actions
     * @var array
     * @deprecated
     */
    protected $tFyAppActionsMethods = [];

    /**
     * Ordre de priorité d'exécution des actions
     * @var mixed
     * @deprecated
     */
    protected $tFyAppActionsPriority = [];

    /**
     * Nombre d'arguments autorisés
     * @var mixed
     * @deprecated
     */
    protected $tFyAppActionsArgs = [];

    /**
     * Liste des filtres à déclencher
     * @var string[]
     * @deprecated
     */
    protected $tFyAppFilters = [];

    /**
     * Cartographie des méthodes de rappel des filtres
     * @var array
     * @deprecated
     */
    protected $tFyAppFiltersMethods = [];

    /**
     * Ordres de priorité d'exécution des filtres
     * @deprecated
     */
    protected $tFyAppFiltersPriority = [];

    /**
     * Nombre d'arguments autorisés
     * @deprecated
     */
    protected $tFyAppFiltersArgs = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->__OldConstruct();

        // Définition des actions à déclencher
        foreach ($this->tFyAppActions as $tag) :
            $priority = isset($this->tFyAppActionsPriority[$tag]) ? (int)$this->tFyAppActionsPriority[$tag] : 10;
            $accepted_args = isset($this->tFyAppActionsArgs[$tag]) ? (int)$this->tFyAppActionsArgs[$tag] : 1;

            if (!isset($this->tFyAppActionsMethods[$tag])) :
                $function_to_add = [$this, (string)$tag];
            else :
                $function_to_add = [$this, (string)$this->tFyAppActionsMethods[$tag]];
            endif;

            \add_action($tag, $function_to_add, $priority, $accepted_args);
        endforeach;

        // Définition des filtres à déclencher
        foreach ($this->tFyAppFilters as $tag) :
            $priority = isset($this->tFyAppFiltersPriority[$tag]) ? (int)$this->tFyAppFiltersPriority[$tag] : 10;
            $accepted_args = isset($this->tFyAppFiltersArgs[$tag]) ? (int)$this->tFyAppFiltersArgs[$tag] : 1;

            if (!isset($this->tFyAppFiltersMethods[$tag])) :
                $function_to_add = [$this, (string)$tag];
            else :
                $function_to_add = [$this, (string)$this->tFyAppFiltersMethods[$tag]];
            endif;

            \add_filter($tag, $function_to_add, $priority, $accepted_args);
        endforeach;

        // Dépréciation des déclaration d'actions ancienne génération
        if (!empty($this->tFyAppActions)) :
            trigger_error(
                sprintf(
                    __(
                        'Cette syntaxe de déclaration des actions est dépréciée depuis la version %s de tiFy.' .
                        'Veuillez rectifier %s en utilisant la déclaration $this->appAddAction().',
                        'tify'
                    ),
                    '1.2.553',
                    get_called_class()
                )
            );
            exit;
        endif;

        // Dépréciation des déclaration des fonctions d'aide à la saisie ancienne génération
        if (!empty($this->Helpers)) :
            trigger_error(
                sprintf(
                    __(
                        'Cette syntaxe de déclaration des fonctions d\'aide à la saisie est dépréciée depuis la version %s de tiFy.' .
                        'Veuillez rectifier %s en utilisant la déclaration $this->appAddHelper().',
                        'tify'
                    ),
                    '1.2.553',
                    get_called_class()
                )
            );
            exit;
        endif;

    }

    /**
     * Appel de méthode
     */
    public function __call($method_name, $arguments)
    {
        // Exécution des actions à déclencher
        if (in_array($method_name, $this->tFyAppActions) && method_exists($this, $method_name)) :
            return call_user_func_array([$this, $method_name], $arguments);
        // Exécution des filtres à déclencher
        elseif (in_array($method_name, $this->CallFilters) && method_exists($this, $method_name)) :
            return call_user_func_array([$this, $method_name], $arguments);
        endif;
    }

    /**
     * Récupération d'attributs
     */
    public function __get($name)
    {
        return $this->__OldGet($name);
    }

    /**
     * Vérification d'existance d'attribut
     */
    public function __isset($name)
    {
        return $this->__OldIsset($name);
    }

    /**
     * Définition d'attribut
     * @deprecated
     */
    public function __set($name, $value)
    {
        return $this->__OldSet($name, $value);
    }
}