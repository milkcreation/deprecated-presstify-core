<?php
/**
 * @name Breadcrumb
 * @package PresstiFy
 * @subpackage Components
 * @namespace tiFy\Components\Breadcrumb
 * @desc Affichage de fil d'Ariane des contenus Wordpress
 * @author Jordy Manner
 * @copyright Tigre Blanc Digital
 * @version 1.2.369
 */

namespace tiFy\Components\Breadcrumb;

class Breadcrumb extends \tiFy\App\Component
{
    /**
     * Instance
     * @var integer
     */
    private static $Instance = 1;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Définition des conteneurs de dépendances
        $this->appAddContainer('tiFy\Components\Breadcrumb\Template', $this->appLoadOverride('\tiFy\Components\Breadcrumb\Template'));

        // Definition des événements
        $this->appAddAction('init');
        $this->appAddAction('wp_enqueue_scripts');
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        // Déclaration des scripts
        wp_register_style(
            'tiFyComponents-breadcrumb',
            self::tFyAppAssetsUrl('Breadcrumb.css', get_class()),
            [],
            160318
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    final public function wp_enqueue_scripts()
    {
        if (self::tFyAppConfig('enqueue_scripts')) :
            wp_enqueue_style('tiFyComponents-breadcrumb');
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     */
    final public static function display($args = [], $echo = true)
    {
        global $post;

        $Template = self::tFyAppGetContainer('tiFy\Components\Breadcrumb\Template');

        $config = wp_parse_args($args, self::tFyAppConfig());
        extract($config, EXTR_SKIP);

        if (empty($container_id)) {
            $container_id = 'tiFyBreadcrumb-' . self::$Instance++;
        }

        $output = "";
        $output .= $before . "<ol id=\"{$container_id}\" class=\"tiFyBreadcrumb" . (!empty($container_class) ? ' ' . $container_class : '') . "\">";

        // Retour à la racine du site
        $output .= $Template::root();

        // Page 404 - Contenu introuvable
        if (is_404()) :
            $output .= $Template::is_404();

        // Page de résultats de recherche
        elseif (is_search()) :
            $output .= $Template::is_search();

        // Page de contenus associés à une taxonomie
        elseif (is_tax()) :
            $output .= $Template::is_tax();

        // Page d'accueil du site
        elseif (is_front_page()) :
            $output .= $Template::is_front_page();

        // Page liste des articles du blog
        elseif (is_home()) :
            $output .= $Template::is_home();

        // Page de fichier média
        elseif (is_attachment()) :
            $output .= $Template::is_attachment();

        // Page de contenu de type post
        elseif (is_single()) :
            $output .= $Template::is_single();

        // Page de contenu de type page
        elseif (is_page()) :
            $output .= $Template::is_page();

        // Page de contenus associés à une catégorie 
        elseif (is_category()) :
            $output .= $Template::is_category();

        // Page de contenus associés à un mot-clef
        elseif (is_tag()) :
            $output .= $Template::is_tag();

        // Page de contenus associés à un auteur
        elseif (is_author()) :
            $output .= $Template::is_author();

        // Page de contenus relatifs à une date
        elseif (is_date()) :
            $output .= $Template::is_date();

        // Pages de contenus
        elseif (is_archive()) :
            $output .= $Template::is_archive();

            /**
             * @todo
             */
            // elseif ( is_comments_popup() ) :
            // elseif ( is_paged() ) :
            // else :
        endif;

        $output .= "</ol>" . $after;

        if ($echo) {
            echo $output;
        }

        return $output;
    }
}