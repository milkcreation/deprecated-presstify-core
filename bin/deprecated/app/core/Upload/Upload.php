<?php
namespace tiFy\Core\Upload;

use \tiFy\Lib\Cryptor\Cryptor;

class Upload extends \tiFy\App
{
    /**
     * Liste des fichiers autorisés
     * @var array
     */
    private static $AllowedFiles = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Définition des événements de déclenchement
        $this->appAddAction('template_redirect');
        $this->appAddAction('tify_upload_register');
        $this->appAddFilter('query_vars');
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Définition des arguments de requête
     *
     * @param array $vars Liste des arguments de requête existant
     *
     * @return array
     */
    final public function query_vars($vars)
    {
        $vars[] = 'file_upload_url';
        $vars[] = 'file_upload_media';

        return $vars;
    }

    /**
     * Page de téléchargement du fichier
     *
     * @return string
     */
    final public function template_redirect()
    {
        if (!self::Get()) :
            return;
        endif;

        $upload_url = false;
        if ($upload_url = self::Get('url')) :
            $upload_url = urldecode($upload_url);
        elseif ($attachment_id = self::Get('media')) :
            $upload_url = wp_get_attachment_url($attachment_id);
        endif;

        // L'url vers le fichier n'est pas valide
        if (!$upload_url) :
            wp_die(
                __(
                    '<h1>Téléchargement du fichier impossible</h1>' .
                    '<p>L\'url vers le fichier n\'est pas valide.</p>',
                    'tify'
                ),
                __('Impossible de trouver le fichier', 'tify'),
                404
            );
        endif;

        $relpath = trim(preg_replace('/' . preg_quote(site_url(), '/') . '/', '', $upload_url), '/');
        $abspath = ABSPATH . $relpath;

        // Le fichier n'existe pas
        if (!file_exists($abspath)) :
            wp_die(
                __(
                    '<h1>Téléchargement du fichier impossible</h1>' .
                    '<p>Le fichier n\'existe pas.</p>',
                    'tify'
                ),
                __('Impossible de trouver le fichier', 'tify'),
                404
            );
        endif;

        // Le type du fichier est indeterminé ou non référencé
        $fileinfo = wp_check_filetype($abspath, wp_get_mime_types());
        if (empty($fileinfo['ext']) || empty($fileinfo['type'])) :
            wp_die(
                __(
                    '<h1>Téléchargement du fichier impossible</h1>' .
                    '<p>Le type du fichier est indeterminé ou non référencé.</p>',
                    'tify'
                ),
                __('Type de fichier erroné', 'tify'),
                400
            );
        endif;

        // Le type de fichier est interdit
        if (!in_array($fileinfo['type'], get_allowed_mime_types())) :
            wp_die(
                __(
                    '<h1>Téléchargement du fichier impossible</h1>' .
                    '<p>Le type de fichier est interdit.</p>',
                    'tify'
                ),
                __('Type de fichier interdit', 'tify'),
                405
            );
        endif;

        //
        do_action('tify_upload_register', $abspath);

        // Bypass - Le téléchargement de ce fichier n'est pas autorisé
        if (!in_array($abspath, self::$AllowedFiles)) :
            wp_die(
                __(
                    '<h1>Téléchargement du fichier impossible</h1>' .
                    '<p>Le téléchargement de ce fichier n\'est pas autorisé.</p>',
                    'tify'
                ),
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

        do_action('tify_upload_callback', $abspath);

        exit;
    }

    /**
     *
     */
    public function tify_upload_register()
    {
        if (!$file = self::Get('media')) :
            return;
        endif;
        if (!isset($_REQUEST['token'])) :
            return;
        endif;
        $token = get_post_meta($file, '_tify_upload_token', true);

        if ($_REQUEST['token'] === $token) :
            self::Register($file);
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration des droits de permission de téléchargement d'un fichier
     *
     * @param string|int $file Chemin relatif|Chemin absolue|Url|Identifiant d'un fichier de la médiathèque
     *
     * @return void
     */
    public static function register($file)
    {
        $_file = false;
        if (is_numeric($file)) :
            $abspath = get_attached_file((int)$file);

            if (file_exists($abspath)) :
                $_file = $abspath;
            endif;
        else :
            if ($relpath = preg_replace('/' . preg_quote(ABSPATH, '/') . '/', '', $file)) :
            else :
                $relpath = preg_replace('/' . preg_quote(site_url(), '/') . '/', '', $file);
            endif;

            $abspath = ABSPATH . trim($relpath, '/');
            if (file_exists($abspath)) :
                $_file = $abspath;
            endif;
        endif;

        if ($_file && !in_array($_file, self::$AllowedFiles)) :
            array_push(self::$AllowedFiles, $_file);
        endif;
    }

    /** == Récupération du fichier à télécharger == **/
    public static function Get($type = null)
    {
        $file = null;

        if (!$type) :
            if ($file = get_query_var('file_upload_url', false)) :
                $file = Cryptor::decrypt($file);
            //$file = preg_replace( '/^'. preg_quote( site_url(), '/') .'|^'. preg_quote( ABSPATH, '/') .'/', '', $file );
            elseif ($file = (int)get_query_var('file_upload_media', 0)) :
            endif;
        elseif ($type === 'url') :
            if ($file = get_query_var('file_upload_url', false)) :
                $file = Cryptor::decrypt($file);
                $file = preg_replace('/^' . preg_quote(site_url(), '/') . '|^' . preg_quote(ABSPATH, '/') . '/', '',
                    $file);
            endif;
        elseif ($type === 'media') :
            $file = (int)get_query_var('file_upload_media', 0);
        endif;

        return $file;
    }

    /**
     * Url de téléchargement d'un fichier
     *
     * @param string|int $file Chemin relatif|Chemin absolue|Url|Identifiant d'un fichier de la médiathèque
     * @param array $query_vars
     *
     * @return string
     */
    public static function Url($file, $query_vars = [])
    {
        $vars = [];
        if (is_numeric($file)) :
            $vars['file_upload_media'] = $file;

            $token = Cryptor::encrypt($file);
            if ($token !== get_post_meta($file, '_tify_upload_token', true)) :
                update_post_meta($file, '_tify_upload_token', $token);
            endif;
            $vars['token'] = $token;
        else :
            $file = urlencode_deep(Cryptor::encrypt($file));
            $vars = ['file_upload_url' => $file];
        endif;
        $query_vars = wp_parse_args($vars, $query_vars);

        return add_query_arg($query_vars, site_url('/'));
    }
}