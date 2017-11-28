<?php
namespace tiFy\Core\Db;

class Make
{
    /**
     * Classe de rappel du constructeur de la table de base de données
     * @var \tiFy\Core\Db\Factory
     */
    protected $Db;

    /**
     * Indicateur d'initialisation
     * @var bool
     */
    private $Init = false;

    /**
     * CONSTRUCTEUR
     *
     * @param \tiFy\Core\Db\Factory $Db Classe de rappel du constructeur de la table de base de données
     *
     * @return void
     */
    public function __construct(\tiFy\Core\Db\Factory $Db)
    {
        $this->Db = $Db;

        if (did_action('init')) :
            add_action('init', [$this, 'init'], 100);
        else :
            $this->init();
        endif;
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        $this->install();
    }

    /**
     * CONTROLEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function install()
    {
        // Activation de l'indicateur d'initialisation
        if ($this->Init) :
            return;
        else :
            $this->Init = true;
        endif;

        $name = $this->Db->getName();
        $primary_key = $this->Db->getPrimary();

        // Bypass - La table de base de données existe
        if ($current_version = get_option('tify_db_' . $name, 0)) :
            return;
        endif;

        // Récupération de l'encodage collate des tables
        $charset_collate = $this->getCharsetCollate();

        // Requête de création de la table principale
        $create_ddl = "CREATE TABLE {$name} (";

        // Requêtes de création des colonnes de la table principale
        $_create_ddl = [];
        if ($names = $this->getDb()->getColNames()) :
            foreach ($names as $name) :
                $_create_ddl[] = $this->createColumn($name);
            endforeach;
        endif;

        $create_ddl .= implode(', ', $_create_ddl);

        $create_ddl .= $this->createKeys();
        if ($primary_key) :
            $create_ddl .= ", PRIMARY KEY ({$primary_key})";
        endif;
        $create_ddl .= ") $charset_collate;";

        // Création de la table principale
        if (!$result = $this->maybe_create_table($name, $create_ddl)) :
            return;
        endif;

        // Création de la table des metadonnées
        if ($this->Db->MetaType) :
            $table_name = $this->Db->meta()->getTableName();
            $join_col = $this->Db->meta()->getJoinCol();

            $create_ddl = "CREATE TABLE {$table_name} ( ";
            $create_ddl .= "meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT, ";
            $create_ddl .= "{$join_col} bigint(20) unsigned NOT NULL DEFAULT '0', ";
            $create_ddl .= "meta_key varchar(255) DEFAULT NULL, ";
            $create_ddl .= "meta_value longtext";
            $create_ddl .= ", PRIMARY KEY ( meta_id )";
            $create_ddl .= ", KEY {$join_col} ( {$join_col} )";
            $create_ddl .= ", KEY meta_key ( meta_key )";
            $create_ddl .= " ) $charset_collate;";

            $this->maybe_create_table($table_name, $create_ddl);
        endif;

        if ($result) :
            update_option('tify_db_' . $name, $this->Db->Version);
        endif;
    }

    /**
     * Récupération de la classe de rappel du constructeur de la table de base de données
     *
     * @return \tiFy\Core\Db\Factory
     */
    final public function getDb()
    {
        return $this->Db;
    }

    /**
     * Récupération de l'encodage de collation
     *
     * @return string
     */
    public function getCharsetCollate()
    {
        return $this->getDb()->sql()->get_charset_collate();
    }

    /**
     * Requête de création d'une colone
     *
     * @param $col_name
     *
     * @return string
     */
    private function createColumn($name)
    {
        $allowed_types = [
            // Numériques
            'tinyint',
            'smallint',
            'mediumint',
            'int',
            'bigint',
            'decimal',
            'float',
            'double',
            'real',
            'bit',
            'boolean',
            'serial',
            // Dates
            'date',
            'datetime',
            'timestamp',
            'time',
            'year',
            //Textes
            'char',
            'varchar',
            'tinytext',
            'text',
            'mediumtext',
            'longtext',
            'binary',
            'varbinary',
            'tinyblob',
            'mediumblob',
            'blob',
            'longblob',
            'enum',
            'set'
        ];

        $defaults = [
            'type'           => false,
            'size'           => false,
            'unsigned'       => false,
            'auto_increment' => false,
            'default'        => false
        ];

        if (!$attrs = $this->getDb()->getColAttrs($name)) :
            $attrs = [];
        endif;
        $_attrs = wp_parse_args($attrs, $defaults);

        /**
         * @var string $type
         * @var int $size Taille
         * @var bool $unsigned Limitation aux valeurs de nombres positifs
         * @var bool $auto_increment Activation de l'auto-incrémentation
         * @var null|bool $default Valeur par défaut
         */
        extract($_attrs, EXTR_SKIP);

        // Type de colonne (requis)
        $type = strtolower($type);
        if (!in_array($type, $allowed_types)) :
            return '';
        endif;

        // Colonne de clé primaire
        $is_primary = $this->getDb()->isPrimary($name);

        $create_ddl = "";
        $create_ddl .= "{$name} {$type}";

        // Taille
        if ($size) :
            $create_ddl .= "({$size})";
        endif;

        // Limitation aux valeurs de nombres positifs
        if ($is_primary && !isset($attrs['unsigned'])) :
            $unsigned = true;
        endif;
        if ($unsigned) :
            $create_ddl .= " UNSIGNED";
        endif;

        // Incrémentation automatique
        if ($is_primary && !isset($attrs['auto_increment'])) :
            $auto_increment = true;
        endif;
        if ($auto_increment) :
            $create_ddl .= " AUTO_INCREMENT";
        endif;

        // Valeur par défaut
        if (!is_null($default)) :
            if (is_numeric($default)) :
                $create_ddl .= " DEFAULT {$default} NOT NULL";
            elseif (is_string($default)) :
                $create_ddl .= " DEFAULT '{$default}' NOT NULL";
            else :
                $create_ddl .= " NOT NULL";
            endif;
        else :
            $create_ddl .= " DEFAULT NULL";
        endif;

        return $create_ddl;
    }

    /**
     * Création des clefs d'index
     *
     * @return string
     */
    private function createKeys()
    {
        if (!$index_keys = $this->getDb()->getIndexKeys()) :
            return '';
        endif;

        $create_ddl = [];
        foreach ($index_keys as $name => $attrs) :
            $cols = []; $type = '';

            // Traitement des attributs de configuration des clés d'index
            if (is_string($attrs)) :
                $cols = array_map('trim', explode(',', $attrs));
            elseif(is_array($attrs)) :
                if (isset($attrs['cols'])) :
                    if (is_string($attrs['cols'])) :
                        $cols = array_map('trim', explode(',', $attrs['cols']));
                    else :
                        $cols = $attrs['cols'];
                    endif;
                endif;
                if (isset($attrs['type']) && in_array($attrs['type'], ['UNIQUE', 'SPATIAL', 'FULLTEXT'])) :
                    $type = $attrs['type'] . ' ';
                endif;
            endif;

            $cols = array_map([$this->getDb(), 'isCol'], $cols);

            if (empty($cols)) :
                continue;
            endif;

            if (is_int($name)) :
                $name = join('_', $cols);
            endif;

            $_cols = implode(', ', $cols);
            array_push($create_ddl, "{$type}KEY {$name} ({$_cols})");
        endforeach;

        if (!empty($create_ddl)) :
            return ", " . implode(', ', $create_ddl);
        endif;

        return "";
    }

    /* = HELPERS = */
    /**
     * Create database table, if it doesn't already exist.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param string $table_name Database table name.
     * @param string $create_ddl Create database table SQL.
     *
     * @return bool False on error, true if already exists or success.
     */
    function maybe_create_table($table_name, $create_ddl)
    {
        foreach ($this->Db->sql()->get_col("SHOW TABLES", 0) as $table) :
            if ($table == $table_name) :
                return true;
            endif;
        endforeach;

        // Didn't find it, so try to create it.
        $this->Db->sql()->query($create_ddl);

        // We cannot directly tell that whether this succeeded!
        foreach ($this->Db->sql()->get_col("SHOW TABLES", 0) as $table) {
            if ($table == $table_name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add column to database table, if column doesn't already exist in table.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param string $table_name Database table name
     * @param string $column_name Table column name
     * @param string $create_ddl SQL to add column to table.
     *
     * @return bool False on failure. True, if already exists or was successful.
     */
    function maybe_add_column($table_name, $column_name, $create_ddl)
    {
        foreach ($this->Db->sql()->get_col("DESC $table_name", 0) as $column) :
            if ($column == $column_name) :
                return true;
            endif;
        endforeach;

        // Didn't find it, so try to create it.
        $this->Db->sql()->query($create_ddl);

        // We cannot directly tell that whether this succeeded!
        foreach ($this->Db->sql()->get_col("DESC $table_name", 0) as $column) :
            if ($column == $column_name) :
                return true;
            endif;
        endforeach;

        return false;
    }

    /**
     * Drop column from database table, if it exists.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param string $table_name Table name
     * @param string $column_name Column name
     * @param string $drop_ddl SQL statement to drop column.
     *
     * @return bool False on failure, true on success or doesn't exist.
     */
    function maybe_drop_column($table_name, $column_name, $drop_ddl)
    {
        foreach ($this->Db->sql()->get_col("DESC $table_name", 0) as $column) {
            if ($column == $column_name) {

                // Found it, so try to drop it.
                $this->Db->sql()->query($drop_ddl);

                // We cannot directly tell that whether this succeeded!
                foreach ($this->Db->sql()->get_col("DESC $table_name", 0) as $column) {
                    if ($column == $column_name) {
                        return false;
                    }
                }
            }
        }

        // Else didn't find it.
        return true;
    }

    /**
     * Check column matches criteria.
     *
     * Uses the SQL DESC for retrieving the table info for the column. It will help
     * understand the parameters, if you do more research on what column information
     * is returned by the SQL statement. Pass in null to skip checking that
     * criteria.
     *
     * Column names returned from DESC table are case sensitive and are listed:
     *      Field
     *      Type
     *      Null
     *      Key
     *      Default
     *      Extra
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param string $table_name Table name
     * @param string $col_name Column name
     * @param string $col_type Column type
     * @param bool $is_null Optional. Check is null.
     * @param mixed $key Optional. Key info.
     * @param mixed $default Optional. Default value.
     * @param mixed $extra Optional. Extra value.
     *
     * @return bool True, if matches. False, if not matching.
     */
    function check_column(
        $table_name,
        $col_name,
        $col_type,
        $is_null = null,
        $key = null,
        $default = null,
        $extra = null
    ) {
        $diffs = 0;
        $results = $this->Db->sql()->get_results("DESC $table_name");

        foreach ($results as $row) {

            if ($row->Field == $col_name) {

                // Got our column, check the params.
                if (($col_type != null) && ($row->Type != $col_type)) {
                    ++$diffs;
                }
                if (($is_null != null) && ($row->Null != $is_null)) {
                    ++$diffs;
                }
                if (($key != null) && ($row->Key != $key)) {
                    ++$diffs;
                }
                if (($default != null) && ($row->Default != $default)) {
                    ++$diffs;
                }
                if (($extra != null) && ($row->Extra != $extra)) {
                    ++$diffs;
                }
                if ($diffs > 0) {
                    return false;
                }

                return true;
            } // end if found our column
        }

        return false;
    }
}
