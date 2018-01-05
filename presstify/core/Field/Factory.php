<?php
namespace tiFy\Core\Field;

class Factory extends \tiFy\App\FactoryConstructor
{
    /**
     * Compteur d'instance d'affichage de la classe
     * @var int
     */
    private static $Index = 0;

    /**
     * Numéro d'instance courante
     * @var int
     */
    private $CurrentIndex = 0;

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
            $this->Id = "tiFyCoreField-". (new \ReflectionClass($this))->getShortName() . "-" . $this->getIndex();
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
     * Appel dynamique des méthodes statiques
     *
     * @return null|static
     */
    final public static function __callStatic($name, $arguments)
    {
        // Appel de la méthode statique du contrôleur
        if (in_array($name, ['display', 'init', 'enqueue_scripts'])) :
            if ($name === 'display') :
                ++self::$Index;

                $attrs = $arguments[0];
                $instance = self::create($attrs);

                // Traitement et déclaration des attributs de configuration
                if ($attrs = $instance->parseAttrs($attrs)) :
                    foreach ($attrs as $key => $value) :
                        $instance->setAttr($key, $value);
                    endforeach;
                endif;
            else :
                $instance = self::create();
            endif;

            if (method_exists($instance, $name)) :
                return call_user_func_array([$instance, $name], $arguments);
            endif;
        endif;
    }

    /**
     * Instanciation de la classe
     *
     * @return self
     */
    final public static function create($attrs = [])
    {
        return new static($attrs);
    }

    /**
     * Traitement des attributs de configuration
     *
     * @param array $args Liste des attributs de configuration
     *
     * @return array
     */
    protected function parseAttrs($args = [])
    {
        $class = "tiFyCoreField-" . lcfirst(self::tFyAppShortname());
        $args['attrs']['class'] = isset($args['attrs']['class']) ? $class . ' ' . $args['attrs']['class'] : $class;

        // Traitement de l'attribut de configuration de la qualification de soumission du champ "name"
        $args = $this->parseAttrName($args);

        // Traitement de l'attribut de configuration de la valeur de soumission du champ "value"
        $args = $this->parseAttrValue($args);

        // Traitement de l'attribut de configuration de liste de selection "options"
        $args = $this->parseAttrOptions($args);

        return $args;
    }

    /**
     * Traitement de l'attribut de configuration de la qualification de soumission du champ "name"
     *
     * @param array $args Liste des attributs de configuration
     *
     * @return array
     */
    protected function parseAttrName($args = [])
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
    protected function parseAttrValue($args = [])
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
    protected function parseAttrOptions($args = [])
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
            $option['label'] = esc_attr($option['label']);
            $_options[] = $option;
        endforeach;
        $args['options'] = $_options;

        return $args;
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
     * Récupération de l'attribut de configuration de la qualification de soumission du champ "name"
     *
     * @return string
     */
    protected function getName()
    {
        return $this->getAttr('name', '');
    }

    /**
     * Récupération de l'attribut de configuration de la valeur initiale de soumission du champ "value"
     *
     * @return mixed
     */
    protected function getValue()
    {
        return $this->getAttr('value', null);
    }

    /**
     * Définition d'un attribut de balise HTML
     *
     * @param string $attrIdentifiant de qualification de l'attribut de balise HTML
     * @param string $value Valeur de l'attribut de balise HTML
     *
     * @return bool
     */
    protected function setHtmlAttr($attr, $value)
    {
        $attrs = $this->getAttr('attrs');
        $attrs[$attr] = $value;

        return $this->setAttr('attrs', $attrs);
    }

    /**
     * Récupération d'un attribut de balise HTML
     *
     * @param string $attr Identifiant de qualification de l'attribut de balise HTML
     * @param mixed $default Valeur de retour par défaut
     *
     * @return string
     */
    final public function getHtmlAttr($attr, $default = '')
    {
        if (!$attrs = $this->getAttr('attrs')) :
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
    final public function issetHtmlAttr($attr)
    {
        if (!$attrs = $this->getAttr('attrs')) :
            return false;
        endif;

        return isset($attrs[$attr]);
    }

    /**
     * Récupération de la liste des attributs de balises
     *
     * @return array
     */

    final public function getHtmlAttrs()
    {
        if (!$attrs = $this->getAttr('attrs')) :
            return;
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
     * Affichage de la liste des attributs de balise
     *
     * @return string
     */
    final public function htmlAttrs()
    {
        if (!$html_attrs = $this->getHtmlAttrs()) :
            return '';
        endif;

        echo implode(' ', $html_attrs);
    }

    /**
     * Récupération des attributs des options de liste de sélection
     *
     * @return array
     */
    final public function getOptionList()
    {
        return $this->getAttr('options', []);
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
            return;
        endif;

        foreach($options as $option) :
            if ($option['value'] == $value) :
                return $option;
            endif;
        endforeach;
    }

    /**
     * Affichage du contenu placé avant le champ
     *
     * @return string
     */
    final public function before()
    {
        echo $this->getAttr('before', '');
    }

    /**
     * Affichage du contenu placé après le champ
     *
     * @return string
     */
    final public function after()
    {
        echo $this->getAttr('after', '');
    }

    /**
     * Affichage du contenu de la balise champ
     *
     * @return string
     */
    final public function tagContent()
    {
        echo $this->getAttr('content', '');
    }

    /**
     * Affichage du contenu de la liste de selection
     *
     * @return string
     */
    final public function tagOptions()
    {
       echo WalkerOptions::output($this->getAttr('options', []), ['selected' => $this->getValue()]);
    }

    /**
     * Vérification de correspondance entre la valeur de coche et celle du champ
     *
     * @return bool
     */
    final public function isChecked()
    {
        if (!$this->issetHtmlAttr('value')) :
            return false;
        endif;

        return $this->getAttr('checked') === $this->getValue();
    }

    /**
     * Affichage
     *
     * @param array $args Liste des attributs de configuration du champ
     *
     * @return string
     */
    protected function display($attrs = [])
    {
        echo '';
    }
}