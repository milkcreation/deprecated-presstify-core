<?php
namespace tiFy\Core\Fields;

class Factory extends \tiFy\App\FactoryConstructor
{
    /**
     * Instance
     * @var int
     */
    protected static $Instance = 0;

    /**
     * Liste des attributs HTML autorisés
     * @var array
     */
    protected $AllowedHtmlAttrs = [];

    /**
     * CONSTRUCTEUR
     *
     * @param string $id Identifiant de qualification
     * @param array $attrs Attributs de configuration
     *
     * @return void
     */
    public function __construct($id = null, $attrs = [])
    {
        self::$Instance++;

        if(!$id && isset($attrs['id'])) :
            $id = $attrs['id'];
        else :
            $id = "tiFyCoreFields-". (new \ReflectionClass($this))->getShortName() . "-" . static::$Instance;
        endif;

        parent::__construct($id, $attrs);
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    public static function init(){ }

    /**
     * CONTROLEURS
     */
    /**
     * Traitement des attributs de configuration
     *
     * @param array $args Liste des attributs de configuration
     *
     * @return array
     */
    protected function parseAttrs($args = [])
    {
        $_args = [];

        // Définition des attributs de balise du champ
        $_args['attrs'] = isset($args['attrs']) ? $args['attrs'] :[];
        unset($args['attrs']);

        // Rétrocompatibilité
        if (isset($args['html_attrs'])) :
            $_args['attrs'] = \wp_parse_args($_args['attrs'], $args['html_attrs']);
            unset($args['html_attrs']);
        endif;
        if (isset($args['container_id']) && !isset($_args['attrs']['id'])) :
            $_args['attrs']['id'] = $args['container_id'];
        endif;
        if (isset($args['container_class']) && !isset($_args['attrs']['class'])) :
            $_args['attrs']['class'] = $args['container_class'];
        endif;
        if (isset($args['name']) && !isset($_args['attrs']['name'])) :
            $_args['attrs']['name'] = $args['name'];
        endif;
        if (isset($args['value']) && !isset($_args['attrs']['value'])) :
            $_args['attrs']['value'] = $args['value'];
        endif;

        $_args = \wp_parse_args($_args, $args);

        $class = "tiFyCoreFields-" . self::tFyAppShortname();
        $_args['attrs']['class'] = isset($_args['attrs']['class']) ? $class . ' ' . $_args['attrs']['class'] : $class;

        return $_args;
    }

    /**
     * Vérification la permission d'utilisation d'un attribut de balise
     *
     * @param string $html_attr Identifiant de qualification de l'attribut
     *
     * @return bool
     */
    final public function isAllowedHtmlAttr($html_attr)
    {
        if (empty($this->AllowedHtmlAttrs)) :
            return true;
        endif;

        return in_array($html_attr, $this->AllowedHtmlAttrs);
    }

    /**
     * Définition d'un attribut de balise
     *
     * @param string $html_attr Identifiant de qualification de l'attribut de balise
     * @param string $value Valeur de l'attribut de balise
     *
     * @return bool
     */
    protected function setHtmlAttr($name, $value)
    {
        if (!$this->isAllowedHtmlAttr($name)) :
            return false;
        endif;

        $attrs = $this->getAttr('attrs');
        $attrs[$name] = $value;

        return $this->setAttr('attrs', $attrs);
    }

    /**
     * Récupération d'un attribut de balise
     *
     * @param string $html_attr Identifiant de qualification de l'attribut de balise
     *
     * @return string
     */
    final public function getHtmlAttr($html_attr, $default = '')
    {
        if (!$attrs = $this->getAttr('attrs')) :
            return $default;
        endif;

        if (isset($attrs[$html_attr])) :
            return $attrs[$html_attr];
        endif;

        return $default;
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
            if (!in_array($k, ['id', 'class', 'name']) && !$this->isAllowedAttr($k)) :
                continue;
            endif;
            if (is_array($v)) :
                $v = rawurlencode(json_encode($v));
            endif;
            $html_attrs[]= "{$k}=\"{$v}\"";
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
     * Affichage
     *
     * @param string $id Identifiant de qualification du champ
     * @param array $args {
     *      Liste des attributs de configuration du champ
     *
     * }
     *
     * @return string
     */
    public static function display($id = null, $attrs = [])
    {
        echo '';
    }

    /**
     * Récupération de la valeur de sortie de l'affichage d'un champ
     *
     * @param string $id Identifiant de qualification du champ
     * @param array $args {
     *      Liste des attributs de configuration du champ
     *
     * }
     *
     * @return string
     */
    public static function content($id = null, $attrs = [])
    {
        ob_start();
        static::display($id = null, $attrs);
        return ob_get_clean();
    }
}