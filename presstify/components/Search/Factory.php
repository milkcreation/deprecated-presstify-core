<?php
namespace tiFy\Components\Search;

use tiFy\Components\Search\Search;

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
     * Instance de requête join des metas
     * @var string
     */
    private $JoinMeta                   = 0;

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
        if(empty($this->Attrs['s'])) :
            $this->Attrs['s'] =  $WP_Query->get('s', '');
        endif;

        // Traitement des variables de requêtes
        $this->QueryVars = $this->_parseQueryVars($this->Attrs, $WP_Query);

        // Filtrages des conditions de requêtes
        add_filter('posts_search', [$this, 'posts_search'], 10, 2);
        add_filter('posts_clauses', [$this, 'posts_clauses'], 10, 2);

        // Empêche l'execution multiple du filtre
        \remove_filter(current_filter(), __METHOD__, 10);
    }

    /**
     * Filtrage des conditions de requêtes de recherche
     */
    final public function posts_search($search, $WP_Query)
    {
        // Empêche l'execution multiple du filtre
        \remove_filter(current_filter(), __METHOD__, 10);

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
     * @param \WP_Query
     *
     * @return array
     */
    final public function posts_clauses($clauses, $WP_Query)
    {
        global $wpdb;

        // Extraction des conditions de requête
        extract($clauses);

        $distinct = "DISTINCT";
        $where .= $this->_parseSearch($this->QueryVars, $WP_Query);

        if ($this->QueryVars['search_metas']) :
            for($i=1; $i <= $this->JoinMeta; ++$i) :
                $join .= " INNER JOIN {$wpdb->postmeta} as tfysmeta{$i} ON ({$wpdb->posts}.ID = tfysmeta{$i}.post_id)";
            endfor;
        endif;

        // Empêche l'execution multiple du filtre
        \remove_filter(current_filter(), __METHOD__, 10);

        return compact(array_keys($clauses));
    }

    /**
     * CONTROLEURS
     */
    /**
     * Traitement de la requête de recherche
     * @see \WP_Query::parse_search()
     *
     * @param array $q Variables de requête
     * @pram \WP_Query $WP_Query Instance de la classe de requête de Wordpress
     *
     * @return string
     */
    private function _parseSearch(&$q, $WP_Query)
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
                $search_parts[] = "({$wpdb->posts}.{$search_field} $like_op %s)";
                $search_parts_args[] = $like;
            endforeach;

            /**
             * Limitation de la recherche aux champs principaux définis
             */
            foreach ($q['search_metas'] as $search_meta) :
                if (!$this->JoinMeta) :
                    $this->JoinMeta++;
                elseif($andor_op === 'AND') :
                    $this->JoinMeta++;
                endif;

                $search_parts[] = "(tfysmeta{$this->JoinMeta}.meta_key = '{$search_meta}' AND tfysmeta{$this->JoinMeta}.meta_value $like_op %s)";
                $search_parts_args[] = $like;
            endforeach;

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
            $search = " AND ({$search}) ";
            if (! is_user_logged_in() ) :
                $search .= " AND ({$wpdb->posts}.post_password = '') ";
            endif;
        endif;

        return $search;
    }

    /**
     * Pré-Traitement des variables de requêtes
     *
     * @param mixed $attrs
     * @param \WP_Query $WP_Query
     *
     * @return $mixed
     */
    private function _parseQueryVars($attrs = [], &$WP_Query)
    {
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
        $WP_Query->query_vars = \wp_parse_args($QueryVars, $WP_Query->query_vars);

        return $WP_Query->query_vars;
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
        $instance->Attrs = $attrs;

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

}