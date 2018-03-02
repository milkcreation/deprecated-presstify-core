<?php
/**
 * @name MediaFile
 * @desc Controleur d'affichage de chargement de fichier dans la médiathèque Wordpress
 * @package presstiFy
 * @namespace tiFy\Core\Control\MediaFile
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\MediaFile;

use tiFy\Lib\File;

/**
 * @Overrideable \App\Core\Control\MediaFile\MediaFile
 *
 * <?php
 * namespace \App\Core\Control\MediaFile
 *
 * class MediaFile extends \tiFy\Core\Control\MediaFile\MediaFile
 * {
 *
 * }
 */

class MediaFile extends \tiFy\Core\Control\Factory
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
        \wp_register_style(
            'tify_control-media_file',
            self::tFyAppAssetsUrl('MediaFile.css', get_class()),
            ['dashicons'],
            160621
        );
        \wp_register_script(
            'tify_control-media_file',
            self::tFyAppAssetsUrl('MediaFile.js', get_class()),
            ['jquery'],
            160621,
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
        wp_enqueue_media();
        \wp_enqueue_style('tify_control-media_file');
        \wp_enqueue_script('tify_control-media_file');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return string
     */
    protected function display($args = [])
    {
        if (!is_admin()) :
            return;
        endif;

        // Traitement des attributs de configuration
        $defaults = [
            'id'       => 'tify_control_media_file-' . $this->getId(),
            'name'     => 'tify_control_media_file-' . $this->getId(),
            'value'    => 0,    // Attachment ID
            'default'  => 0,    // Attachment ID
            'filetype' => '', // video || application/pdf || video/flv, video/mp4,
        ];
        $args = wp_parse_args($args, $defaults);
        extract($args);

        // Définition des attributs par défaut
        if ($default && ($filename = get_attached_file($default))) :
            $original_media_id = (int)$default;
            $original_media_title = get_the_title($original_media_id) . ' &rarr; ' . basename($filename);
        else :
            $original_media_id = 0;
            $original_media_title = __('Aucun fichier choisi', 'tify');
        endif;

        // Récupération de l'ID du média depuis son url
        if (!is_numeric($value)) :
            $value = File::attachmentIDFromUrl($value);
        endif;

        // Définition des attribut de l'élément existant
        if ($value && ($filename = get_attached_file($value))) :
            $media_id = (int)$value;
            $media_title = get_the_title($media_id) . ' &rarr; ' . basename($filename);
        else :
            $media_id = $original_media_id;
            $media_title = $original_media_title;
        endif;

        // Affichage
        $output = "";
        $output .= "<div id=\"{$id}\" data-tify_control=\"media_file\" class=\"tify_control_media_file" . ($media_id ? ' active' : '') . "\"";
        $output .= " data-original_id=\"{$original_media_id}\"";
        $output .= " data-original_title=\"{$original_media_title}\"";
        $output .= " data-media_library_title=\"" . __('Sélection du fichier média', 'tify') . "\"";
        $output .= " data-media_library_button=\"" . __('Sélectionner', 'tify') . "\"";
        $output .= " data-media_library_filetype=\"{$filetype}\"";
        $output .= ">\n";
        $output .= "\t<input type=\"hidden\" name=\"{$name}\" value=\"{$media_id}\" class=\"tify_control_media_file-id\"/>";
        $output .= "\t<div class=\"tify_input-large tify_input_media\">\n";
        $output .= "\t\t<input disabled=\"disabled\" type=\"text\" value=\"{$media_title}\" class=\"tify_control_media_file-title\" autocomplete=\"off\"/>\n";
        $output .= "\t</div>";
        $output .= "\t<a href=\"#{$id}\" class=\"tify_control_media_file-reset dashicons dashicons-no-alt\"></a>";
        $output .= "</div>\n";

        echo $output;
    }
}
