<?php
namespace tiFy\Core\Ui;

use tiFy\Core\Labels\Labels;
use tiFy\Core\Db\Db;

class Factory extends \tiFy\App
{
    /**
     * Identifiant de qualification
     * @var string
     */
    protected $Id = null;

    /**
     * Classe de rappel d'affichage du gabarit
     * @var object
     */
    protected $Template = null;

    /**
     * Liste des attributs de configuration des gabarits parent
     * @var array
     */
    protected $Parents = [];

    /**
     * Identifiant de qualification du gabarit parent courant
     * @var null|string
     */
    protected $ParentId = null;

    /**
     * Liste des attributs de configuration
     * @var array
     */
    protected $Attrs = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct($id, $attrs = [])
    {
        parent::__construct();

        $this->Id = $id;
        $this->Attrs = $this->parseAttrs($attrs);

        // Définition des événements de déclenchement
        $this->init();
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public function init()
    {
        if (!$cb = $this->getAttr('cb')) :
            return;
        endif;
        if (!class_exists($cb)) :
            return;
        endif;

        $id = $this->getId();
        $attrs = $this->getAttrList();

        $this->Template = new $cb($id, $attrs);

        // Déclenchement de l'événenement d'initialisation globale dans le gabarit
        $this->getTemplate()->init();
    }

    /**
     * CONTROLEURS
     */
    /**
     * Traitement des attributs de configuration
     *
     * @param array $attrs Tableau associatif des attributs de configuration à traiter
     *
     * @return array
     */
    public function parseAttrs($attrs = [])
    {
        // Définition de la classe de rappel d'affichage du gabarit
        $cb = !empty($attrs['cb']) ? $attrs['cb'] : null;
        if (empty($cb)) :
        elseif (in_array($cb, $this->getParentIds())) :
            $cb = self::tFyAppAttr('Namespace', get_class($this)) . "\\Templates\\{$cb}\\{$cb}";
        endif;
        $attrs['cb'] = $cb;

        // Récupération du gabarit parent
        if (class_exists($attrs['cb'])) :
            $this->ParentId = $this->queryParentId($attrs['cb']);
        endif;

        // Définition de la classe de rappel de la base de données
        if (!isset($attrs['db'])) :
            $db = '';
        else :
            $db = $attrs['db'];
        endif;
        if ($db === false) :
        elseif ($db instanceof \tiFy\Core\Db\Factory) :
        elseif (is_string($db) && ($_db = Db::get($db))) :
            $attrs['db'] = $_db;
        elseif ($db = $this->getParentAttr('db')) :
            $attrs['db'] = Db::get($db);
        endif;

        // Définition de la classe de rappel des intitulés
        $labels = !empty($attrs['labels']) ? $attrs['labels'] : null;
        if ($labels instanceof \tiFy\Core\Labels\Factory) :
        elseif (is_string($labels) && ($_labels = Labels::get($labels))) :
            $attrs['labels'] = $_labels;
        elseif (is_array($labels)) :
            $attrs['labels'] = Labels::register('_UiLabels-' . $this->getId(), $attrs['labels']);
        elseif ($label = $this->getParentAttr('label')) :
            $attrs['labels'] = Labels::get($label);
        else :
            $attrs['labels'] = Labels::register('_UiLabels-' . $this->getId());
        endif;

        return $attrs;
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

    /**
     * Récupération de l'identifiant de qualification
     *
     * @return string
     */
    final public function getId()
    {
        return $this->Id;
    }

    /**
     * Récupération de la liste des attributs de configuration des gabarits parent
     *
     * @return array
     */
    final public function getParentList()
    {
        return $this->Parents;
    }

    /**
     * Récupération des attributs de configuration d'un garabit parent
     *
     * @param string $parent_id Identifiant de qualification du parent
     *
     * @return array
     */
    final public function getParentAttrList($parent_id)
    {
        if (!$parents = $this->getParentList()) :
            return [];
        endif;

        if(isset($parents[$parent_id])) :
            return $parents[$parent_id];
        endif;

        return [];
    }

    /**
     * Récupération de la liste des identifiants de qualification des gabarits parents
     *
     * @return string[]
     */
    final public function getParentIds()
    {
        return array_keys($this->Parents);
    }

    /**
     * Récupération de l'identifiant de qualification du gabarit parent courant
     *
     * @return object
     */
    final public function getParentId()
    {
        return $this->ParentId;
    }

    /**
     * Récupération d'un attribut du gabarit parent courant
     *
     * @param string $name Identifiant de qualification de l'attribut
     * @param mixed $default Valeur de retour par défaut
     *
     * @return mixed
     */
    final public function getParentAttr($name, $default = '')
    {
        if (!$parent_id = $this->getParentId()) :
            return $default;
        endif;

        if (!$parent_attrs = $this->getParentAttrList($parent_id)) :
            return $default;
        endif;

        if (isset($parent_attrs[$name])) :
            return $parent_attrs[$name];
        endif;

        return $default;
    }

    /**
     * Rcupération de l'identifiant de qualification d'un gabarit parent
     *
     * @param obj|string $classname
     *
     * @return string
     */
    final public function queryParentId($classname)
    {
        $current_id = (new \ReflectionClass($classname))->getShortName();
        if (in_array($current_id, $this->getParentIds())) :
            return $current_id;
        endif;

        if (!$parent_class = get_parent_class($classname)):
            return false;
        endif;

        if (!preg_match("#". preg_quote(self::tFyAppAttr('Namespace'), '\\') ."#", $parent_class)) :
            return false;
        endif;

        return $this->queryParentId($parent_class);
    }

    /**
     * Récupération de la classe de rappel du gabarit
     *
     * @return null|object
     */
    final public function getTemplate()
    {
        return $this->Template;
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
    final public function getLabel($label, $default = '')
    {
        return $this->getAttr('labels')->get($label, $default);
    }

    /**
     * Récupération de la liste des classes de rappel des gabarits de traitement externe
     *
     * @return array|\tiFy\Core\Ui\Factory[]
     */
    public function getHandleList()
    {
        return [];
    }

    /**
     * Récupération d'une classe de rappel de gabarit de traitement externe
     *
     * @param string $task Tâche du gabarit (edit|list|import ...)
     *
     * @return null|\tiFy\Core\Ui\Factory
     */
    public function getHandle($task)
    {
        if (!$handle_list = $this->getHandleList()) :
            return;
        endif;

        if(!isset($handle_list[$task])) :
            return;
        endif;

        return $handle_list[$task];
    }

    /**
     * Affichage
     */
    public function render()
    {
        if ($this->Template) :
            $this->Template->render();
        endif;
    }
}