<?php
namespace tiFy\Core\Control\MediaImage;

class MediaImage extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de qualification de la classe
     * @var string
     */
    protected $ID = 'media_image';
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     * 
     * @return void
     */
    final public function init()
    {
        wp_register_style('tify_control-media_image', self::tFyAppAssetsUrl('MediaImage.css', get_class()), array(), '141212');
        wp_register_script('tify_control-media_image', self::tFyAppAssetsUrl('MediaImage.js', get_class()), array('jquery'), '141212', true);
    }
    
    /**
     * Mise en file des scripts
     * 
     * @return void
     */
    public static function enqueue_scripts()
    {
        @ wp_enqueue_media();
        wp_enqueue_style('tify_control-media_image');
        wp_enqueue_script('tify_control-media_image');
    }
    
    /**
     * Affichage du controleur
     * 
     * @param array $args {
     *      Attributs d'affichage du controleur
     *      
     *      @param string $id Identifiant de qualification
     *      @param string $name Nom du champ d'enregistrement
     *      @param int $value ID de l'attachment.
     *      @param int|string $default ID de l'attachment ou url de l'image initial.
     *      @param string $default_color Valeur Hexadécimal de la couleur de fond. "#F4F4F4" par défaut.
     *      @param int $width Largeur de l'image en pixel. 1920 par défaut 
     *      @param int $height Hauteur de l'image en pixel. 360 par defaut
     *      @param string $size Taille de l'attachment utilisé pour la prévisualisation de l'image. 'large' par défaut
     *      @param string $inner_html Contenu HTML d'enrichissement de l'affichage de l'interface de saisie.
     *      @param string $media_library_title' Titre de la Médiathèque. "Personnalisation de l'image" par défaut.
     *      @param string $media_library_button' Texte d'ajout de l'image dans la Médiathèque. "Utiliser cette image" par défaut.
     *      @param bool $image_editable Activation de l'administrabilité de l'image
     *      @param bool $echo Activation de l'affichage
     *  }
     * 
     * @return string
     */
    public static function display($args = array()) 
    {
        if(! is_admin())
            return;
    
        static $instance = 0;
        $instance++;
        
        $defaults = array(
            'id'                        => 'tify_control_media_image-'. $instance,
            'name'                      => 'tify_control_media_image-'. $instance,
            'value'                     => 0,
            'default'                   => '',
            'default_color'             => "#F4F4F4",
            'width'                     => 1920,
            'height'                    => 360,
            'size'                      => 'large',
            'inner_html'                => '',
            'media_library_title'       => __( 'Personnalisation de l\'image', 'tify' ),
            'media_library_button'      => __( 'Utiliser cette image', 'tify' ),
            'image_editable'            => true,
            'echo'                      => 1
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args );
        
        // Calcul du ratio
        $ratio = 100 * ( $height / $width );
        add_action( 'admin_print_footer_scripts', create_function( '', 'echo "<style type=\"text/css\">#'. $id .':before{padding-top:'. $ratio .'%;}</style>";' ) );
    
        // Traitement de la valeur
        $default = ( is_numeric( $default ) && ( $default_image = wp_get_attachment_image_src( $default, $size ) ) ) ? $default_image[0] : ( is_string( $default ) ? $default: '' );
        $value = ( is_numeric( $value ) && ( $image = wp_get_attachment_image_src( $value, $size ) ) ) ? $image[0] : ( ( is_string( $value ) && !empty( $value) ) ? $value : $default );
        
        $output  = "";
        $output .= "<div id=\"{$id}\" class=\"tify_control_media_image\" style=\"background-color:{$default_color}; max-width:{$width}px; max-height:{$height}px;\">\n";
        $output .= "\t<a href=\"#tify_control_media_image-add\"".
                    " class=\"tify_control_media_image-add\"";
        foreach( $args as $k => $v ) 
            $output .= " data-$k=\"". esc_attr( ${$k} ) ."\"";
        $output .= " title=\"". __( 'Modification de l\'image', 'tify' ) ."\"";
        $output .= " style=\"background-image:url( $value ); ". ( $image_editable ? 'cursor:pointer;' : 'cursor:default;' )."\"";
        $output .= ">\n";
        if( $image_editable )
            $output .= "\t\t<i class=\"tify_control_media_image-add_ico\"></i>\n";
        $output .= "\t</a>\n";    
        $output .= "\t<span class=\"tify_control_media_image-size\">". sprintf( __( '%dpx / %dpx', 'tify' ), $width, $height ) ."</span>\n";
        if( $inner_html )
            $output .= "\t<div class=\"tify_control_media_image-inner_html\">". $inner_html ."</div>\n";
        
        $output .= "\t<input type=\"hidden\" class=\"tify_control_media_image-input\" name=\"{$name}\" value=\"{$args['value']}\" />\n";
        $output .= "\t<a href=\"#tify_control_media_image-reset\" title=\"". __( 'Rétablir l\'image originale', 'tify' ) ."\" class=\"tify_control_media_image-reset tify_button_remove\" style=\"display:". ( ( $value && ( $value != $default ) )? 'inherit' : 'none' ) .";\"></a>";
        $output .= "</div>\n";
        
        if( $echo )
            echo $output;

        return $output;
    }
}