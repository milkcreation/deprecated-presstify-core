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
     * Identifiant d'accroche de la page d'affichage
     *
     * @return string
     */
    final public function getHookname()
    {
        return $this->Hookname;
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
     * Récupération de l'identifiant de qualification
     *
     * @return string
     */
    final public function getId()
    {
        return $this->getAttr('id');
    }

    /**
     * Récupération de l'object de la page d'affichage
     *
     * @return string
     */
    final public function getObject()
    {
        return $this->getAttr('object');
    }

    /**
     * Récupération de l'object_type de la page d'affichage
     *
     * @return string
     */
    final public function getObjectType()
    {
        return $this->getAttr('object_type');
    }
}