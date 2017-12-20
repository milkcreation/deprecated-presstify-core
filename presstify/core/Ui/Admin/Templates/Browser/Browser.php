<?php
namespace tiFy\Core\Ui\Admin\Templates\Browser;

use tiFy\Core\Ui\Ui;
use tiFy\Core\Control\Control;
use tiFy\Lib\Stream\Local\Filesystem as Stream;
use Symfony\Component\Filesystem\Filesystem;

class Browser extends \tiFy\Core\Ui\Admin\Factory
{
    /**
     * Classe de rappel du système de fichier
     * @return \tiFy\Lib\Stream\Local\Filesystem
     */
    protected $Stream = null;

    /**
     * CONSTRUCTEUR
     *
     * @param string $id Identifiant de qualification
     * @param array $attrs Attributs de configuration
     *
     * @return void
     */
    public function __construct($id = null, $attrs = [])
    {
        parent::__construct($id, $attrs);

        // Définition de la liste des paramètres autorisés
        $this->setAllowedParamList(
            [
                'root',
                'chroot',
                'per_page'
            ]
        );

        // Définition de la liste des paramètres par défaut
        $this->setDefaultParam(
            'root',
            WP_CONTENT_DIR . '/uploads'
        );
        $this->setDefaultParam(
            'chroot',
            true
        );
        $this->setDefaultParam(
            'per_page',
            -1
        );

        // Définition des événements de déclenchement
        $this->tFyAppAddAction('wp_ajax_tiFyCoreUiAdminTemplatesBrowser-getContent', 'ajaxGetContent');
        $this->tFyAppAddAction('wp_ajax_tiFyCoreUiAdminTemplatesBrowser-getItemInfos', 'ajaxGetItemInfos');
        $this->tFyAppAddAction('wp_ajax_tiFyCoreUiAdminTemplatesBrowser-getImagePreview', 'ajaxGetImagePreview');
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Affichage de l'écran courant
     *
     * @param \WP_Screen $current_screen
     *
     * @return void
     */
    public function current_screen($current_screen)
    {
        parent::current_screen($current_screen);

        // Exécution des actions
        $this->process_actions();

        // Préparation de la liste des éléments à afficher
        //$this->prepare_items();
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    public function admin_enqueue_scripts()
    {
        parent::admin_enqueue_scripts();

        // Controleurs
        Control::enqueue_scripts('curtain_menu');
        Control::enqueue_scripts('spinkit');
        Control::enqueue_scripts('scroll_paginate');

        // Action Ajax
        \wp_enqueue_style('tiFyCoreUiAdminTemplatesBrowser', self::tFyAppUrl(get_class()) . '/Browser.css', [], 171201);
        \wp_enqueue_script('tiFyCoreUiAdminTemplatesBrowser', self::tFyAppUrl(get_class()) . '/Browser.js', ['jquery'], 171201);
    }

    /**
     * Récupération Ajax du contenu du répertoire
     *
     * @return string
     */
    public function ajaxGetContent()
    {
        // Initialisation des paramètres de configuration de la table
        $this->initParams();

        // Affichage du contenu du répertoire
        echo $this->getContent($_POST['folder']);
        die(0);
    }

    /**
     * Récupération Ajax des informations sur un élément
     *
     * @return string
     */
    public function ajaxGetItemInfos()
    {
        // Initialisation des paramètres de configuration de la table
        $this->initParams();

        // Affichage du contenu du répertoire
        echo $this->getItemInfos($_POST['item']);
        die(0);
    }

    /**
     * Récupération Ajax de l'aperçu d'une image
     *
     * @return string
     */
    public function ajaxGetImagePreview()
    {
        $filename = $_POST['filename'];

        if (!preg_match("#^". preg_quote(ABSPATH, '/') ."#", $filename)) :
            $mime_type = \mime_content_type($filename);
            $data = \base64_encode(file_get_contents($filename));
            $src = "data:image/{$mime_type};base64,{$data}";
        else :
            $rel = preg_replace("#^". preg_quote(ABSPATH, '/') ."#", '', $filename);
            $src = \site_url($rel);
        endif;

        wp_send_json(compact('src'));
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération de la classe de rappel du système de fichier
     *
     * @return \tiFy\Lib\Stream\Local\Filesystem
     */
    public function getStream()
    {
        if (!$this->Stream) :
            $this->Stream = new Stream(
                [
                    'root'     => $this->getRoot() . func_get_arg(0)
                ]
            );
        endif;

        return $this->Stream;
    }

    /**
     * Récupération du chemin vers le répertoire racine
     *
     * @return string
     */
    public function getRoot()
    {
        $root = $this->getParam('root', '');
        $root = wp_normalize_path(untrailingslashit($root)) . '/';

        return $root;
    }

    /**
     * Récupération de la liste informations des fichiers/dossiers/liens pour un chemin donné
     *
     * @param string $path Chemin vers le répertoire à traiter
     * @param int $offset Indice du premier élément à traiter
     * @param int $per_page Nombre d'éléments à traiter
     *
     * @return \SplFileInfo
     */
    public function getFilesInfos($path = '/', $offset = 0, $per_page = -1)
    {
        // Récupération des infos fichier du repertoire
        if ($filesinfos = $this->getStream($path)->listFilesInfos($path)) :
            // Trie des éléments
            // @todo Créer un itérateur de trie
            // @see Symfony\Component\Finder\Iterator\SortableIterator
            uasort($filesinfos, function ($a, $b) {
                if ($a->isDir() && $b->isFile()) :
                    return -1;
                elseif ($a->isFile() && $b->isDir()) :
                    return 1;
                endif;

                return strcmp($a->getRealpath(), $b->getRealpath());
            });

            return $filesinfos;
        endif;
    }

    /**
     * Récupération de la liste informations d'un fichier/dossier/lien d'un répertoire selon son chemin
     *
     * @param string $filename Chemin vers le fichier/dossier/lien
     *
     * @return \SplFileInfo
     */
    public function getFileInfos($filename)
    {
        return new \SplFileInfo($filename);
    }

    /**
     * Récupération de l'icone représentatif d'un fichier
     *
     * @param \SplFileInfo $fileinfo
     *
     * @return string
     */
    public function getFileIcon($fileinfo)
    {
        if ($fileinfo->isDir()) :
            $type = 'dir';
            $icon = "<span class=\"Browser-fileIcon dashicons dashicons-category\"></span>";
        else :
            $type = \wp_ext2type($fileinfo->getExtension());

            switch($type) :
                case 'archive' :
                case 'audio' :
                case 'code' :
                case 'document' :
                case 'interactive' :
                case 'spreadsheet' :
                case 'text' :
                case 'video' :
                    $icon = "<span class=\"Browser-fileIcon dashicons dashicons-media-{$type}\"></span>";
                    break;

                case 'image' :
                    $icon = "<span class=\"Browser-fileIcon dashicons dashicons-format-image\"></span>";
                    break;

                default :
                    $icon = "<span class=\"Browser-fileIcon dashicons dashicons-media-default\"></span>";
                    break;
            endswitch;
        endif;

        return $icon;
    }

    /**
     * Récupération de la liste des fichiers du repertoire courant selon la page d'affichage
     *
     * @param array $options
     * @param int $offset
     *
     * @return array
     */
    public static function queryPaginateContentFiles($options = [], $offset = 0)
    {
        /**
         * @var string $id Identifiant de qualification du controleur
         * @var string $container_id ID HTML du controleur d'affichage
         * @var string $container_class Classe HTML du controleur d'affichage
         * @var string $text Texte du controleur d'affichage
         * @var string $ajax_action Action Ajax de récupération des éléments
         * @var string $ajax_nonce Chaîne de sécurisation de l'action Ajax
         * @var array $query_args Argument de requête de récupération des éléments
         * @var array $per_page Nombre d'éléments par passe de récupération
         * @var string $target Identifiant de qualification du selecteur du DOM d'affichage de la liste des éléments
         * @var string $before_item Chaine d'ouverture d'encapsulation d'un élément
         * @var string $after_item Chaine de fermeture d'encapsulation d'un élément
         * @var string $query_items_cb Methode ou fonction de rappel de récupération de la liste des éléments
         * @var string $item_display_cb Methode ou fonction de rappel d'affichage d'un élément
         */
        extract($options);

        /**
         * @var static $inst
         */
        $inst = Ui::getAdmin('PixvertImport-media');
        $inst->initParams();

        $html = "";
        $complete = true;

        $dir = $query_args['dir'];
        $per_page = $inst->getParam('per_page', 20);
        if ($filesinfos = $inst->getFilesInfos($dir, $offset, $per_page)) :
            foreach($filesinfos as $fileinfos) :
                $html .= $inst->getContentFile($fileinfos);
            endforeach;
        endif;

        return compact('html', 'complete');
    }

    /**
     * Récupération de la liste des fichiers d'un répertoire
     *
     * @param string $rel Chemin relatif vers le repertoire courant
     *
     * @return string
     */
    public function getContent($rel = '/')
    {
        $output = "";

        // Indicateur de chargement
        $output .= $this->getContentLoader();

        // Fil d'ariane
        $output .= $this->getContentBreadcrumb($rel);

        // Contenu du répertoire
        $output .= "<div class=\"Browser-contentView Browser-contentView--grid\">";

        // Liste des fichiers du répertoire courant
        $output .= $this->getContentFileList($rel);

        $output .= "</div>";

        // Pagination
        if ($this->getParam('per_page') > 0) :
            $output = $this->getContentPagination($rel);
        endif;

        return $output;
    }

    /**
     * Affichage de l'indicateur de chargement du repertoire courant
     *
     * @return string
     */
    public function getContentLoader()
    {
        $output  = "";
        $output .= "<div class=\"Browser-contentLoader\">";
        $output .= Control::Spinkit(['type' => 'spinner-pulse'], false);
        $output .= "</div>";

        return $output;
    }

    /**
     * Récupération du fil d'arianne
     *
     * @param string $rel Chemin relatif vers le repertoire courant
     *
     * @return string
     */
    public function getContentBreadcrumb($rel = '/')
    {
        $output = "";
        $output .= "<ol class=\"Browser-contentBreadcrumb\">";
        $output .= "<li class=\"Browser-contentBreadcrumbPart BrowserFolder-BreadcrumbPart--root\">";
        $output .= "<a href=\"#\" data-target=\"/\" class=\"Browser-contentBreadcrumbPartLink\">";
        $output .= "<span class=\"dashicons dashicons-admin-home\"></span>";
        $output .= "</a>";
        $output .= "</li>";

        if($rel !== '/') :
            $target = '';
            foreach(explode('/', $rel) as $item) :
                if (empty($item) || ($item === '.')) :
                    continue;
                endif;

                $target .= $item . '/';

                $output .= "<li class=\"Browser-contentBreadcrumbPart\">";
                if ($target !== $rel):
                    $output .= "<a href=\"#\" data-target=\"{$target}\" class=\"Browser-contentBreadcrumbPartLink\">{$item}</a>";
                else :
                    $output .= $item;
                endif;
                $output .= "</li>";
            endforeach;
        endif;
        $output .= "</ol>";

        return $output;
    }

    /**
     * Affichage de la liste des fichers du répertoire courant
     *
     * @param string $rel Chemin relatif vers le repertoire courant
     *
     * @return string
     */
    public function getContentFileList($rel = '/')
    {
        $output = "";

        $output .= "<ul class=\"Browser-contentFileList\">";

        // Lien de retour au repertoire parent
        if ($rel !== '/') :
            $output .= "<li class=\"Browser-contentFile\">";
            $output .= "<a href=\"#\" data-target=\"/\" class=\"Browser-contentFileLink Browser-contentFileLink--dir\">";
            $output .= '..';
            $output .= "</a>";
            $output .= "</li>";
        endif;

        // Traitement des fichiers
        $offset = 0; $per_page = $this->getParam('per_page');
        if ($filesinfos = $this->getFilesInfos($rel, $offset, $per_page)) :
            foreach($filesinfos as $fileinfos) :
                $output .= $this->getContentFile($fileinfos);
            endforeach;
        endif;

        $output .= "</ul>";

        return $output;
    }

    /**
     * Récupération de l'affichage d'un fichier
     *
     * @param \SplFileInfo $file
     *
     * @return string
     */
    public function getContentFile($file)
    {
        $fs = new Filesystem();

        $output = "";
        $output .= "<li class=\"Browser-contentFile\">";
        $output .= "<a href=\"#\" data-target=\"" . $fs->makePathRelative($file->getRealPath(), $this->getRoot()) . "\" class=\"Browser-contentFileLink Browser-contentFileLink--" . ($file->isDir() ? 'dir' : 'file') . "\">";
        $output .= "<div class=\"Browser-contentFilePreview\">". $this->getFileIcon($file) ."</div>";
        $output .= "<span class=\"Browser-contentFileName\">" . $file->getBasename() . "</span>";
        $output .= "</a>";
        $output .= "</li>";

        return $output;
    }

    /**
     * Affichage de l'interface de navigation
     *
     * @param string $rel Chemin relatif vers le repertoire courant
     *
     * @return mixed
     */
    public function getContentPagination($rel = null)
    {
        return Control::ScrollPaginate(
            [
                'container_class' => 'Browser-contentPaginate',
                'target'          => '.BrowserFolder-Files',
                'query_args'      => [
                    'ui'    => $this->getId(),
                    'rel'   => $rel
                ],
                'query_items_cb'  => get_called_class() . '::queryPaginateContentFiles'
            ],
            false
        );
    }

    /**
     * Récupération des informations détaillées d'un élément (fichier|dossier|lien)
     *
     * @param string $rel_path Chemin relatif vers l'élément (fichier|dossier|lien)
     *
     * @return string
     */
    public function getItemInfos($rel_path = null)
    {
        // Définition du chemin absolu vers l'élément (fichier|dossier|lien)
        if(in_array($rel_path, ['./', '.', '/'])) :
            $rel_path = null;
        endif;
        $path = $rel_path ? $this->getRoot() . $rel_path : $this->getRoot();

        if(!$fileinfos = $this->getFileInfos($path)) :
            return;
        endif;

        $output  = "";
        $output .= "<div class=\"Browser-itemInfos--preview\">". $this->getFileIcon($fileinfos) ."</div>";
        $output .= "<ul class=\"Browser-itemInfosAttrList\">";
        $output .= "<li class=\"Browser-itemInfosAttr Browser-itemInfosAttr--name\"><label>nom : </label><span>" . $fileinfos->getBasename() . "</span></li>";
        $output .= "<li class=\"Browser-itemInfosAttr Browser-itemInfosAttr--type\"><label>type : </label><span>" . $fileinfos->getType() . "</span></li>";
        $output .= "<li class=\"Browser-itemInfosAttr Browser-itemInfosAttr--ext\"><label>extension : </label><span>" . $fileinfos->getExtension() . "</span></li>";
        $output .= "<li class=\"Browser-itemInfosAttr Browser-itemInfosAttr--size\"><label>taille : </label><span>" . $fileinfos->getSize() . "</span></li>";
        $output .= "<li class=\"Browser-itemInfosAttr Browser-itemInfosAttr--owner\"><label>propriétaire : </label><span>" . $fileinfos->getOwner() . "</span></li>";
        $output .= "<li class=\"Browser-itemInfosAttr Browser-itemInfosAttr--group\"><label>groupe : </label><span>" . $fileinfos->getGroup() . "</span></li>";
        $output .= "</ul>";

        return $output;
    }

    /**
     * Affichage de la page
     *
     * @return string
     */
    public function render()
    {
?>
<div class="wrap">
    <h2>
        <?php echo $this->getParam('page_title'); ?>
    </h2>
    <div class="Browser">
        <div class="Browser-sidebar">
            <div class="Browser-itemInfos">
                <?php // echo $this->getItemInfos(); ?>
            </div>
        </div>
        <div class="Browser-content">
            <?php echo $this->getContent(); ?>
        </div>
    </div>
</div>
<?php
    }
}