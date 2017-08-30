<?php
namespace tiFy\Core\CustomType;

class CustomType extends \tiFy\Environment\Core
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
     */
    public function __construct()
    {
        parent::__construct();
        
        // Traitement des types personnalisés passés en arguments
        // Taxonomie
        foreach( (array) self::tFyAppConfig( 'taxonomy' ) as $taxonomy => $args ) :
            self::registerTaxonomy( $taxonomy, $args );
        endforeach;
        
        // Type de post        
        foreach( (array) self::tFyAppConfig( 'post_type' ) as $post_type => $args )
            self::registerPostType( $post_type, $args );        
            
        add_action( 'init', array( $this, 'register_taxonomy' ), 0 );
        add_action( 'init', array( $this, 'register_post_type' ), 0 );
        add_action( 'init', array( $this, 'register_taxonomy_for_object_type' ), 25 );
        add_action( 'admin_init', array( $this, 'create_initial_terms' ) );
    }
    
    /**
     * DECLENCHEURS
     */
    /**
     * Déclaration des taxonomies personnalisées
     */
    final public function register_taxonomy()
    {        
        do_action( 'tify_custom_taxonomy_register' );

        foreach( (array) self::$Taxonomies as $taxonomy => $attrs ) :
            self::createTaxonomy( $taxonomy, $attrs );
        endforeach;
    }

    /**
     * Déclaration des types de posts personnalisés
     */
    final public function register_post_type()
    {
        do_action( 'tify_custom_post_type_register' );

        foreach( (array) self::$PostTypes as $post_type => $attrs ) :
           self::createPostType( $post_type, $attrs );
        endforeach;
    }
    
    /**
     * Déclaration des taxonomies par type de post
     */
    final public function register_taxonomy_for_object_type()
    {        
        foreach( (array) self::$Taxonomies as $taxonomy => $args ) :            
            if( ! isset( $args['object_type'] ) )
                continue;                  
            $post_types = ! is_string( $args['object_type'] ) ? $args['object_type'] : array_map( 'trim', explode( ',', $args['object_type'] ) );
            
            foreach( $post_types as $post_type ) :
                \register_taxonomy_for_object_type( $taxonomy, $post_type );
            endforeach;
        endforeach;
        
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
    public static function registerTaxonomy( $taxonomy, $args )
    {
        if( ! isset( self::$Taxonomies[$taxonomy] ) )
            self::$Taxonomies[$taxonomy] = $args;
    }
    
    /**
     * Déclaration de type de post personnalisé
     */
    public static function registerPostType( $post_type, $args )
    {
        if( ! isset( self::$PostTypes[$post_type] ) )
            self::$PostTypes[$post_type] = $args;
    }
    
    /**
     * Création de la taxonomie personnalisée
     */
    public static function createTaxonomy( $taxonomy, $args )
    {
        // Déclaration des taxonomies non enregistrés
        if( ! isset( self::$Taxonomies[$taxonomy] ) )
            self::$Taxonomies[$taxonomy] = $args;
        
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
            array(),
            $taxonomy_args
        );       
    }    
    
    /**
     * Création du type de post personnalisé
     */
    public static function createPostType( $post_type, $args )
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
     * Traitements des arguments par défaut de taxonomie personnalisée
     */
    private static function parseTaxonomyAttrs( $taxonomy, $args = array() )
    {
        // Traitement des arguments généraux
        $label      = _x( $taxonomy, 'taxonomy general name', 'tify' );
        $plural     = _x( $taxonomy, 'taxonomy plural name', 'tify' );
        $singular   = _x( $taxonomy, 'taxonomy singular name', 'tify' );
        $gender     = false; 
        foreach( array( 'gender', 'label', 'plural', 'singular' ) as $attr ) :
            if ( isset( $args[$attr] ) ) :
                ${$attr} = $args[$attr];
                unset( $args[$attr] );
            endif;
        endforeach;
        
        if( ! isset( $args['labels'] ) )
            $args['labels'] = array();    
        $labels = new \tiFy\Core\Labels\Factory( wp_parse_args( $args['labels'], array( 'singular' => $singular, 'plural' => $plural, 'gender' => $gender ) ) );
        $args['labels'] = $labels->Get();
        
        $defaults['public']                 = true;
        $defaults['show_ui']                 = true;
        $defaults['show_in_menu']             = true;
        $defaults['show_in_nav_menus']         = false;
        $defaults['show_tagcloud']             = false;
        $defaults['show_in_quick_edit']     = false;
        $defaults['meta_box_cb']             = null;
        $defaults['show_admin_column']         = true;
        $defaults['description']             = '';
        $defaults['hierarchical']             = false;
        //$defaults['update_count_callback'] = '';
        $defaults['query_var']                 = true;
        $defaults['rewrite']                 = array(
            'slug'             => $taxonomy, 
            'with_front'    => false, 
            'hierarchical'     => false        
        );
        //$defaults['capabilities'] = '';
        $defaults['sort']     = true;
        
        return wp_parse_args( $args, $defaults );
    }
    
    /**
     * Traitement des arguments par défaut de type de post personnalisé
     */
    private static function parsePostTypeAttrs( $post_type, $args = array() )
    {
        // Traitement des arguments généraux
        /// Intitulés
        $label      = _x( $post_type, 'post type general name', 'tify' );
        $plural     = _x( $post_type, 'post type plural name', 'tify' );
        $singular   = _x( $post_type, 'post type singular name', 'tify' );
        $gender     = false; 
        foreach( array( 'gender', 'label', 'plural', 'singular' ) as $attr ) :
            if ( isset( $args[$attr] ) ) :
                ${$attr} = $args[$attr];
                unset( $args[$attr] );
            endif;
        endforeach;
        
        if( ! isset( $args['labels'] ) )
            $args['labels'] = array();    
        
        $labels = new \tiFy\Core\Labels\Factory( wp_parse_args( $args['labels'], array( 'singular' => $singular, 'plural' => $plural, 'gender' => $gender ) ) );
        
        $args['labels'] = $labels->Get();
        
        // Définition des arguments du type de post
        /// Description
        $defaults['description'] = '';
        
        /// Autres arguments
        $defaults['public']                 = true;
        $defaults['exclude_from_search']    = false;
        $defaults['publicly_queryable']     = true;
        $defaults['show_ui']                 = true;
        $defaults['show_in_nav_menus']        = true;
        $defaults['show_in_menu']             = true;
        $defaults['show_in_admin_bar']        = true;
        $defaults['menu_position']             = null;
        $defaults['menu_icon']                 = false;
        $defaults['capability_type']         = 'page';
        //$args['capabilities']            = array();
        $defaults['map_meta_cap']            = null;
        $defaults['hierarchical']             = false;
        $defaults['supports']                 = array( 'title', 'editor', 'thumbnail' );
        $defaults['register_meta_box_cb']    = '';
        $defaults['taxonomies']                = array();
        $defaults['has_archive']             = true;
        $defaults['permalink_epmask']        = EP_PERMALINK;
        $defaults['rewrite']                 = array( 
            'slug'             => $post_type, 
            'with_front'    => false, 
            'feeds'         => true, 
            'pages'         => true,
            'ep_mask'        => EP_PERMALINK
        );            
        $defaults['query_var']                 = true;
        $defaults['can_export']                = true;
        $defaults['show_in_rest']            = true;
        $defaults['rest_base']                = $post_type;
        $defaults['rest_controller_class']    = 'WP_REST_Posts_Controller';        
                        
        return wp_parse_args( $args, $defaults );
    }
}