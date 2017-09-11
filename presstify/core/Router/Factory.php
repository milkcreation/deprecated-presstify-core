<?php
namespace tiFy\Core\Router;

class Factory extends \tiFy\App\Factory
{
    /**
     * Identifiant unique de qualification.
     * @var string
     */
    protected $Id;

    /**
     * Attributs de configuration
     * @var array {
     *
     * }
     */
    protected $Attrs            = [];

    /**
     * CONSTRUCTEUR
     */
    public function __construct($id, $attrs)
    {
        parent::__construct();

        $this->Id = $id;
        $this->Attrs = $this->parseAttrs($attrs);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Traitement des attributs de configuration
     *
     * @param array $attrs {
     *      Liste des attributs de configuration.
     *
     *      @param string $title Intitulé de qualification de la route
     *      @param string $desc Texte de descritpion de la route
     *      @param string object_type Type d'objet (post|taxonomy) en relation avec la route
     *      @param string object_name Nom de qualification de l'objet en relation (ex: post, page, category, tag ...)
     *      @param string option_name Clé d'index d'enregistrement en base de données
     *      @param int selected Id de l'objet en relation
     *      @param string list_order Ordre d'affichage de la liste de selection de l'interface d'administration
     *      @param string show_option_none Intitulé de la liste de selection de l'interface d'administration lorsqu'aucune relation n'a été établie
     * }
     *
     * @return array
     */
    protected function parseAttrs($attrs = array())
    {
        $defaults = [
            'title'             => $this->Id,
            'desc'              => '',
            'object_type'       => 'post',
            'object_name'       => 'page',
            'option_name'       => 'tFyCoreRouter_' . $this->Id,
            'selected'          => 0,
            'listorder'         => 'menu_order, title',
            'show_option_none'  => __('Aucune page choisie', 'tify'),
        ];
        $attrs = wp_parse_args($attrs, $defaults);

        if ($selected = (int)get_option($attrs['option_name'], 0)) :
            $attrs['selected'] = $selected;
        endif;

        return $attrs;
    }

    /**
     * Récupération de la liste des attributs de configuration
     *
     * @return array
     */
    public function getAttrs()
    {
        return $this->Attrs;
    }

    /**
     * Récupération d'un attribut de configuration
     *
     * @param string $name Identifiant qualifiant l'attribut de configuration à récupérer
     * @param mixed $default Valeur par défaut de retour
     *
     * @return mixed
     */
    public function getAttr($name, $default = '')
    {
        if (! isset($this->Attrs[$name]))
            return $default;

        return $this->Attrs[$name];
    }

    /**
     * Récupération de l'intitulé de qualification de la route
     * @see \tiFy\Core\Router\Factory::getAttr()
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getAttr('title');
    }

    /**
     * Type d'objet (post|taxonomy) en relation avec la route
     * @see \tiFy\Core\Router\Factory::getAttr()
     *
     * @return string
     */
    public function getObjectType()
    {
        return $this->getAttr('object_type');
    }

    /**
     * Nom de qualification de l'objet en relation (ex: post, page, category, tag ...)
     * @see \tiFy\Core\Router\Factory::getAttr()
     *
     * @return string
     */
    public function getObjectName()
    {
        return $this->getAttr('object_name');
    }

    /**
     * Clé d'index d'enregistrement en base de données de la route
     * @see \tiFy\Core\Router\Factory::getAttr()
     *
     * @return string
     */
    public function getOptionName()
    {
        return $this->getAttr('option_name');
    }

    /**
     * Id de l'objet en relation
     * @see \tiFy\Core\Router\Factory::getAttr()
     *
     * @return int
     */
    public function getSelected()
    {
        return (int)$this->getAttr('selected');
    }
}