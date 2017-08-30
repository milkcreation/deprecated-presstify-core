<?php
namespace tiFy\Core\Taboox\Taxonomy\CustomHeader\Admin;

class CustomHeader extends \tiFy\Core\Taboox\Taxonomy\Admin
{
    /**
     * DECLENCHEURS
     */
    /**
     * Chargement de la page courante
     */
    public function current_screen( $current_screen )
    {
        tify_meta_term_register( $current_screen->taxonomy, '_custom_header', true );
    }
    
    /**
     * Mise en file des scripts de l'interface d'administration
     */
    public function admin_enqueue_scripts()
    {
        wp_enqueue_style( 'Taboox_Taxonomy_CustomHeader_Admin', self::tFyAppUrl( get_class() ) .'/CustomHeader.css', array( 'tify_control-media_image' ), '150325' );
        wp_enqueue_script( 'Taboox_Taxonomy_CustomHeader_Admin', self::tFyAppUrl( get_class() ) .'/CustomHeader.js', array( 'jquery', 'tify_control-media_image' ), '150325', true );
    }
    
        /**
     * CONTROLEURS
     */
    /**
     * Formulaire de saisie
     */
    public function form( $term, $taxonomy )
    {
        $this->args['media_library_title']      = __( 'Personnalisation de l\'image d\'entête', 'tify' );
        $this->args['media_library_button']     = __( 'Utiliser comme image d\'entête', 'tify' );
        $this->args['name']                     = 'tify_meta_term[_custom_header]';
        $this->args['value']                    = get_term_meta( $term->term_id, '_custom_header', true );
                
        tify_control_media_image( $this->args );
    }
}