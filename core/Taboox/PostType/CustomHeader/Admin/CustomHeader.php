<?php
namespace tiFy\Core\Taboox\PostType\CustomHeader\Admin;

class CustomHeader extends \tiFy\Core\Taboox\PostType\Admin
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
        // Déclaration des metadonnées à enregistrer
        tify_meta_post_register($current_screen->id, '_custom_header', true);
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    public function admin_enqueue_scripts()
    {
        tify_control_enqueue('media_image');
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
        $this->args['media_library_title'] = __('Personnalisation de l\'image d\'entête', 'tify');
        $this->args['media_library_button'] = __('Utiliser comme image d\'entête', 'tify');
        $this->args['name'] = 'tify_meta_post[_custom_header]';
        $this->args['value'] = get_post_meta($post->ID, '_custom_header', true);

        tify_control_media_image($this->args);
    }
}