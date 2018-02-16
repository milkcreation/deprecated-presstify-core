<?php

namespace tiFy\Core\Layout;

use tiFy\App\Traits\App as TraitsApp;

abstract class AbstractFactory
{
    use TraitsApp;

    /**
     * Indicateur d'instanciation
     * @var bool
     */
    private $Instanciate = false;

    /**
     * Compteur d'instance d'affichage
     * @var int
     */
    private $Index = 0;

    /**
     * Liste des attributs de configuration
     * @var array
     */
    protected $Attrs = [];

    /**
     * Liste des identifiant de qualification des attributs de configuration permis
     * @var array
     */
    protected $AllowedAttrs = [];

    /**
     * Court-circuitage de l'implémentation
     *
     * @return void
     */
    private function __construct()
    {
        // Initialisation des événements
        if (!did_action('init') && !$this->Instanciate) :
            self::_tFyAppRegister($this);
            $this->Instanciate = true;
            $this->events();
        endif;
    }

    /**
     * Court-circuitage de l'implémentation
     *
     * @return void
     */
    protected function __clone()
    {

    }

    /**
     * Court-circuitage de l'implémentation
     *
     * @return void
     */
    protected function __wakeup()
    {

    }

    /**
     * Instanciation
     *
     * @param array Attributs de configuration
     *
     * @return $this
     */
    final public function __invoke($attrs = [])
    {
        $this->set('_index', ++$this->Index);
        $this->set('_id', 'tiFyLayout-' . $this->appShortname() . '--' . $this->getIndex());

        // Traitement des attributs de configuration
        if ($attrs = $this->parse($attrs)) :
            foreach ($attrs as $name => $value) :
                $this->set($name, $value);
            endforeach;
        endif;

        return $this;
    }

    /**
     * Récupération de l'affichage du controleur
     *
     * @return string
     */
    final public function __toString()
    {
        return $this->display();
    }

    /**
     * Initialisation
     */
    final public static function make()
    {
        return new static();
    }

    /**
     * Initialisation des événements
     *
     * @return void
     */
    protected function events()
    {
        if (method_exists($this, 'init')) :
            $this->appAddAction('init');
        endif;

        if (file_exists($this->appDirname() . '/Helpers.php')) :
            require_once $this->appDirname() . '/Helpers.php';
        endif;
    }

    /**
     * Traitement des attributs de configuration
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return array
     */
    protected function parse($attrs = [])
    {
        return $attrs;
    }

    /**
     * Vérifie si un attribut de configuration est permis
     *
     * @param string $name Identifiant de qualification de l'attribut de configuration
     *
     * @return bool
     */
    final public function valid($name)
    {
        if (empty($this->AllowedAttrs)) :
            return true;
        endif;

        return in_array($name, $this->AllowedAttrs);
    }

    /**
     * Définition d'un attribut de configuration
     *
     * @param string $name Identifiant de qualification de l'attribut de configuration
     * @param mixed $value Valeur de retour de l'attribut
     *
     * @return $this
     */
    final public function set($name, $value)
    {
        if ($this->valid($name)) :
            $this->Attrs[$name] = $value;
        endif;

        return $this;
    }

    /**
     * Récupération de la liste des attributs de configuration
     *
     * @return array
     */
    final public function all()
    {
        return $this->Attrs;
    }

    /**
     * Vérification d'existance d'un attribut de configuration
     *
     * @param string $name Identifiant de qualification de l'attribut
     *
     * @return bool
     */
    final public function is($name)
    {
        return isset($this->Attrs[$name]);
    }

    /**
     * Récupération d'un attribut de configuration
     *
     * @param string $name Identifiant de qualification de l'attribut
     * @param mixed $default Valeur de retour par défaut
     *
     * @return mixed
     */
    final public function get($name, $default = '')
    {
        if (!$this->is($name)) :
            return $default;
        endif;

        return $this->Attrs[$name];
    }

    /**
     * Récupération de la valeur du compteur d'instance
     *
     * @return int
     */
    final public function getIndex()
    {
        return $this->get('_index');
    }

    /**
     * Récupération de l'identifiant de qualification de la classe
     *
     * @return string
     */
    final public function getId()
    {
        return $this->get('_id');
    }

    /**
     * Récupération de la liste des clés d'indexes des attributs de configuration
     *
     * @return string[]
     */
    final public function keys()
    {
        return array_keys($this->Attrs);
    }

    /**
     * Récupération de la liste des valeurs des attributs de configuration
     *
     * @return mixed[]
     */
    final public function values()
    {
        return array_values($this->Attrs);
    }

    /**
     * Récupération une liste d'attributs de configuration
     *
     * @param string[] $keys Clé d'index des attributs à retourner
     *
     * @return array
     */
    final public function compact($keys = [])
    {
        if (empty($keys)) :
            return $this->all();
        endif;

        $attrs = [];
        foreach ($keys as $key) :
            $attrs[$key] = $this->get($key);
        endforeach;

        return $attrs;
    }

    /**
     * Affichage
     *
     * @return string
     */
    protected function display()
    {
        return '';
    }
}