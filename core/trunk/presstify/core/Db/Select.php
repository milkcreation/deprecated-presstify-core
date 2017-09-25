<?php

namespace tiFy\Core\Db;

use tiFy\Lib\StdClass;

class Select
{

    /**
     *
     * @var Factory
     */
    protected $Db;

    /**
     * CONSTRUCTEUR
     *
     * @param Factory $Db
     */
    public function __construct(Factory $Db)
    {
        $this->Db = $Db;
    }

    /* = COMPTE = */
    /** == Compte le nombre d'éléments selon une liste de critère == **/
    public function count($args = [])
    {
        $name        = $this->Db->Name;
        $primary_key = $this->Db->Primary;

        // Traitement des arguments de requête
        $defaults = [
            'item__not_in' => '',
            's'            => '',
            'limit'        => -1,
        ];

        // Traitement des arguments
        $parse = $this->Db->parse();
        $args  = $parse->query_vars($args, $defaults);

        // Traitement de la requête
        /// Selection de la table de base de données
        $query = "SELECT COUNT( {$name}.{$primary_key} ) FROM {$name}";

        // Conditions de jointure
        $query .= $parse->clause_join($args);

        /// Conditions définies par les arguments de requête
        if ($clause_where = $parse->clause_where($args)) :
            $query .= " " . $clause_where;
        endif;

        /// Recherche de terme
        if ($clause_search = $parse->clause_search($args['s'])) :
            $query .= " " . $clause_search;
        endif;

        /// Exclusions
        if ($clause__not_in = $parse->clause__not_in($args['item__not_in'])) :
            $query .= " " . $clause__not_in;
        endif;

        /// Groupe
        /*if( $clause_group_by = $parse->clause_group_by() )
            $query .= " ". $clause_group_by;*/

        //// Limite
        if ($args['limit'] > -1) :
            $query .= " LIMIT {$args['limit']}";
        endif;

        // Résultat
        return (int)$this->Db->sql()->get_var($query);
    }

    /* = VERIFICATION D'EXISTANCE = */
    /** == Vérification de l'existance de la valeur d'un cellule selon des critères == **/
    public function has($col_name = null, $value = '', $args = [])
    {
        $name        = $this->Db->Name;
        $primary_key = $this->Db->Primary;

        // Traitement de l'intitulé de la colonne
        if (is_null($col_name)) :
            $col_name = $primary_key;
        elseif (!$col_name = $this->Db->isCol($col_name)) :
            return null;
        endif;

        $args[$col_name] = $value;

        return $this->count($args);
    }

    /* = CELLULE = */
    /** == Récupération de l'id d'un élément selon des critères == **/
    public function id($args = [])
    {
        return $this->cell(null, $args);
    }

    /** == Récupération de la valeur d'un cellule selon des critères == **/
    public function cell($col_name = null, $args = [])
    {
        $name        = $this->Db->Name;
        $primary_key = $this->Db->Primary;

        // Traitement de l'intitulé de la colonne
        if (is_null($col_name)) :
            $col_name = $primary_key;
        elseif (!$col_name = $this->Db->isCol($col_name)) :
            return null;
        endif;

        // Traitement des arguments
        $defaults = [
            'item__in'     => '',
            'item__not_in' => '',
            's'            => '',
            'order'        => 'DESC',
            'orderby'      => $primary_key,
        ];

        // Traitement des arguments
        $parse = $this->Db->parse();
        $args  = $parse->query_vars($args, $defaults);

        // Traitement de la requête
        /// Selection de la table de base de données
        $query = "SELECT {$name}.{$col_name} FROM {$name}";

        /// Conditions de jointure
        $query .= $parse->clause_join($args);

        /// Conditions des arguments de requête
        if ($clause_where = $parse->clause_where($args)) :
            $query .= " " . $clause_where;
        endif;

        /// Recherche de terme
        if ($clause_search = $parse->clause_search($args['s'])) :
            $query .= " " . $clause_search;
        endif;

        /// Inclusions
        if ($clause__in = $parse->clause__in($args['item__in'])) :
            $query .= " " . $clause__in;
        endif;

        /// Exclusions
        if ($clause__not_in = $parse->clause__not_in($args['item__not_in'])) :
            $query .= " " . $clause__not_in;
        endif;

        /// Groupe
        if ($clause_group_by = $parse->clause_group_by()) :
            $query .= " " . $clause_group_by;
        endif;

        /*
        if( $item__in && ( $orderby === 'item__in' ) )
            $query .= " ORDER BY FIELD( {$this->wpdb_table}.{$this->primary_key}, $item__in )";
        else */
        if ($clause_order = $parse->clause_order($args['orderby'], $args['order'])) :
            $query .= $clause_order;
        endif;

        if ($var = $this->Db->sql()->get_var($query)) :
            return maybe_unserialize($var);
        endif;
    }

    /** == Récupération de la valeur d'un cellule selon son l'id de l'élément == **/
    public function cell_by_id($id, $col_name)
    {
        if ( ! $col_name = $this->Db->isCol($col_name)) :
            return null;
        endif;

        if (($item = wp_cache_get($id, $this->Db->Name)) && isset($item->{$col_name})) :
            return $item->{$col_name};
        else :
            return $this->cell($col_name, [$this->Db->Primary => $id]);
        endif;
    }

    /* = COLONNE = */
    /** == Récupération des valeurs d'une colonne de plusieurs éléments selon des critères == **/
    public function col($col_name = null, $args = [])
    {
        $name        = $this->Db->Name;
        $primary_key = $this->Db->Primary;

        // Traitement de l'intitulé de la colonne
        if (is_null($col_name)) :
            $col_name = $primary_key;
        elseif (!$col_name = $this->Db->isCol($col_name)) :
            return null;
        endif;

        // Traitement des arguments
        $parse = $this->Db->parse();
        $args  = $parse->query_vars($args);

        // Traitement de la requête
        /// Selection de la table de base de données
        $query = "SELECT {$name}.{$col_name} FROM {$name}";

        // Condition de jointure
        $query .= $parse->clause_join($args);

        /// Conditions des arguments de requête
        if ($clause_where = $parse->clause_where($args)) :
            $query .= " " . $clause_where;
        endif;

        /// Recherche de termes
        if ($clause_search = $parse->clause_search($args['s'])) :
            $query .= " " . $clause_search;
        endif;

        /// Inclusions
        if ($clause__in = $parse->clause__in($args['item__in'])) :
            $query .= " " . $clause__in;
        endif;

        /// Exclusions
        if ($clause__not_in = $parse->clause__not_in($args['item__not_in'])) :
            $query .= " " . $clause__not_in;
        endif;

        /// Groupe
        if ($clause_group_by = $parse->clause_group_by()) :
            $query .= " " . $clause_group_by;
        endif;

        /*
        /// Ordre
        if( $item__in && ( $orderby === 'item__in' ) )
            $query .= " ORDER BY FIELD( {$this->wpdb_table}.{$this->primary_key}, $item__in )";
        else */
        if ($clause_order = $parse->clause_order($args['orderby'], $args['order'])) :
            $query .= $clause_order;
        endif;

        /// Limite
        if ($args['per_page'] > 0) :
            if ( ! $args['paged']) :
                $args['paged'] = 1;
            endif;
            $offset = ($args['paged'] - 1) * $args['per_page'];
            $query  .= " LIMIT {$offset}, {$args['per_page']}";
        endif;

        // Resultats
        if ($res = $this->Db->sql()->get_col($query)) :
            return array_map('maybe_unserialize', $res);
        endif;
    }

    /** == Récupération des valeurs de la colonne id de plusieurs éléments selon des critères == **/
    public function col_ids($args = [])
    {
        return $this->col(null, $args);
    }

    /**
     * Retourne un tableau indexé sous la forme couple clé <> valeur
     *
     * @param string $value_col Colonne utilisée en tant que valeur du couple
     * @param string $key_col Colonne utilisée en tant que clé du couple
     * @param array $args Liste des arguments de requête
     * @param string $output Format de sortie
     *
     * @return null|array
     */
    public function pairs($value_col, $key_col = '', $args = [])
    {
        // Récupération de la colonne utilisée en tant que clé du couple
        if (!$key_col) :
            $key_col = $this->Db->Primary;
        endif;

        $args['fields'] = [$key_col, $value_col];

        // Traitement de la requête
        if(! $query = $this->Db->parse()->query($args)) :
            return;
        endif;

        // Récupération des resultats de requête
        if (!$items = $this->Db->sql()->get_results($query)) :
            return;
        endif;

        // Tratiement du resultat
        if (! $results = $this->Db->parse()->parse_output($items, OBJECT)) :
            return;
        endif;

        $pairs = [];
        foreach ($results as $row) :
            $pairs[$row->$key_col] = $row->$value_col;
        endforeach;

        return $pairs;
    }

    /* = LIGNE = */
    /** == Récupération des arguments d'un élément selon des critères == **/
    public function row($args = [], $output = OBJECT)
    {
        // Traitement des arguments
        $args['per_page'] = 1;

        // Bypass
        if ( ! $ids = $this->col_ids($args)) {
            return null;
        }
        $id = current($ids);

        return $this->row_by_id($id, $output);
    }

    /** == Récupération d'un élément selon un champ et sa valeur == **/
    public function row_by($col_name = null, $value, $output = OBJECT)
    {
        $name        = $this->Db->Name;
        $primary_key = $this->Db->Primary;

        // Traitement de l'intitulé de la colonne
        if (is_null($col_name)) :
            $col_name = $primary_key;
        elseif (! $col_name = $this->Db->isCol($col_name)) :
            return null;
        endif;

        $type = $this->Db->getColAttr($col_name, 'type');

        if (in_array($type, ['INT', 'BIGINT'])) :
            $query = "SELECT * FROM {$name} WHERE {$name}.{$col_name} = %d";
        else :
            $query = "SELECT * FROM {$name} WHERE {$name}.{$col_name} = %s";
        endif;

        if (! $item = $this->Db->sql()->get_row($this->Db->sql()->prepare($query,$value))) :
            return;
        endif;

        // Délinéarisation des tableaux
        $item = (object)array_map('maybe_unserialize', get_object_vars($item));

        // Mise en cache
        wp_cache_add($item->{$primary_key}, $item, $name);

        if ($output == OBJECT) :
            return ! empty($item) ? $item : null;
        elseif ($output == ARRAY_A) :
            return ! empty($item) ? get_object_vars($item) : null;
        elseif ($output == ARRAY_N) :
            return ! empty($item) ? array_values(get_object_vars($item)) : null;
        elseif (strtoupper($output) === OBJECT) :
            return ! empty($item) ? $item : null;
        endif;
    }

    /** == Récupération des arguments d'un élément selon son id == **/
    public function row_by_id($id, $output = OBJECT)
    {
        return $this->row_by(null, $id, $output);
    }

    /* = LIGNES = */
    /** == Récupération des arguments de plusieurs éléments selon des critères == **/
    public function rows($args = [], $output = OBJECT)
    {
        // Bypass
        if (! $ids = $this->col_ids($args)) :
            return;
        endif;

        $r = [];
        foreach ((array)$ids as $id) :
            $r[] = $this->row_by_id($id, $output);
        endforeach;

        return $r;
    }

    /**
     *
     */
    /** == Récupération de l'élément voisin selon un critère == **/
    public function adjacent($id, $previous = true, $args = [], $output = OBJECT)
    {
        $name        = $this->Db->Name;
        $primary_key = $this->Db->Primary;

        // Traitement des arguments
        $defaults = [
            'item__in'     => '',
            'item__not_in' => '',
            's'            => '',
        ];

        // Traitement des arguments
        $parse = $this->Db->parse();
        $args  = $parse->query_vars($args, $defaults);
        unset($args[$primary_key]);

        $op               = $previous ? '<' : '>';
        $args['order']    = $previous ? 'DESC' : 'ASC';
        $args['$orderby'] = $this->Db->primary_key;

        // Traitement de la requête
        /// Selection de la table de base de données
        $query = "SELECT * FROM {$name}";

        // Condition de jointure
        $query .= $parse->clause_join($args);

        /// Conditions definies par les arguments de requête
        if ($clause_where = $parse->clause_where($args)) :
            $query .= " " . $clause_where;
        endif;

        /// Conditions spécifiques
        $query .= " AND {$name}.{$primary_key} $op %d";

        /// Recherche de terme
        if ($clause_search = $parse->clause_search($args['s'])) :
            $query .= " " . $clause_search;
        endif;

        /// Inclusions
        if ($clause__in = $parse->clause__in($args['item__in'])) :
            $query .= " " . $clause__in;
        endif;

        /// Exclusions
        if ($clause__not_in = $parse->clause__not_in($args['item__not_in'])) :
            $query .= " " . $clause__not_in;
        endif;

        /// Groupe
        if ($clause_group_by = $parse->clause_group_by()) :
            $query .= " " . $clause_group_by;
        endif;

        /// Ordre
        if ($clause_order = $parse->clause_order($args['orderby'], $args['order'])) :
            $query .= $clause_order;
        endif;

        if ( ! $item = $this->Db->sql()->get_row($this->Db->sql()->prepare($query, $id))) :
            return;
        endif;

        // Délinéarisation des tableaux
        $item = (object)array_map('maybe_unserialize', get_object_vars($item));

        // Mise en cache
        wp_cache_add($item->{$primary_key}, $item, $name);

        if ($output == OBJECT) :
            return ! empty($item) ? $item : null;
        elseif ($output == ARRAY_A) :
            return ! empty($item) ? get_object_vars($item) : null;
        elseif ($output == ARRAY_N) :
            return ! empty($item) ? array_values(get_object_vars($item)) : null;
        elseif (strtoupper($output) === OBJECT) :
            return ! empty($item) ? $item : null;
        endif;
    }

    /* == Récupération de l'élément précédent == */
    public function previous($id, $args = [], $output = OBJECT)
    {
        return $this->adjacent($id, true, $args, $output);
    }

    /* == Récupération de l'élément suivant == */
    public function next($id, $args = [], $output = OBJECT)
    {
        return $this->adjacent($id, false, $args, $output);
    }
}
