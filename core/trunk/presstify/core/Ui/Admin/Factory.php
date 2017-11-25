<?php
namespace tiFy\Core\Ui\Admin;

use tiFy\Core\Ui\Ui;

class Factory extends \tiFy\Core\Ui\Factory
{
    /**
     * Liste des attributs de configuration des gabarits parents
     * @var array
     */
    protected $Parents = [
        'EditForm' => [

        ],
        'ListTable' => [

        ],
        'PostListTable' => [
            'db'    => 'posts'
        ],
        'UserEditForm' => [
            'db'    => 'users'
        ],
        'UserListTable' => [
            'db'    => 'users'
        ],
    ];

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation de l'interface d'administration
     *
     * @return void
     */
    public function admin_init()
    {
        if ($admin_menu = $this->getAttr('admin_menu')) :
            // Récupération de l'identifiant d'affichage de la page
            $hookname = \get_plugin_page_hookname($admin_menu['menu_slug'], $admin_menu['parent_slug']);
            $this->setAttr('hookname', $hookname);

            // Récupération de l'url d'affichage de la page
            $base_uri = \menu_page_url($admin_menu['menu_slug'], false);
            $this->setAttr('base_uri', $base_uri);
        else :
            $hookname = false;
            $base_uri = false;
        endif;

        // Déclenchement de l'événenement d'initialisation de l'interface d'administration dans le gabarit
        if ($template = $this->getTemplate()) :
            $template->setAttr('hookname', $hookname);
            $template->setAttr('base_uri', $base_uri);
            $template->admin_init();
        endif;
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
        if ($current_screen->id !== $this->getAttr('hookname')) :
            return;
        endif;

        // Déclaration de la liste des événements à declencher
        $this->tFyAppActionAdd('admin_enqueue_scripts');
        $this->tFyAppActionAdd('admin_notices');

        // Déclenchement de l'événenement d'affichage de l'écran courant dans le gabarit
        if ($template = $this->getTemplate()) :
            $template->current_screen($current_screen);
        endif;
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    public function admin_enqueue_scripts()
    {
        // Déclenchement de l'événenement de mise en file des scripts de l'interface d'administration dans le gabarit
        if ($template = $this->getTemplate()) :
            $template->admin_enqueue_scripts();
        endif;
    }

    /**
     * Affichage des notifications de l'interface d'administration
     *
     * return void|string
     */
    public function admin_notices()
    {
        // Déclenchement de l'événenement de mise en file des scripts de l'interface d'administration dans le gabarit
        if ($template = $this->getTemplate()) :
            $template->admin_notices();
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Inititalisation
     */
    public function tFyAppOnInit()
    {
        // Déclaration de la liste des événements à declencher
        $this->tFyAppActionAdd('admin_init');
        $this->tFyAppActionAdd('current_screen');
    }

    /**
     * Traitement des attributs de configuration
     *
     * @param array $attrs Tableau associatif des attributs de configuration à traiter
     *
     * @return array
     */
    final public function parseAttrs($attrs = [])
    {
        $attrs = parent::parseAttrs($attrs);

        if(!isset($attrs['admin_menu']) || ($attrs['admin_menu'] === true)) :
            $attrs['admin_menu'] = [];
        endif;

        if ($attrs['admin_menu'] !== false) :
            // Définition des attributs par défaut de menu
            $defaults = [
                'menu_slug'   => $this->getId(),
                'parent_slug' => null,
                'page_title'  => $this->getId(),
                'menu_title'  => '',
                'capability'  => 'manage_options',
                'icon_url'    => null,
                'position'    => 99,
                'function'    => [$this, 'render']
            ];

            // Définition des intitulés de menu par défaut
            $labels = $attrs['labels'];
            switch ($this->getParentId()) :
                default :
                    $defaults['menu_title'] = $labels->get('menu_name');
                    break;
                case 'EditForm' :
                case 'EditUser' :
                case 'TabooxEditUser' :
                    $defaults['menu_title'] = $labels->get('add_new');
                    break;
                case 'Import' :
                    $defaults['menu_title'] = $labels->get('import_items');
                    break;
                case 'TabooxOption' :
                    $defaults['menu_title'] = __('Options', 'tify');
                    break;
                case 'ListTable' :
                case 'ListUser' :
                    $defaults['menu_title'] = $labels->get('all_items');
                    break;
            endswitch;

            $attrs['admin_menu'] = \wp_parse_args($attrs['admin_menu'], $defaults);
        endif;

        return $attrs;
    }

    /**
     * Récupération de la liste des classes de rappel des gabarits de traitement externe
     *
     * @return array|\tiFy\Core\Ui\Admin\Factory[]
     */
    final public function getHandleList()
    {
        if (!$handle_templates = $this->getAttr('handle')) :
            return [];
        endif;

        $handle = [];
        foreach ($handle_templates as $task => $id) :
            if ($factory = Ui::getAdmin($id)) :
                $handle[$task] = $factory;
            endif;
        endforeach;

        return $handle;
    }
}