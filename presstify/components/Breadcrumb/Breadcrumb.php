<?php
/**
 * @name Breadcrumb
 * @package PresstiFy
 * @category Components
 * @namespace tiFy\Components\Breadcrumb
 * @desc Affichage de fil d'Ariane
 * @author Jordy Manner
 * @copyright Tigre Blanc Digital
 * @version 1.2.170726
 */
namespace tiFy\Components\Breadcrumb;

class Breadcrumb extends \tiFy\Environment\Component
{
    /**
     * Liste des actions à déclencher
     */
    protected $CallActions                = array(
        'init',
        'wp_enqueue_scripts'
    );
    
    /**
     * Instance
     * @var integer
     */
    private static $Instance            = 1;

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    final public function init()
    {
        // Déclaration des controleurs
        self::setController( 'template', '\tiFy\Components\Breadcrumb\Template' );
        
        // Déclaration des scripts
        $min = SCRIPT_DEBUG ? '' : '.min';
        wp_register_style( 'tiFyComponentBreadcrumb', self::getAssetsUrl( get_class() ) .'/Breadcrumb'. $min.'.css', array(), 160318 );
    }

    /**
     * Mise en file des scripts
     */
    final public function wp_enqueue_scripts()
    {    
        if( $theme = self::getConfig( 'theme' ) )
            wp_enqueue_style( 'tiFyComponentBreadcrumb' );
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     */
    final public static function display( $args = array(), $echo = true )
    {
        global $post;
        
        $Template = self::getController( 'template' );
        
        $config = wp_parse_args( $args, self::getConfig() );
        extract( $config, EXTR_SKIP );
        
        if( empty( $id )  )
            $id = 'tiFyBreadcrumb-'. self::$Instance++;
        
        $output  = "";
        $output .= $before ."<ol id=\"{$id}\" class=\"tiFyBreadcrumb". ( ! empty( $class ) ? ' '. $class : '' ) ."\">";
        
        // Retour à la racine du site
        $output .= $Template::root();
        
        // Page 404 - Contenu introuvable
        if( is_404() ) : 
            $output .= $Template::is_404();
        
        // Page de résultats de recherche
        elseif( is_search() ) : 
            $output .= $Template::is_search();
        
        // Page de contenus associés à une taxonomie
        elseif( is_tax() ) :        
            $output .= $Template::is_tax();
        
        // Page d'accueil du site
        elseif( is_front_page() ) : 
            $output .= $Template::is_front_page();
        
        // Page liste des articles du blog
        elseif( is_home() ) :
            $output .= $Template::is_home();
        
        // Page de fichier média
        elseif ( is_attachment() ) :
            $output .= $Template::is_attachment();
        
        // Page de contenu de type post
        elseif ( is_single() ) :        
            $output .= $Template::is_single();
        
        // Page de contenu de type page
        elseif ( is_page() ) :  
            $output .= $Template::is_page();
        
        // Page de contenus associés à une catégorie 
        elseif( is_category() ) :
            $output .= $Template::is_category();
        
        // Page de contenus associés à un mot-clef
        elseif ( is_tag() ) :
            $output .= $Template::is_tag();
        
        // Page de contenus associés à un auteur
        elseif ( is_author() ) :
            $output .= $Template::is_author();
        
        // Page de contenus relatifs à une date
        elseif ( is_date() ) :
            $output .= $Template::is_date();
        
        // Pages de contenus
        elseif ( is_archive() ) :
            $output .= $Template::is_archive();
        
        /** 
         * @todo
         */
        // elseif ( is_comments_popup() ) :
        // elseif ( is_paged() ) :
        // else :
        endif;
                
        $output .= "</ol>". $after;
        
        if( $echo )
            echo $output;

        return $output;
    }
}