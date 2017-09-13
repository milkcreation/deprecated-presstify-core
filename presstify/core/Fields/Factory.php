<?php
namespace tiFy\Core\Fields;

class Factory extends \tiFy\App\Factory
{
    /**
     * @var string
     */
    private $Attrs = '';

    /**
     * Liste des attributs HTML autorisés
     * @var array
     */
    protected $AllowedHtmlAttrs = [];

    /**
     * CONSTRUCTEUR
     */
    public function __construct($attrs)
    {
        $this->Attrs = $attrs;
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
     *
     */
    final public function getAttr($name, $default = '')
    {
        if (isset($this->Attrs[$name]))
            return $this->Attrs[$name];

        return $default;
    }

    /**
     *
     */
    final public function getId()
    {
        return $this->getAttr('id');
    }

    /**
     *
     */
    final public function getName()
    {
        return $this->getAttr('name');
    }

    /**
     *
     */
    final public function getValue()
    {
        return $this->getAttr('value');
    }

    /**
     *
     */
    final public function getContainerId()
    {
        return $this->getAttr('container_id');
    }

    /**
     *
     */
    final public function getContainerClass()
    {
        return ($class = $this->getAttr('container_class')) ? ' '. $class : '';
    }

    /**
     *
     */
    final public function getHtmlAttrs()
    {
        if (! $this->AllowedHtmlAttrs) :
            return;
        endif;

        if(! $html_attrs = $this->getAttr('html_attrs')) :
            return;
        endif;

        $attrs = [];
        foreach ((array)$this->AllowedHtmlAttrs as $name) :
            if (!isset($html_attrs[$name])) :
                continue;
            endif;
            $attrs[]= $name . "=" . $html_attrs[$name];
        endforeach;

        return implode(' ', $attrs);
    }

    /**
     *
     */
    public static function display($attrs = [])
    {
        echo '';
    }

    /**
     *
     */
    public static function content($attrs = [])
    {
        ob_start();
        static::display($attrs);
        return ob_get_clean();
    }
}