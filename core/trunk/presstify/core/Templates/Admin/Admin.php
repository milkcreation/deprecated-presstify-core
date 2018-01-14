<?php
namespace tiFy\Core\Templates\Admin;

use \tiFy\Core\Templates\Templates;

class Admin extends \tiFy\App
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des événements
        $this->appAddAction('admin_menu');
    }

    /**
     * EVENEMENTS
     */
    /**
     * Initialisation du menu d'administration
     */
    final public function admin_menu()
    {
        $menus = array();
        $submenus = array();

        foreach( (array) Templates::listAdmin() as $id => $Factory ) :
            // L'entrée de menu de doit pas apparaître
            if( $Factory->getAttr( 'admin_menu' ) === false )
                continue;

            // Définition des attributs de menu
            $defaults = array(
                'menu_slug'        => $Factory->getID(),
                'parent_slug'    => null,
                'page_title'     => $Factory->getID(),
                'menu_title'     => '',
                'capability'    => 'manage_options',
                'icon_url'         => null,
                'position'         => 99,
                'function'         => array( $Factory, $Factory->getAttr( 'render_cb' ) )
            );

            switch( $Factory->getModel() ) :
               default :
                    $defaults['menu_title'] =    $Factory->getLabel( 'menu_name' );
                    break;
                case 'EditForm' :
                case 'EditUser' :
                case 'TabooxEditUser' :
                    $defaults['menu_title'] = $Factory->getLabel( 'add_new' );
                    break;
                case 'Import' :
                    $defaults['menu_title'] = $Factory->getLabel( 'import_items' );
                    break;
                case 'TabooxOption' :
                    $defaults['menu_title'] = __( 'Options', 'tify' );
                    break;
                case 'ListTable' :
                case 'ListUser' :
                    $defaults['menu_title'] = $Factory->getLabel( 'all_items' );
                    break;
            endswitch;

            $admin_menu = wp_parse_args( $Factory->getAttr( 'admin_menu', array() ), $defaults );

            if( ! $admin_menu['parent_slug'] ) :
                $menus[$admin_menu['menu_slug']] = $admin_menu;
            else :
                $submenus[$admin_menu['parent_slug']][] = $admin_menu;
            endif;
        endforeach;

        // Déclaration des menus
        foreach( (array) $menus as $menu_slug => $menu ) :
            add_menu_page( $menu['page_title'], $menu['menu_title'], $menu['capability'], $menu_slug, $menu['function'], $menu['icon_url'], $menu['position'] );
        endforeach;

        // Déclaration des sous-menus
        foreach( (array) $submenus as $parent_slug =>  $_submenus ) :
            // Trie des sous-menus
            $submenus_ordered = array();
            foreach( $_submenus as $k => $v ) :
                $submenus_ordered[(int) $v['position']] = $v;
            endforeach;
            ksort( $submenus_ordered );

            foreach( $submenus_ordered as $position => $submenu ) :
                add_submenu_page( $parent_slug, $submenu['page_title'], $submenu['menu_title'], $submenu['capability'], $submenu['menu_slug'], $submenu['function'] );
            endforeach;
        endforeach;
    }
}