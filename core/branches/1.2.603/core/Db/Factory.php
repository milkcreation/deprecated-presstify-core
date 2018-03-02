<?php
namespace tiFy\Core\Db;

use tiFy\Core\Db\Make;
use tiFy\Core\Db\Handle;
use tiFy\Core\Db\Meta;
use tiFy\Core\Db\Parse;
use tiFy\Core\Db\Query;
use tiFy\Core\Db\Select;

class Factory extends \tiFy\App\FactoryConstructor
{
    /**
     * Identifiant unique de la table
     * @var string
     */
    public $ID = '';

    /**
     * Nom de qualification la table hors prefix
     * @var string
     */
    public $ShortName = '';

    /**
     * Nom réel de la table (prefixé)
     * @var string
     */
    public $Name = '';

    /**
     * Numéro de version
     * @var int
     */
    public $Version = 0;

    /**
     * Préfixe des intitulés de colonne
     * @var string
     */
    public $ColPrefix = '';

    /**
     * Liste des noms de colonnes préfixés
     * @var array
     */
    public $ColNames = [];

    /**
     * Cartographie des noms de colonnes
     * @var array
     */
    public $ColMap = [];

    /**
     * Nom de la colonne clé primaire
     * @var null
     */
    public $Primary = null;

    /**
     * Liste des clés d'index
     * @var array
     */
    public $IndexKeys = [];

    /**
     * Nom des colonnes ouvertes à la recherche de termes
     * @var array
     */
    public $SearchColNames = [];

    /**
     * Moteur de requête SQL
     * @var null
     */
    public $SQLEngine = null;

    /**
     * Nom de la table de metadonnées
     * @var null
     */
    public $MetaType = null;

    /**
     * Variables de requête privées
     * @var array
     */
    public $PrivateQueryVars = [
        'include',
        /** @todo deprecated alias item__in * */
        'item__in',
        'exclude',
        /** @todo deprecated alias item__not_in * */
        'item__not_in',
        'search',
        /** @todo deprecated alias s * */
        's',
        'fields',
        'per_page',
        'paged',
        'order',
        'orderby',
        'item_meta',
        'limit',
    ];

    // Classe de rappel
    public $Handle, $Meta, $Parse, $Query, $Select;

    /**
     * CONSTRUCTEUR
     *
     * @param $id Identifiant unique de qualification
     * @param array $attrs  {
     *      Attributs de la table de base de données
     *
     *      @param bool $install Activation de l'installation de la table de base de données
     *      @param int $version Numéro de version de la table
     *      @param string $name Nom de la base de données (hors préfixe)
     *      @param string $primary Colonne de clé primaire
     *      @param string $col_prefix Prefixe des colonnes de la table
     *      @param array $columns {
     *          Liste des attributs de configuration des colonnes
     *
     *
     *      }
     *      @param array $keys {
     *          Liste des attributs de configuration des clefs d'index
     *
     *      }
     *      @param string[] $seach {
     *          Liste des colonnes ouvertes à la recherche
     *
     *      }
     *      @param bool|string $meta Activation ou nom de la table de stockage des metadonnées
     *      @param \wpdb|object $sql_engine Moteur (ORM) de requête en base de données
     * }
     *
     * @return void
     */
    public function __construct($id, $attrs = [])
    {
        // Définition de l'identifiant de la table
        $this->ID = $id;

        // Définition des attributs de la classe
        $defaults = [
            'install'    => false,
            'version'    => 1,
            'name'       => '',
            'primary'    => '',
            'col_prefix' => '',
            'columns'    => [],
            'keys'       => [],
            'search'     => [],
            'meta'       => null,
            // moteur de requete SQL global $wpdb par défaut | new \wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
            'sql_engine' => null,
        ];
        $attrs    = wp_parse_args($attrs, $defaults);
        extract($attrs, EXTR_SKIP);

        // Définition de la version
        $this->Version = $version;

        // Définition du moteur de requête SQL
        $this->setSQLEngine($sql_engine);

        // Définition du préfixe par défaut des noms de colonnes
        $this->setColPrefix($col_prefix);

        /// Traitement des attributs de colonnes
        foreach ((array)$columns as $col_name => $attrs) :
            $this->setColAttrs($col_name, $attrs);
        endforeach;

        // Définition de la clé primaire
        $this->setPrimary($primary);

        // Définition des clés d'index
        $this->setIndexKeys($keys);

        // Définition des colonnes ouvertes à la recherche de termes
        $this->setSearchColNames($search);

        // Définition du nom de la table en base de données
        $this->setName($name);

        // Définition de nom de la table de metadonnées en base
        $this->setMeta($meta);

        if ($install) :
            $this->install();
        endif;
    }

    /**
     * Définition du moteur (ORM) de requête en base de données
     *
     * @param \wpdb|object $sql_engine Moteur (ORM) de base de données
     *
     * @return \wpdb|object
     */
    private function setSQLEngine($sql_engine = null)
    {
        if (is_null($sql_engine) || !($sql_engine instanceof \wpdb)) :
            global $wpdb;

            return $this->SQLEngine = $wpdb;
        endif;

        return $this->SQLEngine = $sql_engine;
    }

    /**
     * Définition du préfixe des colonnes
     *
     * @param string $col_prefix Intitulé du préfixe des colonnes
     *
     * @return array
     */
    private function setColPrefix($col_prefix = '')
    {
        return $this->ColPrefix = $col_prefix;
    }

    /**
     * Définition des atttributs de configuration d'un colonne (prefixage + cartographie)
     *
     * @param string $name Nom de déclaration de la colonne (hors préfixe)
     * @param array $attrs Attributs de configuration de la colonne
     *
     * @return array
     */
    private function setColAttrs($name, $attrs = [])
    {
        $defaults = [
            'prefix' => true,
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        $_name = $attrs['prefix'] ? $this->ColPrefix . $name : $name;

        // Ajout à la liste des colonnes
        array_push($this->ColNames, $_name);

        // Ajout à la cartographie des colonnes
        $this->ColMap[$name] = $_name;

        // Déclaration de la variable des stockage des attributs de colonne
        $col = "col_{$_name}";

        return $this->{$col} = $attrs;
    }

    /**
     * Définition de la clé primaire
     *
     * @param string Nom de la colonne de clé primaire
     *
     * @return string
     */
    private function setPrimary($primary_col = '')
    {
        if (empty($this->ColNames)) :
            return;
        endif;

        if ($primary_col && in_array($primary_col, $this->ColNames)) :
            return $this->Primary = $primary_col;
        else :
            return $this->Primary = reset($this->ColNames);
        endif;
    }

    /** == Définition des clés d'index == **/
    private function setIndexKeys($keys = [])
    {
        $this->IndexKeys = $keys;
    }

    /** == Définition des colonnes ouvertes à la recherche de termes == **/
    private function setSearchColNames($search_columns = [])
    {
        foreach ((array)$search_columns as $col_name) {
            if (isset($this->ColMap[$col_name])) {
                array_push($this->SearchColNames, $this->ColMap[$col_name]);
            }
        }
    }

    /** == Définition du nom de la table en base de données == **/
    private function setName($name = '')
    {
        if ( ! $name) {
            $name = $this->ID;
        }

        $this->ShortName = $name;

        if ( ! in_array($name, $this->sql()->tables)) :
            array_push($this->sql()->tables, $name);
            $this->sql()->set_prefix($this->sql()->base_prefix);
        endif;

        return $this->Name = $this->sql()->{$name};
    }

    /** == Définition du nom de la table en base de données == **/
    private function setMeta($meta_type = null)
    {
        if ( ! $meta_type) {
            return;
        }

        if (is_bool($meta_type)) {
            $meta_type = $this->ShortName;
        }

        $table = $meta_type . 'meta';

        if ( ! in_array($table, $this->sql()->tables)) :
            array_push($this->sql()->tables, $table);
            $this->sql()->set_prefix($this->sql()->base_prefix);
        endif;

        return $this->MetaType = $meta_type;
    }

    /**
     * Récupération de la clé primaire
     *
     * @return string
     */
    final public function getPrimary()
    {
        return $this->Primary;
    }

    /**
     * Vérification si une colonne est la colonne déclarée comme primaire
     *
     * @param string $name Identifiant de qualification de la colonne ou nom réel (préfixé) de la colonne
     *
     * @return bool
     */
    final public function isPrimary($name)
    {
        if (!$name = $this->isCol($name)) :
            return false;
        endif;

        return ($this->getPrimary() === $name);
    }

    /**
     * Récupération du nom de la table préfixée
     *
     * @return string
     */
    final public function getName()
    {
        return $this->Name;
    }

    /**
     * Récupération de la liste des noms de colonnes réels (préfixés)
     *
     * @return string[]
     */
    final public function getColNames()
    {
        return $this->ColNames;
    }

    /**
     * Récupération du nom préfixé d'une colonne selon son identifiant de qualification
     *
     * @param string $id Identifiant de qualification d'une colonne
     *
     * @return string
     */
    final public function getColMap($id)
    {
        if (isset($this->ColMap[$id])) :
            return $this->ColMap[$id];
        endif;

        return '';
    }

    /**
     * Vérification d'existance d'une colonne
     *
     * @param string $name Identifiant de qualification de la colonne ou nom réel (préfixé) de la colonne
     *
     * @return bool|string Nom de la colonne préfixée
     */
    final public function isCol($name)
    {
        if ($this->isPrivateQueryVar($name)) :
            return false;
        elseif (in_array($name, $this->getColNames())) :
            return $name;
        elseif ($_name = $this->getColMap($name)) :
            return $_name;
        endif;

        return false;
    }

    /**
     * Récupération des attributs de configuration d'une colonne
     *
     * @param string $name Identifiant de qualification de la colonne ou nom réel (préfixé) de la colonne
     *
     * @return array
     */
    final public function getColAttrs($name)
    {
        if (!$name = $this->isCol($name)) :
            return [];
        endif;

        $col_var = "col_{$name}";
        if (!isset($this->{$col_var})) :
            return [];
        endif;

        return $this->{$col_var};
    }

    /**
     * Récupération d'un attribut de configuration de colonne
     *
     * @param string $name Identifiant de qualification de la colonne ou nom réel (préfixé) de la colonne
     * @param string $key Identifiant de quaalification de l'attribut
     * @param mixed $default Valeur de retour par défaut
     *
     * @return mixed
     */
    final public function getColAttr($name, $key, $default = '')
    {
        if(!$attrs =  $this->getColAttrs($name)) :
            return $default;
        endif;
        if (!isset($attrs[$key])) :
            return $default;
        endif;

        return $attrs[$key];
    }

    /**
     * Récupération des clés d'index
     *
     * @return array
     */
    final public function getIndexKeys()
    {
        return $this->IndexKeys;
    }

    /** == Vérifie si une variable de requête est une variable reservée au système == **/
    final public function isPrivateQueryVar($var)
    {
        return in_array($var, $this->PrivateQueryVars);
    }

    /** == Vérifie de l'existance d'une table de metadonnée en relation avec la table == **/
    final public function hasMeta()
    {
        return $this->MetaType ? true : false;
    }

    /** == Vérifie l'existance de colonnes ouvertes à la recherche == **/
    final public function hasSearch()
    {
        return ! empty($this->SearchColNames);
    }

    /**
     * Moteur de requête SQL
     *
     * @return \wpdb
     */
    public function sql()
    {
        return $this->SQLEngine;
    }

    /**
     * Installation de la base de données
     *
     * @return \wpdb
     */
    public function install()
    {
        return (new Make($this))->install();
    }

    /** == Traitement des éléments en base == **/
    public function handle()
    {
        return new Handle($this);
    }

    /** == Gestion des éléments de la base de metadonnées == **/
    public function meta()
    {
        return new Meta($this);
    }

    /** == Traitement des arguments de requête == **/
    public function parse()
    {
        return new Parse($this);
    }

    /** == == **/
    public function query($query = null)
    {
        return new Query($this, $query);
    }

    /** == Récupération d'élément de la base de données == **/
    public function select($query = null)
    {
        return new Select($this);
    }
}