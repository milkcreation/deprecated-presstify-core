<?php
namespace tiFy\Core\Taboox;

use tiFy\Core\Taboox\Taboox;

class Factory extends \tiFy\App\Factory
{
    /**
     * Identifiant d'accroche de la page d'affichage
     * @var string
     */
    protected $Hookname         = null;

    /**
     * Liste des attributs de configuration
     * @var array
     */
    protected $Attrs            = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct($hookname, $attrs = [])
    {
        parent::__construct();

        // Définition des paramètres
        // Identifiant d'accroche
        Taboox::setHookname($hookname);
        $this->Hookname = $hookname;

        // Attributs de configuration
        $this->Attrs = $this->parseAttrs($attrs);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Traitement des arguments de configuration
     */
    protected function parseAttrs($attrs = [])
    {
        return $attrs;
    }

    /**
     * Récupération de la liste de attributs de configuration
     *
     * @return array
     */
    final public function getAttrList()
    {
        return $this->Attrs;
    }

    /**
     * Récupération d'un attribut de configuration
     *
     * @param string $name Nom de l'attribut de configuration
     * @param mixed $default Valeur de retour par défaut
     *
     * @return mixed
     */
    final public function getAttr($name, $default = '')
    {
        if (!isset($this->Attrs[$name])) :
            return $default;
        endif;

        return $this->Attrs[$name];
    }

    /**
     * Identifiant d'accroche de la page d'affichage
     *
     * @return string
     */
    final public function getHookname()
    {
        return $this->Hookname;
    }

    /**
     * Récupération de l'identifiant de qualification
     *
     * @return string
     */
    final public function getId()
    {
        return $this->getAttr('id');
    }

    /**
     * Récupération de l'object_type de la page d'affichage
     *
     * @return string (post_type|taxonomy|options|user)
     */
    final public function getObjectType()
    {
        return $this->getAttr('object_type');
    }

    /**
     * Récupération de l'object_name de la page d'affichage
     *
     * @return string (post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|user)
     */
    final public function getObjectName()
    {
        return $this->getAttr('object_name');
    }
}