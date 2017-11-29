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
     * Nombre de résultats trouvés
     * @var array
     */
    private $FoundPosts                 = [];

    /**
     * Liste des ids des post trouvés
     * @var array
     */
    private $PostIds                    = [];

    /**
     * Classe de rappel des requêtes Wordpress
     * @var \WP_Query $WP_Query
     */
    private $WP_Query                   = null;

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
        // Définition de la classe de rappel des requêtes Wordpress
        $this->WP_Query = &$WP_Query;

        // Bypass
        if (!$_tfysearch = $this->WP_Query->get('_tfysearch', '')) :
            return;
        endif;
        if($_tfysearch !== $this->getId()) :
            return;
        endif;

        // Définition du terme de recherche
        if(!$this->getAttr('s')) :
            $this->Attrs[0]['s'] =  $this->WP_Query->get('s', '');
        endif;

        // Initialisation du paramètre de recherche global parmis tous les types de post (exclusion de recherche omise)
        if ($this->hasGroup()) :
            $post_types = array_keys(get_post_types());
            $this->Attrs[0]['post_type'] = $post_types;
            $this->WP_Query->set('post_type', $post_types);
        endif;

        // Traitement des arguments de requête
        $this->WP_Query->query_vars = $this->_parseQueryVars(0);

        // Filtrages des conditions de requêtes
        add_filter('posts_search', [$this, 'posts_search'], 10, 2);
        add_filter('posts_clauses', [$this, 'posts_clauses'], 10, 2);

        // Empêcher l'execution multiple du filtre
        \remove_filter(current_filter(), [$this, current_filter()], 10);
    }

    /**
     * Filtrage des conditions de requêtes de recherche
     *
     * @param string $search Conditions de recherche
     * @param \WP_Query $WP_Query
     *
     * @return string
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
     * @param \WP_Query $WP_Query
     *
     * @return array
     */
    final public function posts_clauses($clauses, $WP_Query)
    {
        global $wpdb;

        // Définition de la classe de rappel des requêtes Wordpress
        $this->WP_Query = &$WP_Query;

        if (!$groups_attrs = $this->getGroupsAttrList()) :
            $attrs = $this->getAttrList();

            // Traitement des variables de requêtes
            $clauses = $this->_filterClauses($clauses, 0);
        else :
            $group_clauses = []; $group_query = []; $group_ids = '';

            foreach ($groups_attrs as $i => $group_attrs) :
                // Traitement des arguments de requête
                $query_vars = $this->_parseQueryVars($i);

                // Traitement des variables de requêtes
                $gc = $this->_filterClauses($clauses, $i);
                $group_clauses[] = $gc;

                if (!empty($gc['groupby'])) :
                    $gc['groupby'] = 'GROUP BY ' . $gc['groupby'];
                endif;
                if (!empty($gc['orderby'])) :
                    $gc['orderby'] = 'ORDER BY ' . $gc['orderby'];
                endif;

                // Récupération du compte de résultats trouvés
                if(!$this->FoundPosts[$i] = (int)$wpdb->get_var("SELECT COUNT(DISTINCT {$wpdb->posts}.ID) FROM {$wpdb->posts} {$gc['join']} WHERE 1=1 {$gc['where']}")) :
                    continue;
                endif;

                // Récupération de la liste des posts
                $this->PostIds[$i] =$wpdb->get_col("SELECT {$gc['distinct']} {$wpdb->posts}.ID FROM {$wpdb->posts} {$gc['join']} WHERE 1=1 {$gc['where']} {$gc['groupby']} {$gc['orderby']} {$gc['limits']}");
                $group_ids .= join(',', $this->PostIds[$i]);

                // Préparation de la requête
                $group_query[$i] = "({$wpdb->posts}.ID IN (". join(',', $this->PostIds[$i]) .") AND @tFySearchGroup:=if({$wpdb->posts}.ID, {$i}, 0))";
            endforeach;

            /**
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
            $orderby = "@tFySearchGroup ASC". ($group_ids ? ", FIELD({$wpdb->posts}.ID, {$group_ids})" : "");
            $distinct = "";
            $fields .= ", @tFySearchGroup as tFySearchGroup";
            $limits = "";

            $clauses = compact(array_keys($clauses));

            // Filtre de pré-requête des contenus - Définition de la variable MySQL de qualification du groupe
            add_filter('posts_pre_query', [$this, 'posts_pre_query'], 10, 2);
        endif;

        // Déclaration des événements de débug
        add_filter('posts_request', [$this, 'posts_request'], 10, 2);
        add_filter('the_posts', [$this, 'the_posts'], 10, 2);

        // Empêcher l'execution multiple du filtre
        \remove_filter(current_filter(), [$this, current_filter()], 10);

        return $clauses;
    }

    /**
     * DEBUG - Filtrage de la requête de récupération des contenus.
     *
     * @param string $request
     * @param \WP_Query $WP_Query
     *
     * @return null|\WP_Post[]
     */
    public function posts_request($request, $WP_Query)
    {
        //var_dump($request);
        //exit;

        // Empêcher l'execution multiple du filtre
        \remove_filter(current_filter(), [$this, current_filter()], 10);

        return $request;
    }

    /**
     * Préfiltrage de la liste des posts.
     *
     * @param \WP_Post[] $posts
     * @param \WP_Query $WP_Query
     *
     * @return null|\WP_Post[]
     */
    public function posts_pre_query($posts = null, $WP_Query)
    {
        global $wpdb;

        // Définition de la variable MySQL de qualification du groupe
        $wpdb->query("SET @tFySearchGroup:=0;");

        // Empêcher l'execution multiple du filtre
        \remove_filter(current_filter(), [$this, current_filter()], 10);

        return $posts;
    }

    /**
     * DEBUG - Filtrage de la liste des posts trouvés.
     *
     * @param \WP_Post[] $posts
     * @param \WP_Query $WP_Query
     *
     * @return null|\WP_Post[]
     */
    public function the_posts($posts, $WP_Query)
    {
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
     *
     * @return $mixed
     */
    private function _parseQueryVars($group = 0)
    {
        // Récuperation des attributs de configuration
        if (!$attrs = $this->getAttrList($group)) :
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
        foreach ($this->WP_Query->fill_query_vars($attrs) as $k => $v) :
            if (!isset($attrs[$k])) :
                continue;
            endif;
            $QueryVars[$k] = $v;
        endforeach;
        $QueryVars = \wp_parse_args($QueryVars, $this->WP_Query->query_vars);

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
     *
     * @return array
     */
    private function _filterClauses($clauses, $group = 0)
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

        if ($group) :
            // Traitement des conditions de requêtes induites par les taxonomies
            $this->WP_Query->parse_tax_query($this->QueryVars[$group]);
            $tax_clauses = $this->WP_Query->tax_query->get_sql($wpdb->posts, 'ID');
            $join .= $tax_clauses['join'];
            $where .= $tax_clauses['where'];
        endif;

        $where .= $this->_parseSearch($this->QueryVars[$group], $group);

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
     *
     * @return string
     */
    private function _parseSearch(&$q, $group = 0)
    {
        global $wpdb;

        $search = '';

        if (!empty($q['_s'])) :
            $q['s'] = stripslashes($q['_s']);

            if (empty($_GET['_s']) && $this->WP_Query->is_main_query()) :
                $q['s'] = urldecode($q['s']);
            endif;
        else :
            $q['s'] = stripslashes($q['s']);

            if (empty($_GET['s']) && $this->WP_Query->is_main_query()) :
                $q['s'] = urldecode($q['s']);
            endif;
        endif;

        $q['s'] = str_replace(["\r", "\n"], '', $q['s']);

        $q['search_terms_count'] = 1;
        if (!empty($q['sentence'])) :
            $q['search_terms'] = [$q['s']];
        else :
            if (preg_match_all('/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $q['s'], $matches)) :
                $q['search_terms_count'] = count( $matches[0] );
                $q['search_terms'] = $this->WP_Query->parse_search_terms($matches[0]);

                // if the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence
                if (empty($q['search_terms']) || count($q['search_terms']) > 9) :
                    $q['search_terms'] = [$q['s']];
                endif;
            else :
                $q['search_terms'] = [$q['s']];
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
                $search_parts[] = "(tfys_tms_g{$group}i{$this->JoinTax[$group]}.name {$like_op} %s)";
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
            if ($search_post_types = $this->_parseSearchPostTypes($q, $group)) :
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
     *
     * @return string
     */
    private function _parseSearchPostTypes(&$q, $group = 0)
    {
        global $wpdb;

        $where = "";
        $post_type = (isset($q['post_type'])) ?  $q['post_type'] : 'any';

        if ($post_type === $this->WP_Query->get('post_type')) :
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
     * Récupération de la liste des attributs de l'ensemble des groupes uniquement
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

    /**
     * Récupération du nombre de résultats trouvés pour une requête de recherche
     */
    final public function getFoundPosts($group = 0)
    {
        if (!$group) :
            return $this->WP_Query->found_posts;
        elseif (isset($this->FoundPosts[$group])) :
            return $this->FoundPosts[$group];
        endif;

        return 0;
    }

    /**
     * Traitement des attributs de configuration de la page des resultats de recherche
     */
    final protected function _parseSearchResultsAttrs($attrs, $defaults = [])
    {
        $_defaults = [
            'title'             => '',
            'no_results_found'  => __('Aucun résultat disponible, correspondant à vos critères de recherche.', 'tify'),
            'more_link'         => ''
        ];
        $defaults = empty($defaults) ? $_defaults : \wp_parse_args($defaults, $_defaults);

        $attrs['search_results'] = !isset($attrs['search_results']) ? $defaults : \wp_parse_args($attrs['search_results'], $defaults);

        return $attrs;
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

        // Traitement des attributs de configuration des groupes
        $groups_attrs = false;
        if (isset($attrs['groups'])) :
            $groups_attrs = $attrs['groups'];
            unset($attrs['groups']);
        endif;

        // Traitement des attributs de la page des resultats de recherche
        $attrs = $instance->_parseSearchResultsAttrs($attrs);
        $instance->Attrs[0] = $attrs;

        if ($groups_attrs) :
            foreach ($groups_attrs as $i => $group_attrs) :
                // Traitement des attributs de la page des resultats de recherche
                $group_attrs = $instance->_parseSearchResultsAttrs($group_attrs, $attrs['search_results']);

                $instance->Attrs[$i+1] = $group_attrs;
            endforeach;
        endif;

        // Déclaration d'événement de déclenchement
        add_action('pre_get_posts', [$instance, 'pre_get_posts'], 99);

        return $instance;
    }
}