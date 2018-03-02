<?php

namespace tiFy\Core\Taboox\PostType\RelatedPosts\Helpers;

class RelatedPosts extends \tiFy\App
{
    /**
     * Liste des attributs de récupération par défaut
     * @var array
     */
    public static $DefaultAttrs = [
        'name'        => '_tify_taboox_related_posts',
        'post_type'   => 'any',
        'post_status' => 'publish',
        'max'         => -1,
    ];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des fonctions d'aide à la saisie
        $this->appAddHelper('tify_taboox_related_posts_has', 'has');
        $this->appAddHelper('tify_taboox_related_posts_get', 'get');
        $this->appAddHelper('tify_taboox_related_posts_display', 'display');
    }

    /**
     * Vérification d'existance d'élément
     *
     * @param int|WP_Post $post Identifiant de qualification ou object Post Wordpress
     * @param array $args Liste des attributs de récupération
     *
     * @return bool
     */
    public static function has($post = 0, $args = [])
    {
        return static::get($post, $args);
    }

    /**
     * Récupération de la liste des éléments
     *
     * @param int|WP_Post $post Identifiant de qualification ou object Post Wordpress
     * @param array $args Liste des attributs de récupération
     *
     * @return array Liste des identifiants de qualification des posts en relation
     */
    public static function get($post = 0, $args = [])
    {
        if (!$post = get_post($post)) :
            return;
        endif;

        // Traitement des arguments
        $args = \wp_parse_args($args, static::$DefaultAttrs);

        $related_posts = \get_post_meta($post->ID, $args['name'], true);

        // Suppression des données vides
        if (is_array($related_posts)) :
            $related_posts = array_filter($related_posts, function ($value) {
                return $value !== '';
            });
        endif;

        return $related_posts;
    }

    /**
     * Affichage de la liste des éléments
     *
     * @param int|WP_Post $post Identifiant de qualification ou object Post Wordpress
     * @param array $args Liste des attributs de récupération
     * @param bool $echo Activation de l'affichage
     *
     * @return string Gabarit d'affichage de la liste des éléments
     */
    public static function display($post = 0, $args = [], $echo = true)
    {
        // Bypass
        if (!$related_posts = static::Get($post, $args)) :
            return;
        endif;

        // Traitement des attributs de récupération
        $args = \wp_parse_args($args, static::$DefaultAttrs);

        // Requête de récupération des posts en relation
        $wp_query = new \WP_Query(
            [
                'post_type'      => 'any',
                'post__in'       => $related_posts,
                'posts_per_page' => $args['max'],
                'orderby'        => 'post__in',
            ]
        );
        if ($wp_query->have_posts()) :
            ob_start();
            self::tFyAppGetTemplatePart('display', null, compact('args', 'wp_query'));
            $output = ob_get_clean();
        endif;
        \wp_reset_query();

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}