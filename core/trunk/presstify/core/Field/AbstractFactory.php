<?php

namespace tiFy\Core\Field;

use tiFy\App\Traits\App as TraitsApp;

class AbstractFactory
{
    use TraitsApp;

    /**
     * Liste des instances
     * @var
     */
    private static $Instance = [];

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
     * Initialisation
     *
     * @return self
     */
    final public static function make()
    {
        return new static();
    }

    /**
     * Instanciation
     *
     * @param string $id Identifiant de qualification du controleur d'affichage
     * @param array Attributs de configuration
     *
     * @return $this
     */
    final public function __invoke($id = null, $attrs = [])
    {
        $lower_name = $this->appLowerName();
        $instance_prefix = "tify.field.{$lower_name}";

        if (is_array($id)) :
            $attrs = $id;
            $id = null;
        endif;

        if (is_null($id) && isset($attrs['id'])) :
            $id = $attrs['id'];
        elseif (is_null($id)) :
            $id = uniqid();
        endif;

        if (!isset(self::$Instance["{$instance_prefix}.{$id}"])) :
            $instance = $this;
            $this->set('index', ++$this->Index);
            $this->set('id', $id);
            self::$Instance["{$instance_prefix}.{$id}"] = $instance;
        else :
            $instance = self::$Instance["{$instance_prefix}.{$id}"];
        endif;

        // Traitement des attributs de configuration
        if ($attrs = $this->parse($attrs)) :
            foreach ($attrs as $name => $value) :
                if (!in_array($name, ['id', 'index'])) :
                    $this->set($name, $value);
                endif;
            endforeach;
        endif;

        return $instance;
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
     * Initialisation des événements
     *
     * @return void
     */
    protected function events()
    {
        if (method_exists($this, 'init')) :
            $this->appAddAction('init');
        endif;
    }

    /**
     * Traitement des attributs de configuration
     *
     * @param array $args Liste des attributs de configuration
     *
     * @return array
     */
    protected function parse($args = [])
    {
        $class = "tiFyField-" . lcfirst(self::tFyAppShortname());
        $args['attrs']['class'] = isset($args['attrs']['class']) ? $class . ' ' . $args['attrs']['class'] : $class;

        // Traitement de l'attribut de configuration de la qualification de soumission du champ "name"
        $args = $this->parseName($args);

        // Traitement de l'attribut de configuration de la valeur de soumission du champ "value"
        $args = $this->parseValue($args);

        // Traitement de l'attribut de configuration de liste de selection "options"
        $args = $this->parseOptions($args);

        return $args;
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
        return $this->get('index');
    }

    /**
     * Récupération de l'identifiant de qualification de la classe
     *
     * @return string
     */
    final public function getId()
    {
        return $this->get('id');
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
     * Traitement de l'attribut de configuration de la qualification de soumission du champ "name"
     *
     * @param array $args Liste des attributs de configuration
     *
     * @return array
     */
    protected function parseName($args = [])
    {
        if (isset($args['name'])) :
            $args['attrs']['name'] = $args['name'];
        endif;

        return $args;
    }

    /**
     * Traitement de l'attribut de configuration de la valeur initiale de soumission du champ "value"
     *
     * @param array $args Liste des attributs de configuration
     *
     * @return array
     */
    protected function parseValue($args = [])
    {
        if (isset($args['value'])) :
            $args['attrs']['value'] = $args['value'];
        endif;

        return $args;
    }

    /**
     * Traitement de l'attribut de configuration de liste de selection "options"
     *
     * @param array $args Liste des attributs de configuration
     *
     * @return array
     */
    protected function parseOptions($args = [])
    {
        if (!isset($args['options'])) :
            return $args;
        endif;

        if (!is_array($args['options'])) :
            $options = array_map('trim', explode(',', (string)$args['options']));
        else:
            $options = $args['options'];
        endif;

        $_options = [];
        $id = 0;
        foreach($options as $k => $v) :
            if (is_int($k)) :
                if (!is_array($v)) :
                    $v = [
                        'label' => $v,
                        'value' => $k
                    ];
                else :
                    if (!isset($v['value'])) :
                        $v['value'] = $k;
                    endif;
                endif;
            else :
                $v = [
                    'label' => $v,
                    'value' => $k
                ];
            endif;
            $option = array_merge(
                [
                    'id'     => $id++,
                    'group'  => false,
                    'attrs'  => [],
                    'parent' => ''
                ],
                $v
            );

            // Formatage des attributs
            if (!isset($option['label'])) :
                $option['label'] = $option['value'];
            endif;

            $_options[] = $option;
        endforeach;
        $args['options'] = $_options;

        return $args;
    }

    /**
     * Récupération de l'attribut de configuration de la qualification de soumission du champ "name"
     *
     * @return string
     */
    protected function getName()
    {
        return $this->get('name', '');
    }

    /**
     * Récupération de l'attribut de configuration de la valeur initiale de soumission du champ "value"
     *
     * @return mixed
     */
    protected function getValue()
    {
        return $this->get('value', null);
    }

    /**
     * Définition d'un attribut de balise HTML
     *
     * @param string $attrIdentifiant de qualification de l'attribut de balise HTML
     * @param string $value Valeur de l'attribut de balise HTML
     *
     * @return $this
     */
    protected function setAttr($attr, $value)
    {
        $attrs = $this->get('attrs');
        $attrs[$attr] = $value;

        return $this->set('attrs', $attrs);
    }

    /**
     * Récupération d'un attribut de balise HTML
     *
     * @param string $attr Identifiant de qualification de l'attribut de balise HTML
     * @param mixed $default Valeur de retour par défaut
     *
     * @return string
     */
    final public function getAttr($attr, $default = '')
    {
        if (!$attrs = $this->get('attrs')) :
            return $default;
        endif;

        if (isset($attrs[$attr])) :
            return $attrs[$attr];
        endif;

        return $default;
    }

    /**
     * Vérification d'existance d'un attribut de balise HTML
     *
     * @param string $attr Identifiant de qualification de l'attribut de balise HTML
     *
     * @return string
     */
    final public function issetAttr($attr)
    {
        if (!$attrs = $this->get('attrs')) :
            return false;
        endif;

        return isset($attrs[$attr]);
    }

    /**
     * Récupération de la liste des attributs de balises
     *
     * @return array
     */

    final public function getAttrs()
    {
        if (!$attrs = $this->get('attrs')) :
            return [];
        endif;

        $html_attrs = [];
        foreach ($attrs as $k => $v) :
            if (is_array($v)) :
                $v = rawurlencode(json_encode($v));
            endif;
            if (is_int($k)) :
                $html_attrs[]= "{$v}";
            else :
                $html_attrs[]= "{$k}=\"{$v}\"";
            endif;
        endforeach;

        return $html_attrs;
    }

    /**
     * Récupération des attributs des options de liste de sélection
     *
     * @return array
     */
    final public function getOptionList()
    {
        return $this->get('options', []);
    }

    /**
     * Récupération des attributs des options de liste de sélection
     *
     * @return string[]
     */
    final public function getOptionValues()
    {
        if (!$options = $this->getOptionList()) :
            return [];
        endif;

        return array_column($options, 'value');
    }

    /**
     * Récupération des attributs d'une option de liste de sélection selon sa valeur
     *
     * @param mixed $value Valeur de l'option à récupérer
     *
     * @return null|array
     */
    final public function getOption($value)
    {
        if (!$options = $this->getOptionList()) :
            return null;
        endif;

        foreach($options as $option) :
            if ($option['value'] == $value) :
                return $option;
            endif;
        endforeach;

        return null;
    }

    /**
     * Affichage du contenu placé avant le champ
     *
     * @return void
     */
    final public function before()
    {
        echo $this->get('before', '');
    }

    /**
     * Affichage du contenu placé après le champ
     *
     * @return void
     */
    final public function after()
    {
        echo $this->get('after', '');
    }

    /**
     * Affichage de la liste des attributs de balise
     *
     * @return string
     */
    final public function attrs()
    {
        if (!$html_attrs = $this->getAttrs()) :
            return '';
        endif;

        echo implode(' ', $html_attrs);
    }

    /**
     * Affichage du contenu de la balise champ
     *
     * @return void
     */
    final public function content()
    {
        echo $this->get('content', '');
    }

    /**
     * Affichage du contenu de la liste de selection
     *
     * @return void
     */
    final public function options()
    {
       echo WalkerOptions::output($this->get('options', []), ['selected' => $this->getValue()]);
    }

    /**
     * Vérification de correspondance entre la valeur de coche et celle du champ
     *
     * @return bool
     */
    final public function isChecked()
    {
        if (!$this->issetAttr('value')) :
            return false;
        endif;

        return $this->get('checked') === $this->getValue();
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