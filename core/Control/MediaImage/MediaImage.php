<?php
/**
 * @name MediaImage
 * @desc Controleur d'affichage de chargement de fichier image dans la médiathèque Wordpress
 * @package presstiFy
 * @namespace tiFy\Core\Control\MediaImage
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\MediaImage;

/**
 * @Overrideable \App\Core\Control\MediaImage\MediaImage
 *
 * <?php
 * namespace \App\Core\Control\MediaImage
 *
 * class MediaImage extends \tiFy\Core\Control\MediaImage\MediaImage
 * {
 *
 * }
 */

class MediaImage extends \tiFy\Core\Control\Factory
{
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    protected function init()
    {
        // Déclaration des scripts
        \wp_register_style(
            'tify_control-media_image',
            self::tFyAppAssetsUrl('MediaImage.css', get_class()),
            [],
            141212
        );
        \wp_register_script(
            'tify_control-media_image',
            self::tFyAppAssetsUrl('MediaImage.js', get_class()),
            ['jquery'],
            141212,
            true
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    protected function enqueue_scripts()
    {
        @ wp_enqueue_media();
        \wp_enqueue_style('tify_control-media_image');
        \wp_enqueue_script('tify_control-media_image');
    }

    /**
     * Affichage
     *
     * @param array $attrs {
     *      Liste des attributs de configuration
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
     *      @param string $media_library_title ' Titre de la Médiathèque. "Personnalisation de l'image" par défaut.
     *      @param string $media_library_button ' Texte d'ajout de l'image dans la Médiathèque. "Utiliser cette image" par défaut.
     *      @param bool $image_editable Activation de l'administrabilité de l'image
     *  }
     *
     * @return string
     */
    protected function display($attrs = [])
    {
        if (!is_admin()) :
            return;
        endif;

        // Traitement des attributs de configuration
        $defaults = [
            'id'                   => 'tify_control_media_image-' . $this->getId(),
            'name'                 => 'tify_control_media_image-' . $this->getId(),
            'value'                => 0,
            'default'              => '',
            'default_color'        => "#F4F4F4",
            'width'                => 1920,
            'height'               => 360,
            'size'                 => 'large',
            'size_info'            => true,
            'inner_html'           => '',
            'media_library_title'  => __('Personnalisation de l\'image', 'tify'),
            'media_library_button' => __('Utiliser cette image', 'tify'),
            'image_editable'       => true
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

        // Calcul du ratio
        $ratio = 100 * ($height / $width);
        add_action(
            'admin_print_footer_scripts',
            function() use ($id, $ratio) {
                echo "<style type=\"text/css\">#{$id}:before{padding-top:{$ratio}%;}</style>";
            }
        );

        // Traitement de la valeur
        $default = (is_numeric($default) && ($default_image = wp_get_attachment_image_src($default,
                $size))) ? $default_image[0] : (is_string($default) ? $default : '');
        $value = (is_numeric($value) && ($image = wp_get_attachment_image_src($value,
                $size))) ? $image[0] : ((is_string($value) && !empty($value)) ? $value : $default);

        $output = "";
        $output .= "<div id=\"{$id}\" class=\"tify_control_media_image\" style=\"background-color:{$default_color}; max-width:{$width}px; max-height:{$height}px;\">\n";
        $output .= "\t<a href=\"#tify_control_media_image-add\"" .
            " class=\"tify_control_media_image-add\"";

        foreach ($attrs as $k => $v) :
            $output .= " data-$k=\"" . esc_attr(${$k}) . "\"";
        endforeach;

        $output .= " title=\"" . __('Modification de l\'image', 'tify') . "\"";
        $output .= " style=\"background-image:url( $value ); " . ($image_editable ? 'cursor:pointer;' : 'cursor:default;') . "\"";
        $output .= ">\n";
        if ($image_editable) :
            $output .= "\t\t<i class=\"tify_control_media_image-add_ico\"></i>\n";
        endif;
        $output .= "\t</a>\n";

        if ($size_info) :
            $_size_info_txt = (is_string($size_info)) ? $size_info : sprintf(__('%dpx / %dpx', 'tify'), $width,
                $height);
            $output .= "\t<span class=\"tify_control_media_image-size\">{$_size_info_txt}</span>\n";
        endif;

        if ($inner_html) :
            $output .= "\t<div class=\"tify_control_media_image-inner_html\">" . $inner_html . "</div>\n";
        endif;

        $output .= "\t<input type=\"hidden\" class=\"tify_control_media_image-input\" name=\"{$name}\" value=\"{$attrs['value']}\" />\n";
        $output .= "\t<a href=\"#tify_control_media_image-reset\" title=\"" . __('Rétablir l\'image originale',
                'tify') . "\" class=\"tify_control_media_image-reset tify_button_remove\" style=\"display:" . (($value && ($value != $default)) ? 'inherit' : 'none') . ";\"></a>";
        $output .= "</div>\n";

        echo $output;
    }
}