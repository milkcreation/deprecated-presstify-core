<?php
namespace tiFy\Core\Taboox\PostType\TextRemainingExcerpt\Admin;

class TextRemainingExcerpt extends \tiFy\Core\Taboox\PostType\Admin
{
    /**
     * Chargement de la page courante
     *
     * @param \WP_Screen $current_screen
     *
     * @return void
     */
    public function current_screen($current_screen)
    {
        add_action('add_meta_boxes', function () use ($current_screen) {
            remove_meta_box('postexcerpt', $current_screen->id, 'normal');
        });

        // Traitement des arguments
        $this->args = wp_parse_args($this->args, [
                'length' => 255
            ]);
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    public function admin_enqueue_scripts()
    {
        tify_control_enqueue('text_remaining');
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
        tify_control_text_remaining([
                'name'   => 'excerpt',
                'value'  => $post->post_excerpt,
                'length' => $this->args['length']
            ]);
    }
}