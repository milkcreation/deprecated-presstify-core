<?php
namespace tiFy\Core\Ui\Admin\Templates\Browser;

use tiFy\Core\Control\CurtainMenu\CurtainMenu;

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
        CurtainMenu::enqueue_scripts();

        \wp_enqueue_style('tiFyCoreUiAdminTemplatesBrowser', self::tFyAppUrl() . '/Browser.css', [], 171201);
        \wp_enqueue_script('tiFyCoreUiAdminTemplatesBrowser', self::tFyAppUrl() . '/Browser.js', ['jquery'], 171201);
    }

    final public function ajaxGetFolderContent()
    {
        // Initialisation des paramètres de configuration de la table
        $this->initParams();

        echo $this->get_folder_content($_POST['folder']);
        die(0);
    }

    final public function ajaxGetImagePreview()
    {
        $filename = $_POST['filename'];

        $mime_type = \mime_content_type($filename);
        $base64 = \base64_encode(file_get_contents($filename));

        wp_send_json(
            [
                'mime_type' => $mime_type,
                'data' => $base64
            ]
        );
    }

    /**
     * Préparation de la liste des éléments à afficher
     *
     * @return void
     */
    public function prepare_items() 
    {
        $this->get_menu_nodes();
    }

    /**
     * @param $dir
     * @param null $parent
     * @param int $depth
     */
    public function get_menu_nodes($dir = null, $parent = null, $depth = 0)
    {
        if ($depth >= 4) :
            return;
        endif;

        if (!$dir) :
            $dir = $this->getParam('dir');
        endif;

        $dir = rtrim($dir, '/');

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

            $this->menu_nodes[] = $attrs;
            $this->get_menu_nodes($filename, $dir, $depth+1);
        endforeach;
    }

    /**
     * @param string $dir
     */
    public function get_folder_content($dir = null)
    {
        if (!$dir) :
            $dir = $this->getParam('dir');
        endif;

        $dir = rtrim($dir, '/');

        $output = "";
        if ($filenames = glob($dir . "/*")) :
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

            $output .= "<ul class=\"BrowserFolderContent-items\">";

            if (!$this->getParam('chroot') || ($dir !== rtrim($this->getParam('dir'), '/'))) :
                $output .= "<li class=\"BrowserFolderContent-item\">";
                $output .= "<a href=\"#\" data-target=\"" . dirname($dir) . "\" class=\"BrowserFolderContent-itemLink BrowserFolderContent-itemLink--dir\">";
                $output .= '..';
                $output .= "</a>";
                $output .= "</li>";
            endif;

            foreach($filenames as $filename) :
                $is_dir = false;
                if (is_dir($filename)) :
                    $is_dir = true;
                    $icon = "<span class=\"BrowserFolderContent-itemIcon BrowserFolderContent-itemIcon--folder BrowserFolderContent-itemIcon--glyphicon dashicons dashicons-category\"></span>";
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
                            $icon = "<span class=\"BrowserFolderContent-itemIcon BrowserFolderContent-itemIcon--{$type} BrowserFolderContent-itemIcon--glyphicon dashicons dashicons-media-{$type}\"></span>";
                            break;

                        case 'image' :
                            // @see https://www.alsacreations.com/article/lire/1439-data-uri-schema.html
                            $icon = "<span class=\"BrowserFolderContent-itemIcon BrowserFolderContent-itemIcon--image\"/></span>";
                            break;

                        default :
                            $icon = "<span class=\"BrowserFolderContent-itemIcon BrowserFolderContent-itemIcon--default BrowserFolderContent-itemIcon--glyphicon dashicons dashicons-media-default\"></span>";
                            break;
                    endswitch;
                endif;

                $output .= "<li class=\"BrowserFolderContent-item\">";
                $output .= "<a href=\"#\" data-target=\"{$filename}\" class=\"BrowserFolderContent-itemLink BrowserFolderContent-itemLink--" . ($is_dir ? 'dir' : 'file') . "\">";
                $output .= $icon;
                $output .= "<span class=\"BrowserFolderContent-itemName\">" . basename($filename) . "</span>";
                $output .= "</a>";
                $output .= "</li>";
            endforeach;
            $output .= "</ul>";
        endif;

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
        <div class="BrowserMenu">
        <?php
            CurtainMenu::display(
                [
                    'nodes' => $this->menu_nodes,
                    'theme' => 'light'
                ]
            )
        ?>
        </div>
        <div class="BrowserFolderContent" style="">
            <?php echo $this->get_folder_content(); ?>
        </div>
    </div>
</div>
<?php
    }
}