<?php
namespace tiFy\Core\Ui\Admin\Templates\Browser;

use tiFy\Core\Ui\Ui;
use tiFy\Core\Control\Control;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

class Browser extends \tiFy\Core\Ui\Admin\Factory
{
    /**
     * Liste des éléments de menu
     * @var object
     */
    protected $menu_nodes = [];

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
                'dir',
                'chroot',
                'per_page'
            ]
        );

        // Définition de la liste des paramètres par défaut
        $this->setDefaultParam(
            'dir',
            WP_CONTENT_DIR . '/uploads'
        );
        $this->setDefaultParam(
            'chroot',
            true
        );
        $this->setDefaultParam(
            'per_page',
            20
        );

        $this->tFyAppAddAction('wp_ajax_tiFyCoreUiAdminTemplatesBrowser-getFolderContent', 'ajaxGetFolderContent');
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

        // Définition de l'écran courant
        $this->setScreen($current_screen);

        // Initialisation des paramètres de configuration de la table
        $this->initParams();

        // Vérification de l'habilitation d'accès à l'interface
        $this->check_user_can();

        // Exécution des actions
        $this->process_actions();

        // Préparation de la liste des éléments à afficher
        $this->prepare_items();
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    public function admin_enqueue_scripts()
    {
        parent::admin_enqueue_scripts();

        Control::enqueue_scripts('curtain_menu');
        Control::enqueue_scripts('spinkit');
        Control::enqueue_scripts('scroll_paginate');
        \wp_enqueue_style('tiFyCoreUiAdminTemplatesBrowser', self::tFyAppUrl() . '/Browser.css', [], 171201);
        \wp_enqueue_script('tiFyCoreUiAdminTemplatesBrowser', self::tFyAppUrl() . '/Browser.js', ['jquery'], 171201);
    }

    /**
     *
     */
    final public function ajaxGetFolderContent()
    {
        // Initialisation des paramètres de configuration de la table
        $this->initParams();

        echo $this->getFolderContent($_POST['folder']);
        die(0);
    }

    /**
     *
     */
    final public function ajaxGetImagePreview()
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
     * Préparation de la liste des éléments à afficher
     *
     * @return void
     */
    public function prepare_items() 
    {

    }

    /**
     *
     */
    public static function queryItems($options = [], $offset = 0)
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

        $inst = Ui::getAdmin('PixvertImport-media');
        $inst->initParams();

        $html = "";
        $complete = true;

        $dir = $query_args['dir'];
        $per_page = $inst->getParam('per_page', 20);
        if ($files = self::getFiles($dir, $offset, $per_page)) :
            foreach($files as $file) :
                $html .= $inst->getFileItem($file);
            endforeach;
        endif;

        return compact('html', 'complete');
    }

    /**
     * Récupération de la liste des fichiers d'un répertoire
     *
     * @param string $dir Chemin complet vers le répertoire
     * @param int $offset Numéro de fichier de démarrage
     * @param int $per_page Nombre de fichier par page
     *
     * @return \Symfony\Component\Finder\SplFileInfo
     */
    public static function getFiles($dir, $offset, $per_page)
    {
        $finder = new Finder();
        if ($finder->depth('== 0')->sortByName()->in($dir) ) :
            return new \LimitIterator($finder->getIterator(), $offset, $per_page);
        endif;
    }

    /**
     * Récupération de la liste des fichiers d'un répertoire
     *
     * @param string $dir
     */
    public function getFolderContent($target = null)
    {
        // Traitement du repertoire
        $dir = !$target ? $this->getParam('dir') : $this->getParam('dir') . $target;

        $output = "";
        // Affichage du fil d'ariane
        $output .= $this->getBreadcrumb($dir);

        $output .= "<div class=\"BrowserFolder-Content\">";

        // Indicateur de chargement
        $output .= "<div class=\"BrowserFolder-Spinner\">";
        $output .= Control::Spinkit(
            [
                'type' => 'spinner-pulse'
            ],
            false
        );
        $output .= "</div>";

        // Affichage de la liste des fichers du répertoire
        $output .= "<ul class=\"BrowserFolder-Files\">";

        // Lien de retour au repertoire parent
        if (!$this->getParam('chroot') || ($dir !== $this->getParam('dir'))) :
            $fs = new Filesystem();
            $output .= "<li class=\"BrowserFolder-File\">";
            $output .= "<a href=\"#\" data-target=\"" . $fs->makePathRelative(dirname($dir), $this->getParam('dir')) . "\" class=\"BrowserFolder-FileLink BrowserFolder-FileLink--dir\">";
            $output .= '..';
            $output .= "</a>";
            $output .= "</li>";
        endif;

        // Traitement des fichiers
        $offset = 0;
        $per_page = $this->getParam('per_page', 20);
        if ($files = self::getFiles($dir, $offset, $per_page)) :
            foreach($files as $file) :
                $output .= $this->getFileItem($file);
            endforeach;
        endif;

        $output .= "</ul>";
        $output .= "</div>";
        $output .= Control::ScrollPaginate(
            [
                'container_class' => 'BrowserFolder-Paginate',
                'target'          => '.BrowserFolder-Files',
                'query_args'      => ['ui' => $this->getId(), 'dir' => $dir],
                'query_items_cb'  => get_called_class() . '::queryItems'
            ],
            false
        );

        return $output;
    }

    /**
     * Récupération de l'affichage d'un fichier
     *
     * @param \Symfony\Component\Finder\SplFileInfo $file
     *
     * @return string
     */
    public function getFileItem($file)
    {
        $fs = new Filesystem();

        if ($file->isDir()) :
            $is_dir = true;
            $type = 'dir';
            $icon = "<span class=\"BrowserFolder-FileIcon BrowserFolder-FileIcon--folder BrowserFolder-FileIcon--glyphicon dashicons dashicons-category\"></span>";
        else :
            $is_dir = false;
            $type = \wp_ext2type($file->getExtension());

            switch($type) :
                case 'archive' :
                case 'audio' :
                case 'code' :
                case 'document' :
                case 'interactive' :
                case 'spreadsheet' :
                case 'text' :
                case 'video' :
                    $icon = "<span class=\"BrowserFolder-FileIcon BrowserFolder-FileIcon--{$type} BrowserFolder-FileIcon--glyphicon dashicons dashicons-media-{$type}\"></span>";
                    break;

                case 'image' :
                    $icon = "<span class=\"BrowserFolder-FileIcon BrowserFolder-FileIcon--{$type} BrowserFolder-FileIcon--glyphicon dashicons dashicons-format-image\"></span>" . Control::Spinkit(['container_class' => 'BrowserFolder-FilePreviewSpinner', 'type' => 'three-bounce'], false);
                    break;

                default :
                    $icon = "<span class=\"BrowserFolder-FileIcon BrowserFolder-FileIcon--default BrowserFolder-FileIcon--glyphicon dashicons dashicons-media-default\"></span>";
                    break;
            endswitch;
        endif;

        $output = "";
        $output .= "<li class=\"BrowserFolder-File\">";
        $output .= "<a href=\"#\" data-target=\"". $fs->makePathRelative($file->getRealPath(), $this->getParam('dir')) ."\" class=\"BrowserFolder-FileLink BrowserFolder-FileLink--" . ($is_dir ? 'dir' : 'file') . "\">";
        $output .= "<div class=\"BrowserFolder-FilePreview BrowserFolder-FilePreview--{$type}\">{$icon}</div>";
        $output .= "<span class=\"BrowserFolder-FileName\">" . $file->getRelativePathname() . "</span>";
        $output .= "</a>";
        $output .= "</li>";

        return $output;
    }


    /**
     *
     */
    public function getBreadcrumb($dir = null)
    {
        // Racine
        $root = $this->getParam('dir');
        $root = rtrim($root, '/');

        // Répertoire courant
        if (!$dir) :
            $dir = $root;
        endif;
        $dir = rtrim($dir, '/');

        $items = preg_replace("#{$root}/#", '', "{$dir}/");

        $path = $root;

        $output = "";
        $output .= "<ol class=\"BrowserFolder-Breadcrumb\">";

        $output .= "<li class=\"BrowserFolder-BreadcrumbPart BrowserFolder-BreadcrumbPart--root\">";
        $output .= "<a href=\"#\" data-target=\"{$path}\" class=\"BrowserFolder-BreadcrumbPartLink BrowserFolder-BreadcrumbPartLink--home\">";
        $output .= "<span class=\"dashicons dashicons-admin-home\"></span>";
        $output .= "</a>";
        $output .= "</li>";

        if($items) :
            foreach(explode('/', $items) as $item) :
                if (empty($item)) :
                    continue;
                endif;

                $path .= "/". $item;
                $output .= "<li class=\"BrowserFolder-BreadcrumbPart\">";
                if ($path !== $dir):
                    $output .= "<a href=\"#\" data-target=\"{$path}\" class=\"BrowserFolder-BreadcrumbPartLink\">{$item}</a>";
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
     *
     */
    public static function getFileEdit($filename)
    {
        $fs = new Filesystem();


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
    <div class="Browser Browser--grid">
        <div class="BrowserNav">
            <div class="BrowserNav-Menu">

            </div>
            <div class="BrowserNav-Edit">
                <?php //self::getFileEdit($this->getParam('dir')); ?>
            </div>
        </div>
        <div class="BrowserFolder">
            <?php echo $this->getFolderContent(); ?>
        </div>
    </div>
</div>
<?php
    }
}