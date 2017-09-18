<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\ImageLightbox;

class ImageLightbox extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'image_lightbox';
    
    /**
     * Instance courante
     */
    protected static $Instance;    
    
    /**
     * Groupes
     */
    protected static $Group         = array();
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    final public function init()
    {
        wp_register_script( 'tify_control-image_lightbox', self::tFyAppAssetsUrl('ImageLightbox.js', get_class()), array( 'tify-imagelightbox' ), 170724, true );
    }
    
    /**
     * Mise en file des scripts
     */
    final public function enqueue_scripts()
    {
        wp_enqueue_style( 'tify-imagelightbox' );
        wp_enqueue_script( 'tify_control-image_lightbox' );
    }
       
    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     */
    final public static function display( $args = array(), $echo = true )
    {
        self::$Instance++;
        
        $defaults = array(
            // Marqueur d'identification unique
            'id'                => 'tiFyControl-image_lightbox-'. self::$Instance,
            // Id Html du conteneur
            'container_id'      => 'tiFyControlImageLightbox--'. self::$Instance,
            // Classe Html du conteneur
            'container_class'   => '',
            // Groupe 
            'group'             => '',            
            // Options
            'options'           => array(),
            // Source de l'image
            'src'               => '',
            // Liste des slides
            'content'           => ''
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args );
        
        $options = wp_parse_args(
            $options,
            array(
                // Couleur du theme
                'theme'                 => 'dark',
                // Fond couleur
                'overlay'               => true,
                // Indicateur de chargement
                'spinner'               => true,
                // Bouton de fermeture
                'close_button'          => true,
                // Légende (basé sur le alt de l'image)
                'caption'               => true,
                // Flèche de navigation suivant/précédent
                'navigation'            => true,
                // Onglets de navigation
                'tabs'                  => true,
                // Control au clavier
                'keyboard'              => true,
                // Fermeture au clic sur le fond
                'overlay_close'         => true,
                // Vitesse de défilement
                'animation_speed'       => 250
            )
        );        
        
        if( !$content && !is_null( $content ) )
            $content = "<img src=\"{$src}\" alt=\"". basename( $src ) ."\">";
        
        $output  = "";
        $output .= "<a href=\"{$src}\" id=\"{$container_id}\" class=\"tiFyControlImageLightbox". ( $container_class ? ' '. $container_class : '' ) ."\" data-tify_control=\"image_lightbox\" data-options=\"". htmlentities( json_encode( $options ) ) ."\" data-group=\"{$group}\">\n";
        $output .= $content;
        $output .= "</a>\n";
        
        if( $group && ! in_array( $group, self::$Group ) ) :
            array_push(self::$Group, $group);
            add_action( 'wp_footer', function() use ($group, $options){
                echo "<input id=\"tiFyControlImageLightbox-groupOption--{$group}\" type=\"hidden\" value=\"". htmlentities( json_encode( $options ) ) ."\" />";
            });
        endif;
        
        if( $echo )
            echo $output;

        return $output;
    }     
}