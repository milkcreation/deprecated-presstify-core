<?php
namespace tiFy\Core\Medias;

use Symfony\Component\HttpFoundation\Request;
use tiFy\Lib\Cryptor\Cryptor;

class Download extends \tiFy\App
{
    /**
     * Classe de rappel des requête
     * @var array
     */
    private static $Request = null;

    /**
     * Liste des fichiers autorisés au téléchargement
     * @var array
     */
    private static $Allowed = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Définition de la classe de rappel des requêtes
        self::$Request = Request::createFromGlobals();

        // Définition des événements de déclenchement
        $this->appAddAction('tify_medias_download_register');
        $this->appAddAction('admin_init');
        $this->appAddAction('template_redirect');
        $this->appAddFilter('media_row_actions', null, 99, 3);
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation de l'interface d'administration
     *
     * @return void
     */
    final public function admin_init()
    {
        return $this->download();
    }

    /**
     * Chargement des templates
     *
     * @return void
     */
    final public function template_redirect()
    {
        return $this->download();
    }

    /**
     * Définition d'actions en ligne dans la liste des médias de l'interface d'administration en vue table
     *
     * @param array $actions Liste des actions existantes
     * @param \WP_Post $post Object post WP
     * @param $detached
     *
     * @return array
     */
    final public function media_row_actions($actions, $post, $detached)
    {
        // actions en ligne de téléchargement dans la liste des médias de l'interface d'administration en vue table
        $actions['tify_medias_download'] = "<a href=\"" . self::url($post->ID) . "\">" . __('Télécharger', 'tify') . "</a>";

        return $actions;
    }

    /**
     * Déclaration de permission de téléchargement
     *
     * @return void
     */
    public function tify_medias_download_register($abspath)
    {
        if (in_array($abspath, self::$Allowed)) :
            return;
        endif;

        if(!$token = self::$Request->query->get('tify_medias_download', false)) :
            return;
        endif;

        if (is_admin()) :
            if(!$_wp_nonce = self::$Request->query->get('_wpnonce', false)) :
                return;
            endif;
            if (wp_verify_nonce($_wp_nonce, "tiFyMediasDownload|{$token}")) :
                array_push(self::$Allowed, $abspath);
            endif;
        else :
            $media = Cryptor::decrypt($token);

            if (is_numeric($media)) :
                if (get_post_meta($media, '_tify_medias_download_token', true) === $token) :
                    array_push(self::$Allowed, $abspath);
                endif;
            endif;
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Url de téléchargement d'un fichier
     *
     * @param string|int $file Chemin relatif|Chemin absolue|Url|Identifiant d'un fichier de la médiathèque
     * @param array $additional_query_vars Arguments de requête complémentaires
     *
     * @return string
     */
    public static function url($file, $query_vars = [])
    {
        $vars = [];
        if (is_admin()) :
            $baseurl = wp_nonce_url(admin_url('/'), "tiFyMediasDownload|{$file}");
            $vars['tify_medias_download'] = is_int($file) ? $file : urlencode_deep($file);
        else :
            $baseurl = home_url('/');
            $token = Cryptor::encrypt($file);

            if (is_numeric($file)) :
                if ($token !== get_post_meta($file, '_tify_medias_download_token', true)) :
                    update_post_meta($file, '_tify_medias_download_token', $token);
                endif;
            endif;

            $vars['tify_medias_download'] = $token;
        endif;

        // Ajout des arguments de requête complémentaires
        $query_vars = wp_parse_args($vars, $query_vars);

        return \add_query_arg($query_vars, $baseurl);
    }

    /**
     * Déclaration des droits de permission de téléchargement d'un fichier
     *
     * @param string|int $file Chemin relatif|Url|Identifiant d'un fichier de la médiathèque
     *
     * @return void
     */
    public static function allow($file)
    {
        if (is_numeric($file)) :
            $url = wp_get_attachment_url((int)$file);
        else :
            $url = $file;
        endif;

        $rel = trim(preg_replace('/' . preg_quote(site_url('/'), '/') . '/', '', $url), '/');
        $abspath = ABSPATH . $rel;

        if (!file_exists($abspath)) :
            return;
        endif;

        if (!in_array($abspath, self::$Allowed)) :
            array_push(self::$Allowed, $abspath);
        endif;
    }

    /**
     * Téléchargement du fichier
     *
     * @return void
     */
    private function download()
    {
        if (!$media = self::$Request->query->get('tify_medias_download', false)) :
            return;
        endif;

        if (!is_admin()) :
            $media = Cryptor::decrypt($media);
        endif;

        if (is_numeric($media)) :
            $url = wp_get_attachment_url($media);
        else :
            $url = urldecode($media);
        endif;

        // L'url du fichier média n'est pas valide
        if (!isset($url)) :
            wp_die(
                '<h1>' . __('Téléchargement du fichier impossible', 'tify') . '</h1>' .
                '<p>' . __('L\'url du fichier média n\'est pas valide', 'tify') . '</p>',
                __('Impossible de trouver le fichier', 'tify'),
                404
            );
        endif;

        $relpath = trim(preg_replace('/' . preg_quote(site_url('/'), '/') . '/', '', $url), '/');
        $abspath = ABSPATH . $relpath;

        // Le fichier n'existe pas
        if (!file_exists($abspath)) :
            wp_die(
                '<h1>' . __('Téléchargement du fichier impossible', 'tify') . '</h1>' .
                '<p>' . __('Le fichier n\'existe pas', 'tify') . '</p>',
                __('Impossible de trouver le fichier', 'tify'),
                404
            );
        endif;

        // Le type du fichier est indeterminé ou non référencé
        $fileinfo = wp_check_filetype($abspath, wp_get_mime_types());
        if (empty($fileinfo['ext']) || empty($fileinfo['type'])) :
            wp_die(
                '<h1>' . __('Téléchargement du fichier impossible', 'tify') . '</h1>' .
                '<p>' . __('Le type du fichier est indeterminé ou non référencé', 'tify') . '</p>',
                __('Type de fichier erroné', 'tify'),
                400
            );
        endif;

        // Le type de fichier est interdit
        if (!in_array($fileinfo['type'], get_allowed_mime_types())) :
            wp_die(
                '<h1>' . __('Téléchargement du fichier impossible', 'tify') . '</h1>' .
                '<p>' . __('Le type de fichier est interdit', 'tify') . '</p>',
                __('Type de fichier interdit', 'tify'),
                405
            );
        endif;

        // Déclaration des permissions de téléchargement de fichier
        do_action('tify_medias_download_register', $abspath);

        // Bypass - Le téléchargement de ce fichier n'est pas autorisé
        if (!in_array($abspath, self::$Allowed)) :
            wp_die(
                '<h1>' . __('Téléchargement du fichier impossible', 'tify') . '</h1>' .
                '<p>' . __('Le téléchargement de ce fichier n\'est pas autorisé', 'tify') . '</p>',
                __('Téléchargement interdit', 'tify'),
                401
            );
        endif;

        // Définition de la taille du fichier
        $filesize = @ filesize($abspath);
        $rangefilesize = $filesize - 1;

        if (ini_get('zlib.output_compression')) :
            ini_set('zlib.output_compression', 'Off');
        endif;

        clearstatcache();
        nocache_headers();
        ob_start();
        ob_end_clean();

        header("Pragma: no-cache");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public, max-age=0");
        header("Content-Description: File Transfer");
        header("Accept-Ranges: bytes");

        if ($filesize) :
            header("Content-Length: " . (string)$filesize);
        endif;
        if ($filesize && $rangefilesize) :
            header("Content-Range: bytes 0-" . (string)$rangefilesize . "/" . (string)$filesize);
        endif;

        if (isset($fileinfo['type'])) :
            header("Content-Type: " . (string)$fileinfo['type']);
        else :
            header("Content-Type: application/force-download");
        endif;

        header("Content-Disposition: attachment; filename=" . str_replace(' ', '\\', basename($abspath)));
        //header("Content-Transfer-Encoding: {$fileinfo['type']}\n");

        @ set_time_limit(0);

        $fp = @ fopen($abspath, 'rb');
        if ($fp !== false) :
            while (!feof($fp)) :
                echo fread($fp, 8192);
            endwhile;
            fclose($fp);
        else :
            @ readfile($abspath);
        endif;
        ob_flush();

        do_action('tify_medias_download_callback', $abspath);

        exit;
    }
}