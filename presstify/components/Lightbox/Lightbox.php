<?php
namespace tiFy\Components\Lightbox;

final class Lightbox extends \tiFy\Environment\Component
{
    /**
     * Liste des Actions à déclencher
     */ 
    protected $tFyAppActions                = array(
        'init',
        'wp_enqueue_scripts'
    );

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation générale
     */
    public function init()
    {
        // Déclaration des thèmes
        foreach (glob(self::tFyAppDirname().'/theme/*.css') as $filename) :
            wp_register_style('tiFyComponentsLightbox-theme--'. ucfirst(basename($filename, '.css')), self::tFyAppUrl() . '/theme/' . basename($filename), ['tify-imagelightbox'], 170913);
        endforeach;

        wp_register_script( 'tiFyComponentsLightbox', self::tFyAppAssetsUrl('/Lightbox.js', get_class()), array( 'tify-imagelightbox' ), 170724, true);
    }
    
    /**
     * Mise en file des scripts
     */
    public function wp_enqueue_scripts()
    {
        $args = wp_parse_args(
            self::tFyAppConfig(),
            array(
                'theme'                 => 'dark',
                'overlay'               => true,    // Couleur de fond
                'spinner'               => true,    // Indicateur de chargement
                'close_button'          => true,    // Bouton de fermeture
                'caption'               => true,    // Légende (basé sur le alt de l'image)
                'navigation'            => true,    // Flèche de navigation suivant/précédent 
                'tabs'                  => true,    // Onglets de navigation
                
                'keyboard'              => true,
                'overlay_close'         => false, 
                'animation_speed'       => 250
            )
        );
        
        wp_enqueue_style( 'tiFyComponentsLightbox-theme--'. ucfirst( $args['theme'] ) );
        wp_enqueue_script( 'tiFyComponentsLightbox' );
        wp_localize_script( 
            'tiFyComponentsLightbox', 
            'tiFyLightbox', 
            $args
        );
    }
    
    /**
     * Parcours du contenu à la recherche des images
     * @deprecated
     * @param unknown $content
     * @return string
     */
    public function the_content( $content )
    {
        // Ajout via php de l'attribut de gestion des images des articles 
        $content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
        $document = new \DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML( utf8_decode( $content )    );
            
        foreach( $document->getElementsByTagName('a') as $link ) :
            if( ! $src = $link->getAttribute('href') )
                continue;
            if(  !preg_match( '/\.(?:jpe?g|png|gif)$/', $src ) )
                continue;
            
            foreach( $link->getElementsByTagName('img') as $img ) :
                $link->setAttribute( 'data-role', 'tiFyLightbox-image' );
            endforeach;
        endforeach;
                  
        return $document->saveHTML();
    }
}