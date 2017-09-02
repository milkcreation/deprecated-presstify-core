<?php
namespace tiFy\Core\Taboox\Post\TextRemainingExcerpt\Admin;

class TextRemainingExcerpt extends \tiFy\Core\Taboox\Admin
{    
    /* = CHARGEMENT DE LA PAGE = */ 
    public function current_screen( $current_screen )
    {
        add_action( 'add_meta_boxes', function() use ( $current_screen ) { remove_meta_box( 'postexcerpt', $current_screen->id, 'normal' ); });
        
        // Traitement des arguments
        $this->args = wp_parse_args( 
            $this->args, 
            array(
                'length'        => 255
            )
        );      
    }
    
    /* = MISE EN FILE DES SCRIPTS = */
    public function admin_enqueue_scripts()
    {
        tify_control_enqueue('text_remaining');
    }
    
    /* = FORMULAIRE DE SAISIE = */  
    public function form( $post )
    {
        tify_control_text_remaining(
            array(
                'name'      => 'excerpt', 
                'value'     => $post->post_excerpt,
                'length'    => $this->args['length']
            )
        );
    }
}