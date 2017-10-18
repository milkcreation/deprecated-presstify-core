<?php 
namespace tiFy\Components\Search;

use tiFy\Core\CustomType\CustomType;

class Search extends \tiFy\App\Component
{
    /**
     * Types de post pour lequels les mots-clés de recherche sont activés
     * @var string[]
     */
    private static $TagsPostTypes           = [];

    /**
     * Listes des classe de rappel des requêtes de recherche
     * @var \tiFy\Components\Search\Factory[]
     */
    private static $Factory                 = [];

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation global
     */
    final public function init()
    {
        // Définition de la recherche globale
        if ($global_attrs = self::tFyAppConfig('global')) :
            self::register('global', $global_attrs);
        endif;

        do_action('tify_search_register');

        if ($st_pt = self::tFyAppConfig('search_tags_post_types')) :
            if (is_string($st_pt)) :
                $st_pt = array_map('trim', explode(',', $st_pt));
            endif;
            if(in_array('any', $st_pt)) :
                self::$TagsPostTypes = array_keys(get_post_types(['exclude_from_search' => false]));
            else :
                self::$TagsPostTypes = $st_pt;
            endif;
            if(!empty(self::$TagsPostTypes)) :
                CustomType::createTaxonomy(
                    'tify_search_tag',
                    [
                        'singular'      => __('mot-clef de recherche', 'tify'),
                        'plural'        => __('mots-clefs de recherche', 'tify'),
                        'object_type'   => self::$TagsPostTypes
                    ]
                );
            endif;
        endif;
    }

    /**
     * Personnalisation des variables de requête
     *
     * @param array $aVars Liste des variables de requête
     *
     * @return array
     */
    final public static function query_vars($aVars)
    {
        $aVars[] = '_tfysearch';
        $aVars[] = '_s';

        return $aVars;
    }

    /**
     * Pré-modifications de requête
     * Appelé après la création de l'object variable de requête mais avant que la requête courante ne soit lancée.
     * @see \WP_Query::get_posts()
     *
     * @param \WP_Query $WP_Query
     *
     * @return void
     */
    final public static function pre_get_posts(&$WP_Query)
    {
        // Bypass
        if ($_tfysearch = $WP_Query->get('_tfysearch', '')) :
            return;
        endif;
        if (!isset(self::$Factory['_global'])) :
            return;
        endif;
        if (!$WP_Query->is_main_query()) :
            return;
        endif;
        if (!$WP_Query->is_search()) :
            return;
        endif;

        $WP_Query->set('_tfysearch', '_global');

        // Empêche l'execution multiple du filtre
        \remove_filter(current_filter(), __METHOD__, 0);
    }

    /**
     * Gabarit d'affichage des résultats de recherche
     * @todo
     */
    final public function search_template($template, $type, $templates)
    {
        return $template;
        $this->tFyAppActionAdd('template_include', null, 99);
    }

    /**
     * @todo
     */
    final public static  function template_include($template)
    {
        return self::tFyAppQueryTemplate('search.php');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Initialisation
     */
    public function tFyAppOnInit()
    {
        // Définition des actions
        self::tFyAppActionAdd('pre_get_posts', null, 0);
        self::tFyAppActionAdd('init', null, 20);

        // Définition des filtres
        self::tFyAppFilterAdd('query_vars', null, 99);
        self::tFyAppFilterAdd('search_template', null, 10, 3);

        require self::tFyAppDirname() . '/Helpers.php';
    }

    /**
     * Déclaration de requête
     *
     * @var string $id Identifiant de qualification unique de la requête
     * @var array $attrs Attributs de configuration de la requête
     *
     * @return null|\tiFy\Components\Search\Factory
     */
    public static function register($id, $attrs = [])
    {
        // L'utilisation est reservé par le système
        if ($id === 'global') :
            $id = '_global';
        endif;

        // Bypass
        if (isset(self::$Factory[$id])) :
            return;
        endif;

        $Factory = self::getOverride('tiFy\Components\Search\Factory');

        self::$Factory[$id] = $Factory::_init($id, $attrs);
    }

    /**
     * Récupération de requête
     *
     * @var string $id Identifiant de qualification unique de la requête
     *
     * @return null|\tiFy\Components\Search\Factory
     */
    final public static function get($id)
    {
        if (isset(self::$Factory[$id])) :
            return self::$Factory[$id];
        endif;
    }
}