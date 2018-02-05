<?php
namespace tiFy\Core\CustomType;

use \tiFy\Core\Labels\Labels;

class CustomType extends \tiFy\App\Core
{
    /**
     * Liste des arguments de déclaration des taxonomies personnalisées
     */
    private static $Taxonomies         = array();

    /**
     * Liste des arguments de déclaration des types de post personnalisés
     */
    private static $PostTypes         = array();

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        // Traitement des types personnalisés passés en arguments
        // Taxonomie
        if (!empty(self::tFyAppConfig('taxonomy'))) :
            foreach ((array) self::tFyAppConfig('taxonomy') as $taxonomy => $args) :
                self::registerTaxonomy($taxonomy, $args);
            endforeach;
        endif;
        
        // Type de post
        if (!empty(self::tFyAppConfig('post_type'))) :
            foreach ((array) self::tFyAppConfig('post_type') as $post_type => $args) :
                self::registerPostType($post_type, $args);
            endforeach;
        endif;
            
        add_action('init', array($this, 'register_taxonomy'), 0);
        add_action('init', array($this, 'register_post_type'), 0);
        add_action('init', array($this, 'register_taxonomy_for_object_type'), 25);
        add_action('admin_init', array($this, 'create_initial_terms'), 10);
    }
    
    /**
     * DECLENCHEURS
     */
    /**
     * Déclaration des taxonomies personnalisées
     */
    final public function register_taxonomy()
    {
        do_action('tify_custom_taxonomy_register');

        foreach ((array)self::$Taxonomies as $taxonomy => $attrs) :
            self::createTaxonomy($taxonomy, $attrs);
        endforeach;
    }

    /**
     * Déclaration des types de posts personnalisés
     */
    final public function register_post_type()
    {
        do_action('tify_custom_post_type_register');

        foreach ((array)self::$PostTypes as $post_type => $attrs) :
            self::createPostType($post_type, $attrs);
        endforeach;
    }
    
    /**
     * Déclaration des taxonomies par type de post
     */
    final public function register_taxonomy_for_object_type()
    {
        if (!empty(self::$Taxonomies)) :
            foreach (self::$Taxonomies as $taxonomy => $args) :
                if (!isset($args['object_type'])) :
                    continue;
                endif;
                $post_types = !is_string($args['object_type']) ? $args['object_type'] : array_map('trim', explode(',', $args['object_type']));

                foreach ($post_types as $post_type) :
                    \register_taxonomy_for_object_type($taxonomy, $post_type);
                endforeach;
            endforeach;
        endif;

        foreach( (array) self::$PostTypes as $post_type => $args ) :
            if( ! isset( $args['taxonomies'] ) )
                continue;

            $taxonomies = ! is_string( $args['taxonomies'] ) ? $args['taxonomies'] : array_map( 'trim', explode( ',', $args['taxonomies'] ) );
            
            foreach( $taxonomies as $taxonomy ) :
                \register_taxonomy_for_object_type( $taxonomy, $post_type );
            endforeach;
        endforeach;
    }
    
    /**
     * Création des catégories de produits initiales
     */
    final public function create_initial_terms()
    {
        // Contrôle s'il s'agit d'une routine de sauvegarde automatique.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;
        // Contrôle s'il s'agit d'une execution de page via ajax.
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
            return;
        
        foreach( (array) self::$Taxonomies as $taxonomy => $args ) : 
            if( empty( $args['initial_terms'] ) )
                continue;
            $taxonomies = ! is_string( $args['initial_terms'] ) ? $args['initial_terms'] : array_map( 'trim', explode( ',', $args['initial_terms'] ) );                
                
            foreach( (array) $args['initial_terms'] as $terms ) :
                foreach( (array) $terms as $slug => $name ) :
                    if( ! $term = get_term_by( 'slug', $slug, $taxonomy ) ) :
                        wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
                    /*elseif( $term->name !== $name ) :
                        wp_update_term( $term->term_id, $taxonomy, array( 'name' => $name ) );*/
                    endif;
                endforeach;
            endforeach;
        endforeach;
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration de taxonomie personnalisée
     */
    public static function registerTaxonomy($taxonomy, $args = [])
    {
        if (!isset(self::$Taxonomies[$taxonomy])) :
            return self::$Taxonomies[$taxonomy] = $args;
        endif;
    }

    /**
     * Déclaration de type de post personnalisé
     */
    public static function registerPostType($post_type, $args = [])
    {

        if (!isset(self::$PostTypes[$post_type])) :
            return self::$PostTypes[$post_type] = $args;
        endif;
    }
    
    /**
     * Création de la taxonomie personnalisée
     */
    public static function createTaxonomy($taxonomy, $args = [])
    {
        // Déclaration des taxonomies non enregistrés
        if (!isset(self::$Taxonomies[$taxonomy])) :
            self::$Taxonomies[$taxonomy] = $args;
        endif;
        
        $args = self::parseTaxonomyAttrs( $taxonomy, $args );
                
        $allowed_args = array(
            'label', 'labels', 'public', 'show_ui', 'show_in_menu', 'show_in_nav_menus', 'show_tagcloud' , 'show_in_quick_edit', 
            'meta_box_cb', 'show_admin_column', 'description', 'hierarchical', 'query_var', 'rewrite', 'sort',
            'show_in_rest', 'rest_base', 'rest_controller_class'
        );
        foreach( $allowed_args as $allowed_arg ) :
            if( isset( $args[$allowed_arg] ) ) :
                $taxonomy_args[$allowed_arg] = $args[$allowed_arg];
            endif;
        endforeach;
        
        \register_taxonomy(
            $taxonomy,
            [],
            $taxonomy_args
        );       
    }    
    
    /**
     * Création du type de post personnalisé
     */
    public static function createPostType($post_type, $args = [])
    {
        // Déclaration des types de post non enregistrés
        if( ! isset( self::$PostTypes[$post_type] ) )
            self::$PostTypes[$post_type] = $args;
        
        $args = self::parsePostTypeAttrs( $post_type, $args );
        
        $allowed_args = array( 
            'label', 'labels', 'description', 'public', 'exclude_from_search', 'publicly_queryable', 'show_ui',
            'show_in_nav_menus', 'show_in_menu', 'show_in_admin_bar', 'menu_position', 'menu_icon', 'capability_type',
            'map_meta_cap', 'hierarchical', 'supports', 'register_meta_box_cb', /*'taxonomies',*/ 'has_archive',
            'permalink_epmask', 'rewrite', 'query_var', 'can_export', 'show_in_rest', 'rest_base', 'rest_controller_class'
        );

        foreach( $allowed_args as $allowed_arg ) :
            if( isset( $args[$allowed_arg] ) ) :
                $post_type_args[$allowed_arg] = $args[$allowed_arg];
             endif;
        endforeach;

        \register_post_type(
            $post_type, 
            $post_type_args 
        );
    }
    
    /**
     * Traitement des arguments par défaut de taxonomie personnalisée
     *
     * @param string $taxonomy Identifiant de qualification de la taxonomie
     * @param array $attrs Liste des attributs de configuration personnalisés
     *
     * @return array
     */
    private static function parseTaxonomyAttrs($taxonomy, $args = [])
    {
        // Traitement des arguments généraux
        $label = _x($taxonomy, 'taxonomy general name', 'tify');
        $plural = _x($taxonomy, 'taxonomy plural name', 'tify');
        $singular = _x($taxonomy, 'taxonomy singular name', 'tify');
        $gender = false;

        foreach (['gender', 'label', 'plural', 'singular'] as $attr) :
            if (isset($args[$attr])) :
                ${$attr} = $args[$attr];
                unset($args[$attr]);
            endif;
        endforeach;

        // Traitements des intitulés
        if (!isset($args['labels'])) :
            $args['labels'] = [];
        endif;

        $labels = Labels::register(
            '_tiFyCustomType-Taxonomy--' . $taxonomy,
            \wp_parse_args(
                $args['labels'],
                [
                    'singular' => $singular,
                    'plural' => $plural,
                    'gender' => $gender
                ]
            )
        );
        $args['labels'] = $labels->get();

        // Définition des valeurs par défaut
        $defaults['public'] = true;
        $defaults['show_ui'] = true;
        $defaults['show_in_menu'] = true;
        $defaults['show_in_nav_menus'] = false;
        $defaults['show_tagcloud'] = false;
        $defaults['show_in_quick_edit'] = false;
        $defaults['meta_box_cb'] = null;
        $defaults['show_admin_column'] = true;
        $defaults['description'] = '';
        $defaults['hierarchical'] = false;
        //$defaults['update_count_callback'] = '';
        $defaults['query_var'] = true;
        $defaults['rewrite'] = [
            'slug'         => $taxonomy,
            'with_front'   => false,
            'hierarchical' => false
        ];
        //$defaults['capabilities'] = '';
        $defaults['sort'] = true;

        return \wp_parse_args($args, $defaults);
    }

    /**
     * Traitement des arguments par défaut de type de post personnalisé
     * @see https://codex.wordpress.org/Function_Reference/register_post_type
     *
     * @param string $taxonomy Identifiant de qualification de la taxonomie
     * @param array $attrs Liste des attributs de configuration personnalisés
     *
     * @return array
     */
    private static function parsePostTypeAttrs( $post_type, $args = array() )
    {
        // Traitement des arguments généraux
        $label = _x($post_type, 'post type general name', 'tify');
        $plural = _x($post_type, 'post type plural name', 'tify');
        $singular = _x($post_type, 'post type singular name', 'tify');
        $gender = false;
        foreach (['gender', 'label', 'plural', 'singular'] as $attr) :
            if (isset($args[$attr])) :
                ${$attr} = $args[$attr];
                unset($args[$attr]);
            endif;
        endforeach;

        // Traitements des intitulés
        if (!isset($args['labels'])) :
            $args['labels'] = [];
        endif;

        $labels = Labels::register(
            '_tiFyCustomType-post--' . $post_type,
            \wp_parse_args(
                $args['labels'],
                [
                    'singular' => $singular,
                    'plural' => $plural,
                    'gender' => $gender
                ]
            )
        );
        $args['labels'] = $labels->get();

        // Définition des arguments du type de post
        /// Description
        $defaults['description'] = '';

        /// Autres arguments
        $defaults['public'] = true;
        $defaults['exclude_from_search'] = false;
        $defaults['publicly_queryable'] = true;
        $defaults['show_ui'] = true;
        $defaults['show_in_nav_menus'] = true;
        $defaults['show_in_menu'] = true;
        $defaults['show_in_admin_bar'] = true;
        $defaults['menu_position'] = null;
        $defaults['menu_icon'] = false;
        $defaults['capability_type'] = 'page';
        //$args['capabilities']            = array();
        $defaults['map_meta_cap'] = null;
        $defaults['hierarchical'] = false;
        $defaults['supports'] = ['title', 'editor', 'thumbnail'];
        $defaults['register_meta_box_cb'] = '';
        $defaults['taxonomies'] = [];
        $defaults['has_archive'] = true;
        $defaults['permalink_epmask'] = EP_PERMALINK;
        $defaults['rewrite'] = [
            'slug'       => $post_type,
            'with_front' => false,
            'feeds'      => true,
            'pages'      => true,
            'ep_mask'    => EP_PERMALINK
        ];
        $defaults['query_var'] = true;
        $defaults['can_export'] = true;
        $defaults['show_in_rest'] = true;
        $defaults['rest_base'] = $post_type;
        $defaults['rest_controller_class'] = 'WP_REST_Posts_Controller';

        $_args = \wp_parse_args($args, $defaults);

        if (!isset($args['publicly_queryable'])) :
            $_args['publicly_queryable'] = $_args['public'];
        endif;
        if (!isset($args['show_ui'])) :
            $_args['show_ui'] = $_args['public'];
        endif;
        if (!isset($args['show_in_nav_menus'])) :
            $_args['show_in_nav_menus'] = $_args['public'];
        endif;
        if (!isset($args['show_in_menu'])) :
            $_args['show_in_menu'] = $_args['show_ui'];
        endif;
        if (!isset($args['show_in_admin_bar'])) :
            $_args['show_in_admin_bar'] = $_args['show_in_menu'];
        endif;

        return $_args;
    }
}