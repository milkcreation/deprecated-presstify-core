<?php
namespace tiFy\Core\Taboox\PostType\RichExcerpt\Admin;

class RichExcerpt extends \tiFy\Core\Taboox\PostType\Admin
{
    /**
     * DECLENCHEURS
     */
    /**
     * Chargement de la page courante
     *
     * @param \WP_Screen $current_screen
     *
     * @return void
     */
    public function current_screen($current_screen)
    {
        \add_action(
            'add_meta_boxes',
            function () use ($current_screen) {
                remove_meta_box('postexcerpt', $current_screen->id, 'normal');
        });
    }

    /**
     * CONTROLEURS
     */
    /**
     * Formulaire de saisie
     *
     * @param \WP_Post $post
     *
     * @return string
     */
    public function form($post)
    {
        \wp_editor(html_entity_decode($post->post_excerpt), 'excerpt', ['media_buttons' => false]);
    }
}