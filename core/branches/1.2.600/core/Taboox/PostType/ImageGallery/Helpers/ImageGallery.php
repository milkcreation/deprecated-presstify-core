<?php

namespace tiFy\Core\Taboox\PostType\ImageGallery\Helpers;

use tiFy\Core\Control\Control;

class ImageGallery extends \tiFy\App
{
    /**
     * Nombre d'instance d'appel
     * @var int
     */
    protected static $Instance = 1;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des fonctions d'aide à la saisie
        $this->appAddHelper('tify_taboox_image_gallery_has', 'has');
        $this->appAddHelper('tify_taboox_image_gallery_get', 'get');
        $this->appAddHelper('tify_taboox_image_gallery_display', 'display');
    }

    /**
     * Vérification d'existance d'élément
     *
     * @param int $post_id Identifiant de qualification du post
     * @param array $args Liste des attributs de récupération
     *
     * @return bool
     */
    public static function has($post_id = 0, $args = [])
    {
        return static::get($post_id, $args);
    }

    /**
     * Récupération de la liste des éléments
     *
     * @param int Identifiant de qualification du post
     * @param array $args Liste des attributs de récupération
     *
     * @return array Liste des identifiants de qualification des posts en relation
     */
    public static function get($post_id = 0, $args = [])
    {
        $post_id = (null === $post_id) ? get_the_ID() : $post_id;

        // Traitement des arguments
        $defaults = [
            'name'    => '_tify_taboox_image_gallery',
            // ID HTML
            'id'      => 'tify_taboox_image_gallery-' . static::$Instance++,
            // Class HTML
            'class'   => '',
            // Taille des images
            'size'    => 'full',
            // Nombre de vignette maximum
            'max'     => -1,
            // Attribut des vignettes
            'attrs'   => ['title', 'link', 'caption'],
            // Options
            'options' => [],
        ];
        $args = wp_parse_args($args, $defaults);

        if (!$attachment_ids = get_post_meta($post_id, $args['name'], true)) :
            return;
        endif;

        $slides = [];
        foreach ($attachment_ids as $attachment_id) :
            if (!$attachment_id) :
                continue;
            endif;
            if (!$post = get_post($attachment_id)) :
                continue;
            endif;

            $slides[] = [
                'src'     => wp_get_attachment_image_url($attachment_id, $args['size']),
                'alt'     => trim(strip_tags(get_post_meta($attachment_id, '_wp_attachment_image_alt', true))),
                'url'     => in_array('link', $args['attrs'])
                    ? wp_get_attachment_image_url($attachment_id, 'full')
                    : '',
                'title'   => in_array('title', $args['attrs']) ? get_the_title($attachment_id) : '',
                'caption' => in_array('caption', $args['attrs']) ? $post->post_content : '',
            ];

        endforeach;

        $id = $args['id'];
        $class = $args['class'];
        $options = wp_parse_args(
            $args['options'],
            [
                // Résolution du slideshow
                'ratio'       => '16:9',
                // Navigation suivant/précédent
                'arrow_nav'   => true,
                // Vignette de navigation
                'tab_nav'     => true,
                // Barre de progression
                'progressbar' => false,
            ]
        );

        return compact('id', 'class', 'options', 'slides');
    }

    /**
     * Affichage de la liste des éléments
     *
     * @param int|WP_Post $post Identifiant de qualification ou object Post Wordpress
     * @param array $args Liste des attributs de récupération
     * @param bool $echo Activation de l'affichage
     *
     * @return string Gabarit d'affichage de la liste des éléments.
     */
    public static function display($post_id = 0, $args = [], $echo = true)
    {
        // Bypass
        if (!$attrs = static::get($post_id, $args)) :
            return;
        endif;

        $output = Control::Slider($attrs);

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}