<?php

namespace tiFy\Core\Taboox\PostType\RichExcerpt\Helpers;

class RichExcerpt extends \tiFy\App
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des fonctions d'aide à la saisie
        $this->appAddHelper('the_rich_excerpt', 'display');
    }

    /**
     * Affichage
     *
     * @param int|WP_Post $post Identifiant de qualification ou object Post Wordpress
     *
     * @return string Gabarit d'affichage de la liste des éléments.
     */
    public static function display($post_id = null)
    {
        $post_id = (null === $post_id) ? get_the_ID() : $post_id;

        echo apply_filters('the_content', get_post_field('post_excerpt', $post_id));
    }
}