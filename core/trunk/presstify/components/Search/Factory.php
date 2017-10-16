<?php
namespace tiFy\Components\Search;

class Factory extends \tiFy\App\Factory
{
    /**
     * Identifiant unique de qualification de la requête de recherche
     * @var string
     */
    private $Id                         = '';

    /**
     * Liste des attributs de configuration de la requête de recherche
     * @var mixed
     */
    private $Attrs                      = [];

    /**
     * Liste des variables de requête
     * @var array
     */
    private $QueryVars                  = [];

    /**
     * Liste des variables de requêtes dédiées
     * @var string
     */
    private static $DedicatedQueryVars    = [
        'search_fields', 'search_metas', 'search_tags'
    ];

    /**
     * Instance de requête join des metadonnées
     * @var array
     */
    private $JoinMeta                   = [];

    /**
     * Instance de requête join des taxonomies
     * @var string
     */
    private $JoinTax                    = [];

    /**
     * DECLENCHEURS
     */
    /**
     * Pré-modifications de requête
     * Appelé après la création de l'object variable de requête mais avant que la requête courante ne soit lancée.
     * @see \WP_Query::get_posts()
     *
     * @param \WP_Query $WP_Query
     *
     * @return void
     */
    final public function pre_get_posts(&$WP_Query)
    {
        // Bypass
        if (!$_tfysearch = $WP_Query->get('_tfysearch', '')) :
            return;
        endif;
        if($_tfysearch !== $this->getId()) :
            return;
        endif;

        // Définition du terme de recherche
        if(!$this->getAttr('s')) :
            $this->Attrs[0]['s'] =  $WP_Query->get('s', '');
        endif;

        // Initialisation du paramètre de recherche global parmis tous les types de post (exclusion de recherche omise)
        if ($this->hasGroup()) :
            $post_types = array_keys(get_post_types());
            $this->Attrs[0]['post_type'] = $post_types;
            $WP_Query->set('post_type', $post_types);
        endif;

        // Traitement des arguments de requête
        $WP_Query->query_vars = $this->_parseQueryVars(0, $WP_Query);

        // Filtrages des conditions de requêtes
        self::tFyAppFilterAdd('posts_search', null, 10, 2);
        self::tFyAppFilterAdd('posts_clauses', null, 10, 2);

        // Empêcher l'execution multiple du filtre
        \remove_filter(current_filter(), [$this, current_filter()], 10);
    }

    /**
     * Filtrage des conditions de requêtes de recherche
     */
    final public function posts_search($search, $WP_Query)
    {
        // Empêcher l'execution multiple du filtre
        \remove_filter(current_filter(), [$this, current_filter()], 10);

        // Suppression des conditions de recherche originales
        return '';
    }

    /**
     * Personnalisation des conditions de requêtes
     *
     * @param array $clauses {
     *      Liste des conditions de requêtes
     *
     *      @var string $where
     *      @var string $groupby
     *      @var string $join
     *      @var string $orderby
     *      @var string $distinct
     *      @var string $fields
     *      @var string $limits
     * }
     * @param WP_Query $WP_Query
     *
     * @return array
     */
    final public function posts_clauses($clauses, &$WP_Query)
    {
        global $wpdb;

        if (!$groups_attrs = $this->getGroupsAttrList()) :
            $attrs = $this->getAttrList();

            // Traitement des variables de requêtes
            $clauses = $this->_filterClauses($clauses, 0, $WP_Query);
        else :
            $group_clauses = []; $group_query = [];

            foreach ($groups_attrs as $i => $group_attrs) :
                // Traitement des arguments de requête
                $this->_parseQueryVars($i, $WP_Query);

                // Traitement des variables de requêtes
                $gc = $this->_filterClauses($clauses, $i, $WP_Query);
                $group_clauses[] = $gc;

                if (!empty($gc['groupby'])) :
                    $gc['groupby'] = 'GROUP BY ' . $gc['groupby'];
                endif;
                if (!empty($gc['orderby'])) :
                    $gc['orderby'] = 'ORDER BY ' . $gc['orderby'];
                endif;

                // Préparation de la requête
                $group_query[] = "({$wpdb->posts}.ID IN (SELECT * FROM(SELECT {$gc['distinct']} {$wpdb->posts}.ID FROM {$wpdb->posts} {$gc['join']} WHERE 1=1 {$gc['where']} {$gc['groupby']} {$gc['orderby']} {$gc['limits']}) as tFySearchGroupQuery{$i}) AND @tFySearchGroup:=if({$wpdb->posts}.ID, {$i}, 0))";
            endforeach;

            /*
             * DEBUG
            $wpdb->query("SET @tFySearchGroup:=0;");
            $query = "SELECT {$wpdb->posts}.*, @tFySearchGroup as tFySearchGroup FROM {$wpdb->posts} WHERE 1";
            $query .= " AND (". join(" OR ", $group_query) . ")";
            $r = $wpdb->get_results($query);
            var_dump($r);
            */

            /**
             * Extraction des conditions de requête
             * @var string $where
             * @var string $groupby
             * @var string $join
             * @var string $orderby
             * @var string $distinct
             * @var string $fields
             * @var string $limits
             */
            extract($clauses);

            $where = " AND (". join(" OR ", $group_query) . ")";
            $groupby = "";
            $join = "";
            $orderby = "@tFySearchGroup ASC";
            $distinct = "";
            $fields .= ", @tFySearchGroup as tFySearchGroup";
            $limits = "";

            $clauses = compact(array_keys($clauses));

            // Filtre de pré-requête des contenus - Définition de la variable MySQL de qualification du groupe
            self::tFyAppFilterAdd('posts_pre_query', null, 10, 2);
        endif;

        // Empêcher l'execution multiple du filtre
        \remove_filter(current_filter(), [$this, current_filter()], 10);

        return $clauses;
    }

    /**
     * Pré requête de récupération des contenus
     *
     * @param \WP_Post[] $posts
     * @param WP_Query $WP_Query
     *
     * @return null|\WP_Post[]
     */
    public function posts_pre_query($posts = null, &$WP_Query)
    {
        global $wpdb;

        // Définition de la variable MySQL de qualification du groupe
        $wpdb->query("SET @tFySearchGroup:=0;");

        // Empêcher l'execution multiple du filtre
        \remove_filter(current_filter(), [$this, current_filter()], 10);

        return $posts;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Pré-Traitement des variables de requêtes
     *
     * @param int $group Index d'identification du groupe
     * @param \WP_Query $WP_Query
     *
     * @return $mixed
     */
    private function _parseQueryVars($group = 0, $WP_Query)
    {
        // Récuperation des attributs de configuration
        if (! $attrs = $this->getAttrList($group)) :
            $attrs = [];
        endif;
        $QueryVars = [];

        // Traitement des variables dédiées
        foreach ($attrs as $key => $value) :
            if (!in_array($key, self::$DedicatedQueryVars)) :
                continue;
            endif;
            $QueryVars[$key] = $value;
        endforeach;

        $defaults = [
            'search_fields'     => ['post_title','post_excerpt','post_content'],
            'search_metas'      => [],
            'search_tags'       => false
        ];
        $QueryVars = \wp_parse_args($QueryVars, $defaults);

        /**
         * Traitement des variables natives de WP_Query
         * @see \WP_Query::fill_query_vars()
         */
        foreach ($WP_Query->fill_query_vars($attrs) as $k => $v) :
            if (!isset($attrs[$k])) :
                continue;
            endif;
            $QueryVars[$k] = $v;
        endforeach;
        $QueryVars = \wp_parse_args($QueryVars, $WP_Query->query_vars);

        return $this->QueryVars[$group] = $QueryVars;
    }

    /**
     * Filtrage des conditions de requête
     *
     * @param array $clauses {
     *  Liste des conditions de requête
     *
     *  @var string $where
     *  @var string $groupby
     *  @var string $join
     *  @var string $orderby
     *  @var string $distinct
     *  @var string $fields
     *  @var string $limits
     * }
     * @param int $group Index d'identification du groupe
     * @param $WP_Query
     */
    private function _filterClauses($clauses, $group = 0, $WP_Query)
    {
        global $wpdb;

        /**
         * Extraction des conditions de requête
         * @var string $where
         * @var string $groupby
         * @var string $join
         * @var string $orderby
         * @var string $distinct
         * @var string $fields
         * @var string $limits
         */
        extract($clauses);

        $where .= $this->_parseSearch($this->QueryVars[$group], $group, $WP_Query);

        if (! empty($this->JoinMeta[$group])) :
            foreach($this->JoinMeta[$group] as $i => $meta_key) :
                $join .= " LEFT OUTER JOIN {$wpdb->postmeta} as tfys_meta_g{$group}i{$i} ON ({$wpdb->posts}.ID = tfys_meta_g{$group}i{$i}.post_id AND tfys_meta_g{$group}i{$i}.meta_key = '{$meta_key}')";
            endforeach;
        endif;

        if (! empty($this->JoinTax[$group])) :
            $i = 1;
            $join .= " LEFT OUTER JOIN {$wpdb->term_relationships} AS tfys_tmr_g{$group}i{$i} ON ({$wpdb->posts}.ID = tfys_tmr_g{$group}i{$i}.object_id)";
            $join .= " LEFT OUTER JOIN {$wpdb->term_taxonomy} AS tfys_tmt_g{$group}i{$i} ON (tfys_tmr_g{$group}i{$i}.term_taxonomy_id = tfys_tmt_g{$group}i{$i}.term_taxonomy_id  AND tfys_tmt_g{$group}i{$i}.taxonomy = 'tify_search_tag')";
            $join .= " LEFT OUTER JOIN {$wpdb->terms} AS tfys_tms_g{$group}i{$i} ON (tfys_tmt_g{$group}i{$i}.term_id = tfys_tms_g{$group}i{$i}.term_id)";
        endif;

        if ($this->QueryVars[$group]['search_metas'] || $this->QueryVars[$group]['search_tags']) :
            $groupby = "{$wpdb->posts}.ID";
        endif;

        return compact(array_keys($clauses));
    }

    /**
     * Traitement de la requête de recherche
     * @see \WP_Query::parse_search()
     *
     * @param array $q Variables de requête
     * @param int $group Index d'identification du groupe
     * @param \WP_Query $WP_Query Instance de la classe de requête de Wordpress
     *
     * @return string
     */
    private function _parseSearch(&$q, $group = 0, $WP_Query)
    {
        global $wpdb;

        $search = '';

        // added slashes screw with quote grouping when done early, so done later
        $q['s'] = stripslashes($q['s']);

        if (empty($_GET['s']) && $WP_Query->is_main_query()) :
            $q['s'] = urldecode( $q['s'] );
        endif;

        // there are no line breaks in <input /> fields
        $q['s'] = str_replace(["\r", "\n"], '', $q['s']);

        $q['search_terms_count'] = 1;
        if (!empty($q['sentence'])) :
            $q['search_terms'] = array( $q['s'] );
        else :
            if (preg_match_all('/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $q['s'], $matches)) :
                $q['search_terms_count'] = count( $matches[0] );
                $q['search_terms'] = $WP_Query->parse_search_terms($matches[0]);

                // if the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence
                if (empty($q['search_terms']) || count($q['search_terms']) > 9) :
                    $q['search_terms'] = array( $q['s'] );
                endif;
            else :
                $q['search_terms'] = array( $q['s'] );
            endif;
        endif;

        $n = !empty($q['exact']) ? '' : '%';
        $searchand = '';
        $q['search_orderby_title'] = [];

        /**
         * Filters the prefix that indicates that a search term should be excluded from results.
         *
         * @since 4.7.0
         *
         * @param string $exclusion_prefix The prefix. Default '-'. Returning
         *                                 an empty value disables exclusions.
         */
        $exclusion_prefix = apply_filters('wp_query_search_exclusion_prefix', '-');

        foreach ($q['search_terms'] as $term) :
            // If there is an $exclusion_prefix, terms prefixed with it should be excluded.
            $exclude = $exclusion_prefix && ($exclusion_prefix === substr($term, 0, 1));

            if ($exclude) :
                $like_op  = 'NOT LIKE';
                $andor_op = 'AND';
                $term     = substr($term, 1);
            else :
                $like_op  = 'LIKE';
                $andor_op = 'OR';
            endif;

            if ($n && ! $exclude) :
                $like = '%' . $wpdb->esc_like( $term ) . '%';
                $q['search_orderby_title'][] = $wpdb->prepare("{$wpdb->posts}.post_title LIKE %s", $like);
            endif;

            $like = $n . $wpdb->esc_like( $term ) . $n;

            /**
             * Limitation de la recherche
             */
            $search_parts = []; $search_parts_args = [];
            /**
             * Limitation de la recherche aux champs principaux définis
             */
            foreach ($q['search_fields'] as $search_field) :
                $search_parts[] = "({$wpdb->posts}.{$search_field} {$like_op} %s)";
                $search_parts_args[] = $like;
            endforeach;

            /**
             * Recherche parmis les metadonnées définies
             */
            foreach ($q['search_metas'] as $i => $search_meta) :
                $this->JoinMeta[$group][$i] = $search_meta;

                $search_parts[] = "(tfys_meta_g{$group}i{$i}.meta_value {$like_op} %s)";
                $search_parts_args[] = $like;
            endforeach;

            /**
             * Recherche parmis les mots-clefs de recherche
             */
            if ($q['search_tags']) :
                $this->JoinTax[$group] = 1;
                $search_parts[] = "(tfys_tms_g{$group}i{$this->JoinTax}.name {$like_op} %s)";
                $search_parts_args[] = $like;
            endif;

            if ($search_parts) :
                $_search_parts = implode(" {$andor_op} ", $search_parts);
                array_unshift($search_parts_args, $_search_parts);
                $search .= call_user_func_array([$wpdb, 'prepare'], $search_parts_args);
            endif;

            if ($search) :
                $search = "{$searchand}({$search})";
            endif;
            $searchand = ' AND ';
        endforeach;

        if (! empty($search)) :
            $search = " AND ({$search})";
            if ($search_post_types = $this->_parseSearchPostTypes($q, $group, $WP_Query)) :
                $search .= $search_post_types;
            endif;
            if (! is_user_logged_in() ) :
                $search .= " AND ({$wpdb->posts}.post_password = '') ";
            endif;
        endif;

        return $search;
    }

    /**
     * Traitement des types de post de la requête de recherche
     *
     * @param array $q Variables de requête
     * @param int $group Index d'identification du groupe
     * @param \WP_Query $WP_Query Instance de la classe de requête de Wordpress
     *
     * @return string
     */
    private function _parseSearchPostTypes(&$q, $group = 0, $WP_Query)
    {
        global $wpdb;

        $where = "";
        $post_type = (isset($q['post_type'])) ?  $q['post_type'] : 'any';

        if ($post_type === $WP_Query->get('post_type')) :
            return $where;
        endif;

        if ('any' == $post_type) :
            $in_search_post_types = get_post_types(['exclude_from_search' => false]);
            if (empty($in_search_post_types)) :
                $where .= " AND 1=0 ";
            else :
                $where .= " AND {$wpdb->posts}.post_type IN ('" . join("', '", array_map('esc_sql', $in_search_post_types)) . "')";
            endif;
        elseif (!empty($post_type) && is_array($post_type)) :
            $where .= " AND {$wpdb->posts}.post_type IN ('" . join("', '", esc_sql($post_type)) . "')";
        else :
            $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_type = %s", $post_type);
        endif;

        return $where;
    }

    /**
     * Initialisation
     */
    final public static function _init($id, $attrs = [])
    {
        if ($instance = Search::get($id)) :
            return;
        endif;

        // Instanciation de la classe
        $instance = new static();
        $instance->Id = $id;

        // Traitement des attributs de configuration
        $groups_attrs = false;
        if (isset($attrs['groups'])) :
            $groups_attrs = $attrs['groups'];
            unset($attrs['groups']);
        endif;
        $instance->Attrs[0] = $attrs;

        if ($groups_attrs) :
            foreach ($groups_attrs as $i => $group_attrs) :
                $instance->Attrs[$i+1] = $group_attrs;
            endforeach;
        endif;

        // Déclaration d'événement de déclenchement
        add_action('pre_get_posts', [$instance, 'pre_get_posts'], 99);

        return $instance;
    }

    /**
     * Récupération de l'identifiant unique de la classe de requête de recherche
     *
     * @return string
     */
    final public function getId()
    {
        return $this->Id;
    }

    /**
     * Récupération de la liste des attributs
     *
     * @param int $group Groupe des attributs
     *
     * @return null|array
     */
    final public function getAttrList($group = 0)
    {
        if (isset($this->Attrs[$group])) :
            return $this->Attrs[$group];
        endif;
    }

    /**
     * Récupération d'un attribut
     *
     * @param string $name
     * @param mixed $default Valeur par défaut de l'attribut
     * @param int $group Groupe de l'attribut
     *
     * @return array
     */
    final public function getAttr($name, $default = '', $group = 0)
    {
        if (!$attrs = $this->getAttrList($group)) :
            return $default;
        endif;

        if (isset($attrs[$name])) :
            return $attrs[$name];
        endif;

        return $default;
    }

    /**
     * Vérification d'existance de resultat de recherche groupés
     *
     * @return bool
     */
    final public function hasGroup()
    {
        return count($this->Attrs) > 1;
    }

    /**
     * Récupération de la liste des attributs des groupes
     *
     * @return null|array
     */
    final public function getGroupsAttrList()
    {
        if (!$this->hasGroup()) :
            return;
        endif;

        $attrs = $this->Attrs;
        unset($attrs[0]);

        return $attrs;
    }
}