<?php 
namespace tiFy\Components\Search;

use tiFy\Core\CustomType\CustomType;

class Search extends \tiFy\App\Component
{
    /**
     * Liste des actions à déclencher
     * @var string[]
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference
     */
    protected $tFyAppActions                = [
        'init'
    ];

    /**
     * Ordre de priorité d'exécution des actions
     * @var array
     */
    protected $tFyAppActionsPriority        = [
        'init'              => 20
    ];
    
    /**
     * Liste des Filtres à déclencher
     * @var string[]
     */
    protected $tFyAppFilters                = [
        'query_vars',
        //'search_template'
    ];
    
    /**
     * Ordre de priorité d'exécution des filtres
     * @var array
     */
    protected $tFyAppFiltersPriority        = [
        'query_vars'        => 99
    ];

    /**
     * Nombre d'arguments autorisés
     * @var array
     */
    protected $tFyAppFiltersArgs            = [
        'search_template'   => 3
    ];

    /**
     * Attributs de la configuration de recherche global
     * @var mixed
     */
    private static $GlobalSearchAttrs       = [];

    /**
     * Types de post pour lequels les mots-clés de recherche sont activés
     * @var
     */
    private static $TagPostTypes            = [];

    /**
     * Listes des classe de rappel des requêtes de recherche
     * @var \tiFy\Components\Search\Factory[]
     */
    private static $Factory                 = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        add_action('pre_get_posts', [$this,'pre_get_posts'], 0);
    }
    
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
        \remove_filter(current_filter(), __METHOD__, 10);
    }

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

        // Traitement des mots clefs de recherche
        // @todo
        /*foreach (self::$Section as $id => $attrs) :
            if (! isset($attrs['tags'])) :
                continue;
            endif;

            $post_types = isset($attrs['post_type']) ? $attrs['post_type'] : 'any';
            
            if ($post_types === 'any') :
                $post_types = array_keys(get_post_types(array('exclude_from_search' => false)));
            else :
                $post_types = (array)$post_types;
            endif;

            foreach ($post_types as $post_type) :
                array_push(self::$TagPostTypes, $post_type);
            endforeach;
        endforeach;

        if (self::$TagPostTypes) :
            CustomType::createTaxonomy(
                'tify_search_tag',
                [
                    'singular'      => __('mot-clef de recherche', 'tify'),
                    'plural'        => __('mots-clefs de recherche', 'tify'),
                    'object_type'   => self::$TagPostType
                ]
            );
        endif;
        */
    }
    
    /**
     * Personnalisation des variables de requête
     */
    final public function query_vars( $aVars ) 
    {
        $aVars[] = '_tfysearch';
        // $aVars[] = '_s';

        return $aVars;
    }

    /**
     * Gabarit d'affichage des résultats de recherche
     */
    public function search_template($template, $type, $templates)
    {
        add_action('template_include', [$this, 'template_include'], 99);
    }

    /**
     *
     */
    public function template_include($template)
    {
        return self::tFyAppQueryTemplate('search.php');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
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
     * Récupération
     */
    final public static function get($id)
    {
        if (isset(self::$Factory[$id])) :
            return self::$Factory[$id];
        endif;
    }
}