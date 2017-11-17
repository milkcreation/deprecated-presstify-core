<?php
namespace tiFy\Core\Ui\Common\Traits;

trait Attrs
{
    /**
     * Identifiant de qualification
     * @var string
     */
    private $Id = null;

    /**
     * Liste des attributs de configuration
     * @var array
     */
    private $Attrs = [];

    /**
     * Définition de l'identifiant de qualification
     *
     * @param string $id Identifiant de qualification
     *
     * @return string
     */
    final protected function setId($id)
    {
        return $this->Id = $id;
    }

    /**
     * Récupération de l'identifiant de qualification
     *
     * @return string
     */
    final protected function getId()
    {
        return $this->Id;
    }

    /**
     * Déclaration de la liste des attributs de configuration
     *
     * @param array $attrs Tableau associatif de la liste des attributs de configuration
     *
     * @return array
     */
    final protected function setAttrList($attrs = [])
    {
        return $this->Attrs = $attrs;
    }

    /**
     * Déclaration ponctuelle d'attribut de configuration
     *
     * @param string $name Nom de qualification de l'attribut de configuration
     * @param string $value Valeur de l'attribut de configuration
     *
     * @return void
     */
    final public function setAttr($name, $value)
    {
        $this->Attrs[$name] = $value;
    }

    /**
     * Récupération de la liste des attributs de configuration
     *
     * @return array
     */
    final protected function getAttrList()
    {
        return $this->Attrs;
    }

    /**
     * Récupération d'un attribut de configuration
     *
     * @param string $name Identifiant de qualification de l'attribut
     * @param mixed $default Valeur de retour par défaut
     *
     * @return mixed
     */
    final protected function getAttr($name, $default = '')
    {
        if (!isset($this->Attrs[$name])) :
            return $default;
        endif;

        return $this->Attrs[$name];
    }

    /**
     * Récupération de la classe de rappel de l'object base de données
     *
     * @return \tiFy\Core\Db\Factory
     */
    final public function getDb()
    {
        return $this->getAttr('db', null);
    }

    /**
     * Récupération d'intitulé
     *
     * @param $label Identifiant de qualification de l'intitulé
     * @param $default Valeur de retour par défaut
     *
     * @return void|string|array Chaîne vide ou Chaîne de caractére ou Tableau associatif des intitulés
     */
    final public function getLabel($label = null, $default = '')
    {
        return $this->getAttr('labels')->get($label, $default);
    }
}