<?php
/**
 * @name AdminUI
 * @package PresstiFy
 * @subpackage Components
 * @namespace tiFy\Components\AdminUI
 * @desc Configuration de l'interface d'administration de Wordpress
 * @author Jordy Manner
 * @copyright Tigre Blanc Digital
 * @version 1.2.369
 */
namespace tiFy\Components\AdminUI; 

class AdminUI extends \tiFy\Environment\Component
{
    /**
     * Liste des actions à déclencher
     * @var callable[]
     */
    protected $tFyAppActions                  = array(
        'init',
        'widgets_init',
        'admin_menu',
        'add_meta_boxes',
        'admin_bar_menu',
        'admin_footer_text',
        'wp_before_admin_bar_render'
    );

    /**
     * Ordre de priorité d'exécution des actions
     * @var mixed[]
     */
    protected $tFyAppActionsPriority       = array(
        'init'                  => 99,
        'admin_menu'            => 99,
        'add_meta_boxes'        => 99,
        'admin_bar_menu'        => 11
    );

    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();
        
        foreach( (array) array_keys( self::tFyAppConfigDefault() ) as $prop ) :
            if( self::tFyAppConfig( $prop ) ) :
                $value = is_array( self::tFyAppConfigDefault( $prop ) ) ? wp_parse_args( self::tFyAppConfig( $prop ), self::tFyAppConfigDefault( $prop ) ) : self::tFyAppConfig( $prop );
            else :
                $value = self::tFyAppConfigDefault( $prop );
            endif;
            self::tFyAppConfigSet( $prop, $value );
        endforeach;
        
        if( self::tFyAppConfig( 'disable_post' ) ) :
            add_action( 'admin_init', array( $this, 'disable_post_dashboard_meta_box' ) );
            add_action( 'admin_menu', array( $this, 'disable_post_remove_menu' )  );
            add_filter( 'nav_menu_meta_box_object', array( $this, 'disable_post_nav_menu_meta_box_object' ) );
            add_action( 'wp_before_admin_bar_render', array( $this, 'disable_post_wp_before_admin_bar_render' ) );
        endif;
        
        if( self::tFyAppConfig( 'disable_comment' ) ) :
            add_action( 'init', array( $this, 'disable_comment_init' ) );
            add_action( 'wp_widgets_init', array( $this, 'disable_comment_wp_widgets_init' ) );
            add_action( 'admin_menu', array( $this, 'disable_comment_remove_menu' ) );
            add_action( 'wp_before_admin_bar_render', array( $this, 'disable_comment_wp_before_admin_bar_render' ) );
        endif;
        
        if( self::tFyAppConfig( 'disable_post_category' ) ) :
            add_action( 'init', array( $this, 'disable_post_category' ) );
        endif;
        
        if( self::tFyAppConfig( 'disable_post_tag' ) ) :
            add_action( 'init', array( $this, 'disable_post_tag' ) );
        endif;
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Inititalisation globale
     */
    final public function init()
    {
        $this->remove_support_post_type();
        $this->unregister_taxonomy_for_object_type();
    }

    /**
     * Initialisation des widgets
     */
    final public function widgets_init() 
    {
        $this->unregister_widget();
    }

    /**
     * Initialisation du menu d'administration
     */
    final public function admin_menu()
    {
         $this->remove_menu();
         $this->remove_dashboard_meta_box();
    }

    /**
     * Ajout de métaboxes
     */
    final public function add_meta_boxes()
    {
        $this->remove_meta_box_post_types();
    }

    /**
     * Initialisation de la barre d'administration
     * @param object $wp_admin_bar
     */
    final public function admin_bar_menu( $wp_admin_bar )
    {
        if( ! $admin_bar_menu_logo = self::tFyAppConfig( 'admin_bar_menu_logo' ) ) :
            return;
        endif;
    
        $wp_admin_bar->remove_menu( 'wp-logo' );

        foreach( (array) $admin_bar_menu_logo as $node ) :
            if( ! empty( $node['group'] ) ) :
                $wp_admin_bar->add_group( $node );
            else :
                $wp_admin_bar->add_menu( $node );
            endif;
        endforeach;
    }

    /**
     * Personnalisation du pied de page de l'interface d'administration
     * 
     * @param string $text
     * 
     * @return string
     */
    final public function admin_footer_text( $text = '' )
    {
        if( $admin_footer_text = self::tFyAppConfig( 'admin_footer_text' ) ) :
            $text = $admin_footer_text;
        endif;
    
        return $text;
    }

    /**
     * Pré-rendu de la barre d'administration
     */
    final public function wp_before_admin_bar_render()
    {
        $this->remove_admin_bar_menu();
    }

    /**
     * CONTROLEURS
     */
    /**
     * Suppression des entrées de menus
     */
    private function remove_menu()
    {
        foreach( (array) self::tFyAppConfig( 'remove_menu' ) as $menu ) :
            switch( $menu ) :
                default :
                    remove_menu_page( $menu ); 
                    break;
                case 'dashboard'     :
                    remove_menu_page( 'index.php' ); 
                    break;
                case 'posts'         :
                    remove_menu_page( 'edit.php' );
                    break;
                case 'media'         :
                    remove_menu_page( 'upload.php' );
                    break;
                case 'pages'        :
                    remove_menu_page( 'edit.php?post_type=page' );
                    break;
                case 'comments'        :
                    remove_menu_page( 'edit-comments.php' ); 
                    break;
                case 'appearence'    :
                    remove_menu_page( 'themes.php' );
                    break;
                case 'plugins'    :
                    remove_menu_page( 'plugins.php' );
                    break;
                case 'users'    :
                    remove_menu_page( 'users.php' ); 
                    break;
                case 'tools'    :
                    remove_menu_page( 'tools.php' ); 
                    break;
                case 'settings'    :
                    remove_menu_page( 'options-general.php' ); 
                    break;
            endswitch;
        endforeach;
    }

    /**
     * Suppression des attributs de support des types de post
     */
    private function remove_support_post_type()
    {        
        foreach( array_keys( self::tFyAppConfig() ) as $config ) :
            if( ! preg_match( '/^remove_support_(.*)/', $config, $post_type ) )
                continue;
            if( ! post_type_exists( $post_type[1] ) )
                return;
            
            foreach( (array) self::tFyAppConfig( $config ) as $support )
                remove_post_type_support( $post_type[1], $support );
        endforeach;
    }

    /**
     * Suppression des Widgets
     */
    private function unregister_widget()
    {
        foreach( (array) self::tFyAppConfig( 'unregister_widget' ) as $widget  ) :
            switch( $widget ) :
                default :
                    unregister_widget( $widget );
                    break;
                case 'pages':
                case 'calendar':
                  case 'archives':
                  case 'links':
                  case 'meta':
                  case 'searc':
                  case 'text':
                  case 'categories':
                  case 'recent posts':
                  case 'recent comments':
                  case 'rss':
                  case 'tag cloud':
                  case 'nav menu':    
                    unregister_widget( 'WP_Widget_'. preg_replace( '/\s/', '_', ucwords( $widget ) ) );
                    break;
                case 'rss' :
                    unregister_widget( 'WP_Widget_RSS' );
                    break;
                case 'nav menu' :
                    unregister_widget( 'WP_Nav_Menu_Widget' );
                    break;
            endswitch;
        endforeach;
    }

    /**
     * Suppression de metaboxes du tableau de bord
     */
    private function remove_dashboard_meta_box()
    {
        foreach( (array) self::tFyAppConfig( 'remove_dashboard_meta_box' ) as $metabox => $context ) :
            if( is_numeric( $metabox ) ) :
                remove_meta_box( 'dashboard_'. $context, 'dashboard', false );
            elseif( is_string( $metabox ) ) :
                remove_meta_box( 'dashboard_'. $metabox, 'dashboard', $context );
            endif;
        endforeach;
    }

    /**
     * Suppression de metaboxes par type de post
     */
    private function remove_meta_box_post_types()
    {
        foreach( array_keys(self::tFyAppConfig() ) as $config ) :
            if( ! preg_match( '/^remove_meta_box_(.*)/', $config, $post_type ) )
                continue;
            if( ! post_type_exists( $post_type[1] ) )
                return;
                
            $post_type = $post_type[1];    
            
            foreach( (array) self::tFyAppConfig( $config ) as $metabox => $context ) :
                if( is_numeric( $metabox ) ) :
                    $_metabox = $context;
                    remove_meta_box( $context, $post_type, false );
                elseif( is_string( $metabox ) ) :
                    $_metabox = $metabox;
                    remove_meta_box( $metabox, $post_type, $context );
                endif;
                
                // Hack Wordpress : Maintient du support de la modification du permalien
                if( $_metabox === 'slugdiv' ) :
                    add_action( 'edit_form_before_permalink', function( $post ) use ( $post_type ) {
                        if( $post->post_type !== $post_type )
                            return;
                        $editable_slug = apply_filters( 'editable_slug', $post->post_name, $post );
                        echo "<input name=\"post_name\" type=\"hidden\" size=\"13\" id=\"post_name\" value=\"". esc_attr( $editable_slug ) ."\" autocomplete=\"off\" />";
                    });
                endif;                
            endforeach;
        endforeach;
    }

    /**
     * Suppression d'entrée de menu de la barre d'administration
     */
    private function remove_admin_bar_menu()
    {
        global $wp_admin_bar;
        
        foreach( (array) self::tFyAppConfig( 'remove_admin_bar_menu' ) as $admin_bar_node ) :
            $wp_admin_bar->remove_node( $admin_bar_node );
        endforeach;
    }

    /**
     * Désactivation des interfaces relatives au type post
     */
    /**
     * Suppression de la metaboxe de tableau de bord - édition rapide
     */
    final public function disable_post_dashboard_meta_box() 
    {
        remove_meta_box( 'dashboard_quick_press', 'dashboard', 'normal' );
    }
    
    /**
     * Suppression du menu d'édition
     */
    final public function disable_post_remove_menu()
    {
        remove_menu_page( 'edit.php' );
    }
    
    /**
     * Désactivation de la metaboxe de menu
     * @param string $post_type
     * 
     * @return boolean|string
     */
    final public function disable_post_nav_menu_meta_box_object( $post_type )
    {
        if( $post_type->name === 'post' ) :
            return false;
        else :
            return $post_type;
        endif;
    }
    
    /**
     * Désactivation de l'entrée de menu "nouveau" de la barre d'administration
     */
    final public function disable_post_wp_before_admin_bar_render()
    {
        global $wp_admin_bar;
        
        $wp_admin_bar->remove_node( 'new-post' );
    }

    /**
     * Désactivation des interfaces relatives aux commentaires
     */
    /**
     * Désactivation du support des commentaires
     */
    final public function disable_comment_init()
    {
        remove_post_type_support( 'post', 'comments' );
        remove_post_type_support( 'page', 'comments' );
        update_option( 'default_comment_status', 0 );
    }

    /**
     * Désactivation des widgets
     */
    final public function disable_comment_wp_widgets_init()
    {
        unregister_widget( 'WP_Widget_Recent_Comments' );
    }

    /**
     * Désactivation des entrées de menu principal
     */
    final public function disable_comment_remove_menu()
    {
        remove_menu_page( 'edit-comments.php' ); 
        remove_submenu_page( 'options-general.php', 'options-discussion.php' );
    }

    /**
     * Désactivation des entrées de la barre d'administration
     */
    final public function disable_comment_wp_before_admin_bar_render()
    {
        global $wp_admin_bar;
        
        $wp_admin_bar->remove_node( 'comments' );
        
        if( is_multisite() ) :
            foreach( get_sites() as $site ) :
                $wp_admin_bar->remove_menu( 'blog-'. $site->blog_id .'-c' );
            endforeach;
        endif;
    }

    /**
     * Taxonomies
     */
    /**
     * Désactivation des catégories du type post
     */
    final public function disable_post_category()
    {
        global $wp_taxonomies;
        
        if( isset( $wp_taxonomies['category'] ) ) :
            $wp_taxonomies['category']->show_in_nav_menus = false;
        endif;
        
        unregister_taxonomy_for_object_type( 'category', 'post' );
    }

    /**
     * Désactivation des mots clefs (etiquettes) du type post
     */
    final public function disable_post_tag()
    {
        global $wp_taxonomies;
        
        if( isset( $wp_taxonomies['post_tag'] ) ) :
            $wp_taxonomies['post_tag']->show_in_nav_menus = false;
        endif;
        
        unregister_taxonomy_for_object_type( 'post_tag', 'post' );
    }

    /**
     * Désactivation des taxonomie pour un type de post
     */
    final public function unregister_taxonomy_for_object_type()
    {
        global $wp_taxonomies;
        
        foreach( array_keys( self::tFyAppConfig() ) as $config ) :
            if( ! preg_match( '/^unregister_taxonomy_for_(.*)/', $config, $post_type ) )
                continue;
            if( ! post_type_exists( $post_type[1] ) )
                continue;
            if( ! $taxonomies = self::tFyAppConfig( 'unregister_taxonomy_for_'. $post_type[1] ) )
                continue;
            foreach( $taxonomies as $taxonomy ) :
                unregister_taxonomy_for_object_type( $taxonomy, $post_type[1] );
            endforeach;
        endforeach;
    }
}