<?php

namespace tiFy\Core\Taboox\PostType\RelatedPosts\Helpers;

use tiFy\Core\Taboox\Helpers;

class RelatedPosts extends Helpers
{

    /**
     * Identifiant des fonctions d'aide à la saisie
     */
    protected $ID = 'related_posts';

    /**
     * Liste des méthodes à convertir en fonction d'aide à la saisie
     */
    protected $Helpers = ['has', 'get', 'display'];

    /**
     * Attributs par défaut
     */
    // 
    public static $DefaultAttrs = [
        'name'        => '_tify_taboox_related_posts',
        'post_type'   => 'any',
        'post_status' => 'publish',
        'max'         => -1,
    ];

    /**
     * Vérification d'existance d'élément
     */
    public static function has($post = 0, $args = [])
    {
        return static::Get($post, $args);
    }

    /**
     * Récupération de la liste des éléments
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
     */
    public static function display($post = 0, $args = [], $echo = true)
    {
        // Bypass
        if (!$related_posts = static::Get($post, $args)) :
            return;
        endif;

        static $instances = 0;
        $instances++;

        $args = \wp_parse_args($args, static::$DefaultAttrs);

        $output              = "";
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
            $output .= ob_get_clean();
        endif;
        \wp_reset_query();

        if ($echo) :
            echo $output;
        endif;

        return $output;
    }
}