<?php
namespace tiFy\Core\Roles\Templates\Admin;

class UserList extends \tiFy\Core\Templates\Admin\Model\ListUser\ListUser
{
    /**
     * Liste des attributs de configuration
     * @var array
     */
    private $Attrs = [];

    /**
     * CONSTRUCTEUR
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return void
     */
    public function __construct($attrs = [])
    {
        parent::__construct();

        $this->Attrs = $this->parseAttrs($attrs);
    }

    /**
     * PARAMETRES
     */
    /**
     * Définition de la liste des roles utilisateurs de la table
     *
     * @return string[]
     */
    /** == Définition des rôles des utilisateurs de la table == **/
    public function set_roles()
    {
        if ($roles = $this->getAttr('roles', [])) :
            return $roles;
        endif;

        return [];
    }

    /**
     * CONTROLEURS
     */
    /**
     * Traitement des attributs de configuration
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return array
     */
    final public function parseAttrs($attrs = [])
    {
        return $attrs;
    }

    /**
     * Récupération de la liste des attributs de configuration
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
     * @param string $name Identifiant de qualification de l'attribut
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
}