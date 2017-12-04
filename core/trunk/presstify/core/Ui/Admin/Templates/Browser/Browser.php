<?php
namespace tiFy\Core\Ui\Admin\Templates\Browser;

use tiFy\Core\Control\Control;

class Browser
{
    // Application TiFy
    use \tiFy\App\Traits\App;

    // Fonctions d'aide
    use \tiFy\Core\Ui\Common\Traits\Helpers;

    // Attributs de configuration
    use \tiFy\Core\Ui\Common\Traits\Attrs;
    use \tiFy\Core\Ui\Admin\Traits\Attrs;

    // Paramètres
    use \tiFy\Core\Ui\Common\Traits\Params;
    use \tiFy\Core\Ui\Admin\Traits\Params;

    // Evénements
    use \tiFy\Core\Ui\Common\Traits\Events;
    use \tiFy\Core\Ui\Admin\Traits\Events;

    // Actions
    use \tiFy\Core\Ui\Common\Traits\Actions;
    use \tiFy\Core\Ui\Admin\Traits\Actions;

    // Notifications
    use \tiFy\Core\Ui\Common\Traits\Notices;

    /**
     * Liste des éléments de menu
     * @var object
     */
    protected $menu_nodes = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct($id, $attrs)
    {
        // Déclaration de l'app tiFy
        self::_tFyAppRegister($this);

        //Définition des attributs de configuration
        $this->setId($id);
        $this->setAttrList($attrs);

        // Définition de la liste des paramètres autorisés
        $this->setAllowedParamList(
            [
                'dir',
                'chroot'
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
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public function init()
    {
        $this->tFyAppAddAction('wp_ajax_tiFyCoreUiAdminTemplatesBrowser-getFolderContent', 'ajaxGetFolderContent');
        $this->tFyAppAddAction('wp_ajax_tiFyCoreUiAdminTemplatesBrowser-getImagePreview', 'ajaxGetImagePreview');
    }

    /**
     * Affichage de l'écran courant
     *
     * @param \WP_Screen $current_screen
     *
     * @return void
     */
    public function current_screen($current_screen)
    {
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
        Control::enqueue_scripts('curtain_menu');
        Control::enqueue_scripts('spinkit');
        \wp_enqueue_style('tiFyCoreUiAdminTemplatesBrowser', self::tFyAppUrl() . '/Browser.css', [], 171201);
        \wp_enqueue_script('tiFyCoreUiAdminTemplatesBrowser', self::tFyAppUrl() . '/Browser.js', ['jquery'], 171201);
    }

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

        if (!$rel = preg_replace("#^". preg_quote(ABSPATH, '/') ."#", '', $filename)) :
            $mime_type = \mime_content_type($filename);
            $data = \base64_encode(file_get_contents($filename));
            $src = "data:image/{$mime_type};base64,{$data}";
        else :
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
     * @param $dir
     * @param null $parent
     * @param int $depth
     */
    public function getNavMenuNodes($dir = null, $parent = null, $depth = 0)
    {
        if ($depth >= 4) :
            return;
        endif;

        if (!$dir) :
            $dir = $this->getParam('dir');
        endif;

        $dir = rtrim($dir, '/');

        $nav_menu_nodes = [];
        foreach(glob($dir . "/*") as $filename) :
            if (!is_dir($filename)) :
                continue;
            endif;

            $attrs['id'] = $filename;
            $attrs['title'] = basename($filename);
            $attrs['content'] = basename($filename);
            $attrs['current'] = 0;
            $attrs['is_ancestor'] = 0;
            $attrs['has_children'] = 0;
            if ($parent) :
                $attrs['parent'] = dirname($filename);
            endif;

            $nav_menu_nodes[] = $attrs;

            if ($childs = $this->getNavMenuNodes($filename, $dir, $depth+1)) :
                foreach ($childs as $child) :
                    array_push($nav_menu_nodes, $child);
                endforeach;
            endif;
        endforeach;

        return $nav_menu_nodes;
    }

    /**
     * Récupération de la liste des fichiers d'un répertoire
     *
     * @param string $dir
     */
    public function getFolderContent($dir = null)
    {
        // Traitement du repertoire
        if (!$dir) :
            $dir = $this->getParam('dir');
        endif;
        $dir = rtrim($dir, '/');

        // Récupération de la liste des fichiers
        $filenames = glob($dir . "/*");

        $output = "";

        $output .= "<div class=\"BrowserFolder-Content\">";

        // Affichage du fil d'ariane
        $output .= $this->getBreadcrumb($dir);

        // Indicateur de chargement
        $output .= "<div class=\"BrowserFolder-Spinner\">";
        $output .= Control::spinkit(['type' => 'spinner-pulse'], false);
        $output .= "</div>";

        // Affichage de la liste des fichers du répertoire
        $output .= "<ul class=\"BrowserFolder-Files\">";

        // Lien de retour au repertoire parent
        if (!$this->getParam('chroot') || ($dir !== rtrim($this->getParam('dir'), '/'))) :
            $output .= "<li class=\"BrowserFolder-File\">";
            $output .= "<a href=\"#\" data-target=\"" . dirname($dir) . "\" class=\"BrowserFolder-FileLink BrowserFolder-FileLink--dir\">";
            $output .= '..';
            $output .= "</a>";
            $output .= "</li>";
        endif;

        // Traitement des fichiers
        if ($filenames) :
            // Trie de la liste des fichiers
            usort($filenames, function ($a, $b) {
                $aIsDir = is_dir($a);
                $bIsDir = is_dir($b);
                if ($aIsDir === $bIsDir)
                    return strnatcasecmp($a, $b); // both are dirs or files
                elseif ($aIsDir && !$bIsDir)
                    return -1; // if $a is dir - it should be before $b
                elseif (!$aIsDir && $bIsDir)
                    return 1; // $b is dir, should be before $a
            });

            foreach($filenames as $filename) :
                $is_dir = false;
                if (is_dir($filename)) :
                    $is_dir = true;
                    $icon = "<span class=\"BrowserFolder-FileIcon BrowserFolder-FileIcon--folder BrowserFolder-FileIcon--glyphicon dashicons dashicons-category\"></span>";
                else :
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    $type = wp_ext2type($ext);
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
                            // @see https://www.alsacreations.com/article/lire/1439-data-uri-schema.html
                            $icon = "<span class=\"BrowserFolder-FileIcon BrowserFolder-FileIcon--image\"><div class=\"BrowserFolder-FileIconSpinner\">" . Control::spinkit(['type' => 'three-bounce'], false) . "</div></span>";
                            break;

                        default :
                            $icon = "<span class=\"BrowserFolder-FileIcon BrowserFolder-FileIcon--default BrowserFolder-FileIcon--glyphicon dashicons dashicons-media-default\"></span>";
                            break;
                    endswitch;
                endif;

                $output .= "<li class=\"BrowserFolder-File\">";
                $output .= "<a href=\"#\" data-target=\"{$filename}\" class=\"BrowserFolder-FileLink BrowserFolder-FileLink--" . ($is_dir ? 'dir' : 'file') . "\">";
                $output .= $icon;
                $output .= "<span class=\"BrowserFolder-FileName\">" . basename($filename) . "</span>";
                $output .= "</a>";
                $output .= "</li>";
            endforeach;
        endif;
        $output .= "</ul>";
        $output .= "</div>";

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

        $output .= "<li class=\"BrowserFolder-BreadcrumbPart\">";
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
     * Affichage de la page
     *
     * @return string
     */
    public function render()
    {
?>
<div class="wrap">
    <h2>
        <?php echo $this->getParam('page_title');?>
    </h2>
    <div class="Browser Browser--grid">
        <div class="BrowserNav">
            <div class="BrowserNav-Menu">
            <?php
            /*
             Control::curtain_menu(
                [
                    'nodes' => $this->getNavMenuNodes(),
                    'theme' => 'light'
                ]
            );
            */
            ?>
            </div>
            <div class="BrowserNav-Edit">
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