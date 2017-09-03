<?php

namespace tiFy\Core\Db;

use tiFy\Core\Db\Make;
use tiFy\Core\Db\Handle;
use tiFy\Core\Db\Meta;
use tiFy\Core\Db\Parse;
use tiFy\Core\Db\Query;
use tiFy\Core\Db\Select;

class Factory
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
     * Liste des noms de colonne de la table
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

    //
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
        'per_page',
        'paged',
        'order',
        'orderby',
        'item_meta',
        'limit',
    ];

    // Classe de rappel
    public $Handle, $Meta, $Parse, $Query, $Select;

    // Liste des attributs accessibles
    public $GetAttrs = [
        'ID',
        'Name',
        'ColPrefix',
        'ColNames',
        'Primary',
        'IndexKeys',
        'SearchColNames',
        'MetaType',
    ];

    /**
     * CONSTRUCTEUR
     *
     * @param       $id     Identifiant unique de qualification de la table de
     *                      base de données
     * @param array $attrs  {
     *                      Attributs de la table de base de données
     *                      }
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
            /** @todo Dans le cas où la table n'ai pas pour nom de l'ID */
            'name'       => '',
            /** @todo Cas où la colonne de clé primaire n'est pas la première colonne */
            'primary'    => '',
            'version'    => 1,
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

        /// Définition du préfixe par défaut des noms de colonnes
        $this->setColPrefix($col_prefix);

        /// Traitement des attributs de colonnes
        foreach ((array)$columns as $col_name => $attrs) :
            $this->setColAttrs($col_name, $attrs);
        endforeach;

        /// Définition de la clé primaire
        $this->setPrimary();

        /// Définition des clés d'index
        $this->setIndexKeys($keys);

        /// Définition des colonnes ouvertes à la recherche de termes
        $this->setSearchColNames($search);

        /// Définition du nom de la table en base de données
        $this->setName($name);

        /// Définition de nom de la table de metadonnées en base
        $this->setMeta($meta);

        if ($install) {
            new Make($this);
        }
    }

    /* = PARAMETRAGE = */
    /** == Définition du prefixe des colonnes == **/
    private function setSQLEngine($sql_engine = null)
    {
        if (is_null($sql_engine) || ! ($sql_engine instanceof \wpdb)) :
            global $wpdb;

            return $this->SQLEngine = $wpdb;
        endif;

        return $this->SQLEngine = $sql_engine;
    }

    /** == Définition du prefixe des colonnes == **/
    private function setColPrefix($col_prefix = '')
    {
        return $this->ColPrefix = $col_prefix;
    }

    /** == Traitement des arguments de colonne == **/
    private function setColAttrs($col_name, $attrs = [])
    {
        $defaults = [
            'prefix' => true,
        ];
        $attrs    = wp_parse_args($attrs, $defaults);

        $_col_name = $attrs['prefix'] ? $this->ColPrefix . $col_name : $col_name;
        array_push($this->ColNames, $_col_name);
        $this->ColMap[$col_name] = $_col_name;

        $col = "col_{$_col_name}";

        return $this->{$col} = $attrs;
    }

    /** == Définition de la clé primaire == **/
    private function setPrimary()
    {
        // Bypass
        if (empty($this->ColNames)) {
            return;
        }

        reset($this->ColNames);

        return $this->Primary = $this->ColNames[0];
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

    /* = PARAMETRES = */
    /** == Récupération de la primaire == **/
    final public function getPrimary()
    {
        return $this->Primary;
    }

    /** == Récupération du nom de la table préfixée == **/
    final public function getName()
    {
        return $this->Name;
    }

    /** == Récupération d'un attribut de colonne selon son nom == **/
    final public function getColAttrs($col_name)
    {
        if ( ! $col_name = $this->isCol($col_name)) {
            return null;
        }
        $col = "col_{$col_name}";
        if (isset($this->{$col})) {
            ;
        }

        return $this->{$col};
    }

    /** == Récupération d'un attribut de colonne selon son nom == **/
    final public function getColAttr($col_name, $attr)
    {
        if (($column_attrs = $this->getColAttrs(
                $col_name
            )) && isset($column_attrs[$attr])) {
            ;
        }

        return $column_attrs[$attr];
    }

    /* = VERIFICATION DE DONNÉES = */
    /** == Vérification de l'existance d'une colonne == **/
    final public function isCol($col_name)
    {
        if ($this->isPrivateQueryVar($col_name)) {
            return false;
        } elseif (in_array($col_name, $this->ColNames)) {
            return $col_name;
        } elseif (in_array($this->ColPrefix . $col_name, $this->ColNames)) {
            return $this->ColPrefix . $col_name;
        }

        return false;
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

    /* = FONCTIONS DE RAPPELS = */
    public function sql()
    {
        return $this->SQLEngine;
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