<?php
namespace tiFy\App;

abstract class FactoryConstructor extends \tiFy\App
{
    /**
     * Identifiant de qualification de la classe
     * @var string
     */
    protected $Id = '';

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
     * CONSTRUCTEUR
     *
     * @param string $id Identifiant de qualification
     * @param array $attrs Attributs de configuration
     *
     * @return void
     */
    public function __construct($id = null, $attrs = [])
    {
        parent::__construct();

        if ($id) :
            $this->Id = $id;
        else :
            $this->Id = self::tFyAppClassname();
        endif;

        // Traitement et déclaration des attributs de configuration
        if ($attrs = $this->parseAttrs($attrs)) :
            foreach ($attrs as $name => $value) :
                $this->setAttr($name, $value);
            endforeach;
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération de l'identifiant de qualification de la classe
     *
     * @return string
     */
    public function getId()
    {
        return $this->Id;
    }

    /**
     * Traitement des attributs de configuration
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return array
     */
    protected function parseAttrs($attrs = [])
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
    public function isAllowedAttr($name)
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
     * @return bool
     */
    public function setAttr($name, $value)
    {
        if (!$this->isAllowedAttr($name)) :
            return false;
        endif;

        $this->Attrs[$name] = $value;

        return true;
    }

    /**
     * Récupération de la liste des attributs de configuration
     *
     * @return array
     */
    public function getAttrList()
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
    public function issetAttr($name)
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
    public function getAttr($name, $default = '')
    {
        if (!$this->issetAttr($name)) :
            return $default;
        endif;

        return $this->Attrs[$name];
    }
}
