<?php
namespace tiFy\Core\Taboox\Taxonomy\CustomHeader\Admin;

use tiFy\Core\Control\MediaImage\MediaImage;

class CustomHeader extends \tiFy\Core\Taboox\Taxonomy\Admin
{
    /**
     * DECLENCHEURS
     */
    /**
     * Chargement de la page courante
     */
    public function current_screen($current_screen)
    {
        if (!isset($this->args['name'])) :
            $this->args['name'] = '_custom_header';
        endif;

        tify_meta_term_register($current_screen->taxonomy, $this->args['name'], true);
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     */
    public function admin_enqueue_scripts()
    {
        MediaImage::enqueue_scripts();
        wp_enqueue_style('Taboox_Taxonomy_CustomHeader_Admin', self::tFyAppUrl(get_class()) . '/CustomHeader.css', ['tify_control-media_image'], '150325');
        wp_enqueue_script('Taboox_Taxonomy_CustomHeader_Admin', self::tFyAppUrl(get_class()) . '/CustomHeader.js', ['jquery', 'tify_control-media_image'], '150325', true);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Formulaire de saisie
     */
    public function form( $term, $taxonomy )
    {
        $args = $this->args;

        $args['media_library_title']      = __( 'Personnalisation de l\'image d\'entête', 'tify' );
        $args['media_library_button']     = __( 'Utiliser comme image d\'entête', 'tify' );
        $args['name']                     = "tify_meta_term[{$this->args['name']}]";
        $args['value']                    = get_term_meta( $term->term_id, $this->args['name'], true );

                
        tify_control_media_image($args);
    }
}