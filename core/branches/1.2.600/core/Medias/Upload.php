<?php
namespace tiFy\Core\Medias;

use tiFy\Lib\File;
use tiFy\Lib\Checker;

class Upload extends \tiFy\App
{
    /**
     * Attributs du répertoire d'upload
     * @see \wp_upload_dir()     *
     * @var array {
     *      @type string $path
     *      @type string $url ,
     *      @type string $subdir
     *      @type string $basedir
     *      @type string $baseurl
     *      @type bool $error
     * }
     *
     * @return array
     */
    protected $UploadDir = [];

    /**
     * Metadonnées d'attachement
     * @var mixed
     */
    protected $AttachmentMetadata = [];

    /**
     *
     */
    protected $UniqueFilename = '';

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Réinitialisation
        $this->reset();
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Personnalisation des attributs du repertoire d'upload
     * @see \wp_upload_dir()
     *
     * @param array $upload_dir {
     *
     *      @param string $path
     *      @param string $url ,
     *      @param string $subdir
     *      @param string $basedir
     *      @param string $baseurl
     *      @param bool $error
     * }
     *
     * @return array
     */
    public function upload_dir($upload_dir)
    {
        if (!empty($this->UploadDir)) :
            $upload_dir = $this->UploadDir;
        endif;

        return $upload_dir;
    }

    /**
     *
     */
    public function wp_generate_attachment_metadata($metadata, $attachment_id)
    {
        if (!empty($this->AttachmentMetadata)) :
            foreach ($this->AttachmentMetadata as $meta_key => $meta_value) :
                $metadata[$meta_key] = $meta_value;
            endforeach;
        endif;

        return $metadata;
    }

    /**
     *
     */
    public function wp_unique_filename($filename2, $ext, $dir, $unique_filename_callback)
    {
        if (!empty($this->UniqueFilename)) :
            $filename2 = $this->UniqueFilename;
        endif;

        return $filename2;
    }

    /**
     * CONTROLEURS
     */
    /**
     *
     */
    public function reset()
    {
        // Réinitialisation des attributs de configuration
        $this->UploadDir = [];
        $this->AttachmentMetadata = [];
        $this->UniqueFilename = '';

        // Réinitialisation des filtres de surcharge Wordpress
        remove_filter('upload_dir', [$this, 'upload_dir'], 10);
        remove_filter('wp_generate_attachment_metadata', [$this, 'wp_generate_attachment_metadata'], 10);
        remove_filter('wp_unique_filename', [$this, 'wp_unique_filename'], 10);
    }

    /**
     * Import de fichier dans la médiathèque
     *
     * @param string $file Chemin relatif|Chemin absolu|Url du fichier d'origine
     * @param array $attrs {
     *      Attributs d'import
     *
     * @type string $name Nom du fichier dans le répertoire de destination
     * @type bool $sanitize_name Nettoyage du nom de fichier dans le répertoire de destination
     * @type bool $override Activation de l'écrasement si le fichier existe dans le répertoire de destination
     * @type int $attachment_id ID du fichier média d'attachement à remplacer
     * @type int $max_size Limitation de la taille du fichier média à importer
     * @type int $post_parent ID du post parent auquel relié le média importé
     * @type string $post_mime_type Forcage du typage du média importé (vide recommandé)
     * @type string $guid Url d'accès au fichier (vide recommandé)
     * @type string $post_title Titre du fichier média importé
     * @type string $post_content Texte de description du fichier média importé
     * @type string $post_excerpt Texte court (extrait) du fichier média importé
     * @type array $upload_dir {
     *          Attributs du répertoire de destination
     *          @see \wp_upload_dir()
     *
     * @type string $path
     * @type string $url ,
     * @type string $subdir
     * @type string $basedir
     * @type string $baseurl
     * @type bool $error
     *      }
     * }
     *
     * @return \WP_Error|int
     */
    public static function import($file, $attrs = [])
    {
        // Instanciation
        $Media = new static;

        // Valeur par défaut des attributs
        $defaults = [
            'name'           => '',
            'sanitize_name'  => true,
            'override'       => false,
            'attachment_id'  => 0,
            'max_size'       => -1,
            'post_parent'    => 0,
            'post_mime_type' => '',
            'guid'           => '',
            'post_title'     => '',
            'post_content'   => '',
            'post_excerpt'   => '',
            'upload_dir'     => ''
        ];

        // Filtrage et traitement des attributs
        $_attrs = $attrs;
        $attrs = [];
        foreach ($defaults as $key => $value) :
            $attrs[$key] = isset($_attrs[$key]) ? $_attrs[$key] : $defaults[$key];
        endforeach;

        // Contrôle d'intégrité du répertoire de stockage
        $upload_dir = $attrs['upload_dir'] ? $attrs['upload_dir'] : wp_upload_dir();
        if (!isset($upload_dir['error'])) :
            // Réinitialisation
            $Media->reset();

            // Rapport d'erreur
            return new \WP_Error('tiFy\Statics\Medias::import|UploadDirInvalid',
                __('Le format du répertoire de stockage n\'est pas valide', 'tify'));
        elseif ($upload_dir['error'] !== false) :
            // Réinitialisation
            $Media->reset();

            // Rapport d'erreur
            return new \WP_Error('tiFy\Statics\Medias::import|UploadDirError', $upload_dir['error']);
        endif;

        // Définition du nom du fichier
        $name = $attrs['name'] ? $attrs['name'] : basename($file);

        // Nettoyage du nom de fichier
        if ($attrs['sanitize_name']) :
            $name = sanitize_file_name($name);
        endif;

        // Définition du chemin d'accès au fichier source
        $is_url = false;

        // Chemin absolu local
        if (preg_match('#' . preg_quote(ABSPATH, '/') . '#', $file)) :

            // Url locale
        elseif (preg_match('#' . preg_quote(site_url(), '/') . '#', $file)) :
            $file = ABSPATH . preg_replace('#' . preg_quote(site_url(), '/') . '#', '', $file);

        // Url distante
        elseif (preg_match('#^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$#', $file)) :
            $is_url = true;

        // Chemin relatif
        elseif (file_exists(ABSPATH . '/' . ltrim($file, '/'))) :
            $file = ABSPATH . '/' . ltrim($file, '/');
        endif;

        // Récupération du contenu du fichier source
        // Depuis un fichier externe
        if ($is_url) :
            $file = rawurlencode($file);

            // Récupération de la réponse du serveur
            if (!$response = wp_remote_get($file)) :
                // Réinitialisation
                $Media->reset();

                // Rapport d'erreur
                return new \WP_Error('tiFy\Statics\Medias::import|RemoteGetFailed',
                    __('Le fichier n\'est pas disponible.', 'tify'));
            endif;

            // Traitement des attributs de la réponse
            $code = \wp_remote_retrieve_response_code($response);
            $message = \wp_remote_retrieve_response_message($response);

            if ($code != '200') :
                // Réinitialisation
                $Media->reset();

                // Rapport d'erreur
                return new \WP_Error('tiFy\Statics\Medias::import|RemoteCode200',
                    sprintf(__('Le serveur distant a retourné l\'erreur suivante : %1$d %2$s', 'tify'),
                        esc_html($message), $code));
            endif;

            $content = \wp_remote_retrieve_body($response);

        // Depuis un fichier local
        elseif (file_exists($file)) :
            $content = file_get_contents($file);

        // Le fichier n'existe pas
        else :
            // Réinitialisation
            $Media->reset();

            // Rapport d'erreur
            return new \WP_Error('tiFy\Statics\Medias::import|FileNotExist',
                __('Impossible de récupérer le fichier source', 'tify'));
        endif;

        // Définition du répertoire d'upload personnalisé
        if ($attrs['upload_dir']) :
            // Modification du répertoire d'upload
            $Media->UploadDir = $attrs['upload_dir'];
            add_filter('upload_dir', [$Media, 'upload_dir'], 10);

            // Ajout du repertoire d'upload à la metadonnées _wp_attachment_metadata
            $Media->AttachmentMetadata['upload_dir'] = $attrs['upload_dir'];
            add_filter('wp_generate_attachment_metadata', [$Media, 'wp_generate_attachment_metadata'], 10, 2);
        endif;

        // Traitement de l'écrasement de fichier
        if ($attrs['override']) :
            $Media->UniqueFilename = $name;
            add_filter('wp_unique_filename', [$Media, 'wp_unique_filename'], 10, 4);
        endif;

        // Traitement d'attachment existant
        if ($attrs['attachment_id']) :
            // Traitement du fichier d'origine
            if ($exist_path = get_attached_file($attrs['attachment_id'])) :
                // Le nom du fichier d'origine ou son répertoire de stockage est différent
                if (($name != basename($exist_path)) || (dirname($exist_path) !== $upload_dir['path'])) :
                    wp_delete_attachment($attrs['attachment_id'], true);
                    $attrs['attachment_id'] = 0;
                endif;
            endif;
        endif;

        // Traitement du fichier
        $upload = wp_upload_bits($name, 0, $content);
        if (!empty($upload['error'])) :
            // Réinitialisation
            $Media->reset();

            // Rapport d'erreur
            return new \WP_Error('tiFy\Statics\Medias::import|UnableUploadBits', $upload['error']);
        endif;

        // Définition de la taille du fichier
        $filesize = filesize($upload['file']);

        // Vérifie si le fichier est pas vide
        if (0 == $filesize) :
            // Suppression du fichier importé
            @unlink($upload['file']);

            // Réinitialisation
            $Media->reset();

            // Rapport d'erreur
            return new \WP_Error('tiFy\Statics\Medias::import|EmptyUploadFile',
                __('Le fichier téléchargé est vide', 'tify'));
        endif;

        // Vérifie si la taille du fichier n'excède pas la limite
        if (($attrs['max_size'] > 0) && ($filesize > $attrs['max_size'])) :
            // Suppression du fichier importé
            @unlink($upload['file']);

            // Réinitialisation
            $Media->reset();

            // Rapport d'erreur
            return new \WP_Error('tiFyStaticsMediasImport_MaxSizeAttempt',
                sprintf(__('Le taille du fichier dépasse la limite fixée à %s', 'tify'),
                    size_format($attrs['max_size'])));
        endif;

        // Traitement des arguments du fichier attaché
        $attachment_attrs = [
            'ID'             => $attrs['attachment_id'],
            'post_mime_type' => $attrs['post_mime_type'] ? $attrs['post_mime_type'] : $upload['type'],
            'guid'           => $attrs['guid'] ? $attrs['guid'] : $upload['url'],
            'post_parent'    => $attrs['post_parent'],
            'post_title'     => $attrs['post_title'] ? $attrs['post_title'] : sanitize_title($name),
            'post_content'   => $attrs['post_content'],
            'post_excerpt'   => $attrs['post_excerpt']
        ];
        $attachment_id = wp_insert_attachment($attachment_attrs, $upload['file']);

        if (!\is_wp_error($attachment_id)) :
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            \wp_update_attachment_metadata($attachment_id,
                \wp_generate_attachment_metadata($attachment_id, $upload['file']));
        endif;

        // Réinitialisation
        $Media->reset();

        return $attachment_id;
    }

    /**
     * Ajout d'un fichier attaché à un post
     *
     * @param int $post_parent Identifiant du post d'attachement en relation avec le fichier
     * @param string $file Chemin relatif|Chemin absolu|Url du fichier d'origine
     * @param array $attrs {
     *      Attributs d'ajout du fichier attaché
     *
     * @type string $name Nom du fichier dans le répertoire de destination
     * @type bool $sanitize_name Nettoyage du nom de fichier dans le répertoire de destination
     * @type bool $override Activation de l'écrasement si le fichier existe dans le répertoire de destination
     * @type string $post_title Titre du fichier média importé
     * @type string $post_content Texte de description du fichier média importé
     * @type string $post_excerpt Texte court (extrait) du fichier média importé
     * @type array $upload_dir {
     *          Attributs du répertoire du repertoire de destination
     * @see wp_upload_dir()
     *
     * @type string $path
     * @type string $url ,
     * @type string $subdir
     * @type string $basedir
     * @type string $baseurl
     * @type bool $error
     *      }
     *
     * @return \WP_Error|int
     */
    public static function addAttachedFile($post_parent, $file, $attrs = [])
    {
        // Contrôle d'intégrité du répertoire de stockage
        $upload_dir = isset($attrs['upload_dir']) ? $attrs['upload_dir'] : wp_upload_dir();
        if (!isset($upload_dir['error'])) :
            // Rapport d'erreur
            return new \WP_Error('tiFy\Statics\Medias::addAttachedFile|UploadDirInvalid',
                __('Le format du répertoire de stockage n\'est pas valide', 'tify'));
        elseif ($upload_dir['error'] !== false) :
            // Rapport d'erreur
            return new \WP_Error('tiFy\Statics\Medias::addAttachedFile|UploadDirError', $upload_dir['error']);
        endif;

        // Informations du fichier
        // Mime Type
        $finfo = wp_check_filetype($file);
        if (!$finfo['ext'] || !$finfo['type']) :
            return new \WP_Error('tiFy\Statics\Media::add|UnavailableMimeType',
                __('Impossible de récupérer les informations de typage du fichier', 'tify'));
        endif;

        // Date de modification du fichier
        if (!$filemtime = filemtime($file)) :
            return new \WP_Error('tiFy\Statics\Media::add|UnavailableFileMtime',
                __('Impossible d\'obtenir la date de modification du fichier', 'tify'));
        endif;

        // Valeur par défaut des attributs
        $defaults = [
            'name'          => '',
            'sanitize_name' => true,
            'override'      => false,
            'post_title'    => '',
            'post_content'  => '',
            'post_excerpt'  => '',
            'upload_dir'    => wp_upload_dir(),
            'update'        => true
        ];

        // Filtrage et traitement des attributs
        $_attrs = $attrs;
        $attrs = [];
        foreach ($defaults as $key => $value) :
            $attrs[$key] = isset($_attrs[$key]) ? $_attrs[$key] : $defaults[$key];
        endforeach;

        // Définition du nom du fichier
        $name = $attrs['name'] ? $attrs['name'] : basename($file);
        if ($attrs['sanitize_name']) :
            $name = sanitize_file_name($name);
        endif;

        // Définition des attributs de requête de récupération du fichier à mettre à jour
        $query_args['post_type'] = 'attachment';
        $query_args['post_status'] = 'inherit';
        $query_args['post_parent'] = $post_parent;
        $query_args['fields'] = 'ids';
        $query_args['posts_per_page'] = -1;
        $query_args['post_mime_type'] = $finfo['type'];

        // Récupération de la liste des fichiers existants attachés au post        
        $exists_query = new \WP_Query;
        $exists = $exists_query->query($query_args);
        wp_reset_query();

        $attachment_id = 0;
        foreach ($exists as $exist_id) :
            // Récupération du chemin vers le fichier
            $exist_path = get_attached_file($exist_id);

            // Vérification d'existance de la ressource du fichier existant
            if (!file_exists($exist_path)) :
                continue;
            endif;

            // Récupération du nom du fichier existant avec son extension
            if (!$exist_filename = pathinfo($exist_path, PATHINFO_BASENAME)) :
                continue;
            endif;

            // Bypass - Test de correspondance entre le fichier à important et l'existant
            if ($name !== $exist_filename) :
                continue;
            endif;

            $attachment_id = $exist_id;

            // Récupération des métadonnées wordpress du fichier existant
            $exist_metadata = wp_get_attachment_metadata($exist_id);

            // Récupération de la date de modification du fichier existant
            $exist_filemtime = !empty($exist_metadata['filemtime']) ? (int)$exist_metadata['filemtime'] : filemtime($exist_path);

            // Vérification de la correspondance du répertoire de stockage du fichier existant et du répertoire de stockage de déstination
            if (dirname($exist_path) !== $upload_dir['path']) :
            // Bypass - Le fichier existant est identique
            elseif ($exist_filemtime === $filemtime) :
                return $attachment_id;
            endif;

            // Arrêt de la boucle, la correspondance avec un fichier existant a été trouvée
            break;
        endforeach;

        // Import du fichier média
        $add_id = self::import($file, wp_parse_args([
                'post_parent'   => $post_parent,
                'attachment_id' => $attachment_id
            ], $attrs));

        if (!is_wp_error($add_id)) :
            // Mise à jour de la métadonnées de l'attachment
            \wp_update_attachment_metadata($add_id, wp_parse_args([
                    'filemtime' => $filemtime
                ], \wp_get_attachment_metadata($add_id)));
        endif;

        return $add_id;
    }

    /**
     * Définition d'un répertoire de stockage des médias
     *
     * @param string $base Chemin absolu|Chemin relatif|Url du site
     * @param string $sub Sous répertoire de stockage
     *
     * @return \wp_upload_dir()|array{
     * @type string $path
     * @type string $url
     * @type string $subdir
     * @type string $basedir
     * @type string $baseurl
     * @type bool $error
     * }
     */
    public static function uploadDir($base = null, $sub = null)
    {
        if (is_null($base) && is_null($sub)) {
            return wp_upload_dir();
        }

        if ($base) :
            $rel = File::getRelativeFilename($base);
            if (!is_null($rel)):
                $basedir = untrailingslashit(ABSPATH . $rel);

                $site_url = site_url('/');
                $baseurl = untrailingslashit($site_url . $rel);

            //@todo Repertoire de stockage en dehors du repertoire d'hébergement du site 
            /*elseif (preg_match('#^'. preg_quote($base, '/') .'(.*)#', ABSPATH, $matches)) :
                $forward_path = trim($matches[1], '/');
                $forward_parts = preg_split('#/#',$forward_path);
                $forward_url = '';
                foreach ($forward_parts as $part) :
                    $forward_url .= '../'; 
                endforeach;
                $basedir = $base;
                $baseurl = site_url('/' . $forward_url);*/ else :
                return [
                    'path'    => $base . $sub,
                    'url'     => '',
                    'subdir'  => $sub,
                    'basedir' => $base,
                    'baseurl' => '',
                    'error'   => sprintf(__('Impossible de joindre le répertoire %s', 'tify'), $base . $sub)
                ];
            endif;
        else :
            $basedir = WP_CONTENT_DIR . '/uploads';
            $baseurl = WP_CONTENT_URL . '/uploads';
        endif;

        // Traitement des attributs du repertoire de stockage
        $sub = trim($sub, '/');
        $path = untrailingslashit($basedir . '/' . $sub);
        $url = untrailingslashit($baseurl . '/' . $sub);
        $subdir = '/' . $sub;
        $error = false;

        // Création du répertoire de stockage
        if (!wp_mkdir_p($path)) :
            if (0 === strpos($basedir, ABSPATH)) :
                $error_path = str_replace(ABSPATH, '', $basedir) . $subdir;
            else :
                $error_path = basename($basedir) . $subdir;
            endif;

            $error = sprintf(__('Impossible de créer le répertoire %s', 'tify'), esc_html($error_path));
        endif;

        return compact('path', 'url', 'subdir', 'basedir', 'baseurl', 'error');
    }

    /**
     * Récupération de la source base64 d'un fichier média
     * @todo Permettre de soumettre un chemins relatif
     *
     * @param string $filename Chemin absolu | url vers le fichier
     *
     * @return string
     */
    public static function base64Src($filename)
    {
        if (Checker::isUrl($filename)) :
            $ext = pathinfo(parse_url($filename, PHP_URL_PATH), PATHINFO_EXTENSION);
        else :
            $ext = pathinfo(basename($filename), PATHINFO_EXTENSION);
        endif;

        // Bypass
        if (!in_array($ext, ['svg', 'png', 'jpg', 'jpeg'])) {
            return;
        }

        switch ($ext) :
            case 'svg' :
                $data = 'image/svg+xml';
                break;
            default :
                $data = 'image/' . $ext;
                break;
        endswitch;

        // Bypass
        if (!$content = File::getContents($filename)) {
            return;
        }

        return "data:{$data};base64," . base64_encode($content);
    }
}