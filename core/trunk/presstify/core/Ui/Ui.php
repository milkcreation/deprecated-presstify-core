<?php
namespace tiFy\Core\Ui;

final class Ui extends \tiFy\App\Core
{
    /**
     * Classe de rappel des interfaces déclarées
     * @return
     */
    public static $Factory             = [
        'admin'     => [],
        'user'      => []
    ];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        // Instanciation des contrôleurs
        //new Admin\Admin;
        //new Front\Front;

        $this->tFyAppActionAdd('init');
        $this->tFyAppActionAdd('admin_menu');
    }    
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    final public function init()
    {
        do_action('tify_ui_register');
    }

    /**
     * Initialisation du menu d'administration
     *
     * @return void
     */
    final public function admin_menu()
    {
        if (!$admin_uis = self::getAdminList()) :
            return;
        endif;

        // Pré-traitement des entrées du menu d'administration
        $menus = []; $submenus = [];
        foreach($admin_uis as $id => $admin_ui) :
            $admin_menu = $admin_ui->getAttr('admin_menu');
            if ($admin_menu === false) :
                continue;
            endif;

            if (!$admin_menu['parent_slug']) :
                $menus[$admin_menu['menu_slug']] = $admin_menu;
            else :
                $submenus[$admin_menu['parent_slug']][] = $admin_menu;
            endif;
        endforeach;

        // Déclaration des entrées principales du menu d'administration.
        if ($menus) :
            foreach ($menus as $menu_slug => $menu) :
                \add_menu_page($menu['page_title'], $menu['menu_title'], $menu['capability'], $menu_slug, $menu['function'], $menu['icon_url'], $menu['position']);
            endforeach;
        endif;

        // Déclaration des entrées secondaires du menu d'administration.
        if ($submenus) :
            foreach ($submenus as $parent_slug => $_submenus) :
                // Trie des sous-menus
                $submenus_ordered = [];
                foreach ($_submenus as $k => $v) :
                    $submenus_ordered[(int)$v['position']] = $v;
                endforeach;
                ksort($submenus_ordered);

                foreach ($submenus_ordered as $position => $submenu) :
                    \add_submenu_page($parent_slug, $submenu['page_title'], $submenu['menu_title'], $submenu['capability'], $submenu['menu_slug'], $submenu['function']);
                endforeach;
            endforeach;
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration d'un gabarit de l'interface d'administration.
     * 
     * @param string $id Identifiant de qualification unique de l'interface
     * @param array $attrs {
     *      Attributs de configuration
     *
     *      @param string $cb Nom complet de la classe de rappel du gabarit
     *      @param string $db Identifiant de base de données. posts par défaut
     *      @param \tiFy\Core\Labels\Factory|string|array $labels Identifiant des intitulés (Instance de la classe Labels ou identifiant de la classe ou liste des intitulés)
     *      @param bool|array $admin_menu {
     *          Attributs de configuration du menu d'administration (false: désactiver l'affichage)
     *
     *          @param string $menu_slug Identifiant du menu - Identifiant du template par défaut
     *          @param string $parent_slug Identifiant du menu parent pour les sous-menus uniquement.
     *          @param string $page_title Intitulé de la page
     *          @param string $menu_title Intitulé du menu - Intitulé du modèle prédéfini si vide
     *          @param string $capability Habiltation d'affichage
     *          @param string $icon_url Icone de menu (hors sous-menu : 'parent_slug' => null)
     *          @param int $position Ordre d'affichage de l'entrée de menu
     *          @param string Fonction d'affichage de la page - Factory::render() par défaut
     *      }
     *      @param array handle Tableau associatif de la liste des templates en relation
     * }
     *
     * @return \tiFy\Core\Ui\Admin\Factory
     */
    final public static function registerAdmin($id, $attrs = [])
    {
        return self::$Factory['admin'][$id] = new Admin\Factory($id, $attrs);
    }

    /**
     * Déclaration d'un gabarit de l'interface utilisateur.
     *
     * @param string $id Identifiant de qualification unique de l'interface
     * @param array $attrs {
     *      Attributs de configuration
     * }
     *
     * @return \tiFy\Core\Ui\User\Factory
     */
    public static function registerUser($id, $attrs = [])
    {
        return self::$Factory['user'][$id] = new User\Factory($id, $attrs);
    }

    /**
     * Récupération de la liste des gabarits déclarés de l'interface d'administration.
     *
     * @return \tiFy\Core\Ui\Admin\Factory[]
     */
    public static function getAdminList()
    {
        return self::$Factory['admin'];
    }

    /**
     * Récupération d'un gabarit déclaré de l'interface d'administration.
     *
     * @param string $id Identifiant de qualification de l'interface
     *
     * @return \tiFy\Core\Ui\Admin\Factory
     */
    public static function getAdmin($id)
    {
        if (isset(self::$Factory['admin'][$id])) :
            return self::$Factory['admin'][$id];
        endif;
    }

    /**
     * Récupération de la liste des gabarits déclarés de l'interface d'administration.
     *
     * @return \tiFy\Core\Ui\Admin\Factory[]
     */
    public static function getUserList()
    {
        return self::$Factory['user'];
    }

    /**
     * Récupération d'un gabarit déclaré de l'interface d'administration.
     *
     * @param string $id Identifiant de qualification de l'interface
     *
     * @return \tiFy\Core\Ui\Admin\Factory
     */
    public static function getUser($id)
    {
        if (isset(self::$Factory['user'][$id])) :
            return self::$Factory['user'][$id];
        endif;
    }
}