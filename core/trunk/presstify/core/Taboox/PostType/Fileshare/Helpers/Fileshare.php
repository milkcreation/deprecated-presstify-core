<?php

namespace tiFy\Core\Taboox\PostType\Fileshare\Helpers;

use tiFy\Core\Medias\Upload;
use tiFy\Core\Meta\Post as MetaPost;

class Fileshare extends \tiFy\App
{
    /**
     * Liste des attributs de récupération par défaut
     * @var array
     */
    public static $DefaultAttrs = [
        'name' => '_tify_taboox_fileshare',
        'max'  => -1,
    ];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des fonctions d'aide à la saisie
        $this->appAddHelper('tify_taboox_fileshare_has', 'has');
        $this->appAddHelper('tify_taboox_fileshare_get', 'get');
        $this->appAddHelper('tify_taboox_fileshare_display', 'display');
    }

    /**
     * Vérification d'existance d'éléments
     *
     * @param int $post_id Identifiant de qualification du post
     * @param array $args Liste des attributs de récupération
     *
     * @return bool
     */
    public static function has($post_id = null, $args = [])
    {
        return self::get($post_id, $args);
    }

    /**
     * Récupération de la liste des éléments
     *
     * @param int Identifiant de qualification du post
     * @param array $args Liste des attributs de récupération
     *
     * @return array Liste des identifiants de qualification des posts en relation
     */
    public static function get($post_id = null, $args = [])
    {
        $post_id = (null === $post_id) ? get_the_ID() : $post_id;

        $args = wp_parse_args($args, self::$DefaultAttrs);

        return MetaPost::get($post_id, $args['name']);
    }

    /**
     * Affichage de la liste des éléments
     * @maintenance Passage en template
     *
     * @param int|WP_Post $post Identifiant de qualification ou object Post Wordpress
     * @param array $args Liste des attributs de récupération
     * @param bool $echo Activation de l'affichage
     *
     * @return string Gabarit d'affichage de la liste des éléments.
     */
    public static function display($post_id = null, $args = [], $echo = true)
    {
        $post_id = (null === $post_id) ? get_the_ID() : $post_id;

        if (!$files = self::Get($post_id, $args)) {
            return;
        }

        $args = wp_parse_args($args, self::$DefaultAttrs);

        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['path'];
        $upload_url = $upload_dir['url'];

        $ID = sanitize_key($args['name']);

        $output = "";
        $output .= "<div class=\"tiFyTabooxFileshare tiFyTabooxFileshare--{$ID}\">\n";
        $output .= "\t<ul class=\"tiFyTabooxFileshare-items\">\n";
        foreach ((array)$files as $file_id) :
            $fileurl = wp_get_attachment_url($file_id);
            $filename = $upload_path . '/' . wp_basename($fileurl);
            $ext = preg_replace('/^.+?\.([^.]+)$/', '$1', $fileurl);
            $filesize = 0;

            if (file_exists($filename)) {
                $filesize = round(filesize($filename), 2);
            }

            $thumb_url = false;
            if (($attachment_id = intval($file_id)) && $thumb_url = wp_get_attachment_image_src($attachment_id,
                    'thumbnail', false)) {
                $thumb_url = $thumb_url[0];
            }

            $output .= "\t\t<li class=\"tiFyTabooxFileshare-item\">";
            $output .= "\t\t\t<a href=\"" . Upload::url($file_id) . "\" class=\"tiFyTabooxFileshare-itemUploadLink\"  title=\"" . __('Télécharger le fichier',
                    'tify') . "\">\n";

            // Icone
            if ($thumb_url) :
                $output .= "\t\t\t\t<img src=\"{$thumb_url}\" class=\"tiFyTabooxFileshare-itemThumbnail\" />\n";
            else :
                $output .= "\t\t\t\t<i class=\"tiFyTabooxFileshare-itemIcon tiFyTabooxFileshare-itemIcon--{$ext}\"></i>\n";
            endif;

            // Titre du fichier
            $output .= "\t\t\t\t<span class=\"tiFyTabooxFileshare-itemTitle\">" . get_the_title($file_id) . "</span>\n";

            // Nom du fichier
            $output .= "\t\t\t\t<span class=\"tiFyTabooxFileshare-itemFilename\">" . wp_basename($fileurl) . "</span>\n";

            // Poids du fichiers
            if ($filesize) {
                $output .= "\t\t\t\t<span class=\"tiFyTabooxFileshare-itemFilesize\">" . self::formatBytes($filesize) . "</span>\n";
            }

            $output .= "\t\t\t\t<span class=\"tiFyTabooxFileshare-itemUploadLabel\">" . __('Télécharger',
                    'tify') . "</span>\n";
            $output .= "\t\t\t</a>\n";
            $output .= "\t\t</li>\n";
        endforeach;
        $output .= "\t</ul>\n";
        $output .= "</div>\n";

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Convertion de la Taille d'un fichier exprimée en bytes vers une données lisible
     * @maintenance Centralisation de la methode dans la librairie File
     *
     * @param int $bytes Tailles d'un fichier en bytes
     *
     * @return string
     */
    public static function formatBytes($bytes)
    {
        if ($bytes < 1024) :
            return $bytes . ' B';
        elseif ($bytes < 1048576) :
            return round($bytes / 1024, 2) . ' Ko';
        elseif ($bytes < 1073741824) :
            return round($bytes / 1048576, 2) . ' Mo';
        elseif ($bytes < 1099511627776) :
            return round($bytes / 1073741824, 2) . ' Go';
        elseif ($bytes < 1125899906842624) :
            return round($bytes / 1099511627776, 2) . ' To';
        elseif ($bytes < 1152921504606846976) :
            return round($bytes / 1125899906842624, 2) . ' Po';
        elseif ($bytes < 1180591620717411303424) :
            return round($bytes / 1152921504606846976, 2) . ' Eo';
        elseif ($bytes < 1208925819614629174706176) :
            return round($bytes / 1180591620717411303424, 2) . ' Zo';
        else :
            return round($bytes / 1208925819614629174706176, 2) . ' Yo';
        endif;
    }
}