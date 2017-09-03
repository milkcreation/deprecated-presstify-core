<?php
namespace tiFy\Abstracts;

use tiFy\Deprecated\Deprecated;

abstract class TreeMenuWalker extends \tiFy\Lib\Walkers\MenuTree
{
    public function __construct()
    {
        Deprecated::add('function', '\tiFy\Abstracts\TreeMenuWalker', '1.0.384', '\tiFy\Lib\Walkers\MenuTree');
    }

    /**
     * Ouverture du menu
     */
    public function open_menu()
    {
        Deprecated::add('function', '\tiFy\Abstracts\TreeMenuWalker::open_menu', '1.0.384', '\tiFy\Lib\Walkers\MenuTree::start_content_items');
        return $this->start_content_items(null, 0, '');
    }

    /**
     * Fermeture du menu
     */
    public function close_menu()
    {
        Deprecated::add('function', '\tiFy\Abstracts\TreeMenuWalker::close_menu', '1.0.384', '\tiFy\Lib\Walkers\MenuTree::end_content_items');
        return $this->end_content_items(null, 0, '');
    }

    /**
     * Ouverture d'un sous-menu
     */
    final public function open_submenu($item, $depth, $parent)
    {
        Deprecated::add('function', '\tiFy\Abstracts\TreeMenuWalker::open_submenu', '1.0.384', '\tiFy\Lib\Walkers\MenuTree::start_content_items');
        return $this->start_content_items($item, $depth, $parent);
    }

    /**
     * Fermeture d'un sous-menu
     */
    final public function close_submenu($item, $depth, $parent)
    {
        Deprecated::add('function', '\tiFy\Abstracts\TreeMenuWalker::close_submenu', '1.0.384', '\tiFy\Lib\Walkers\MenuTree::end_content_items');
        return $this->end_content_items($item, $depth, $parent);
    }

    /**
     * Ouverture d'un élément
     */
    final public function open_item($item, $depth, $parent)
    {
        Deprecated::add('function', '\tiFy\Abstracts\TreeMenuWalker::open_item', '1.0.384', '\tiFy\Lib\Walkers\MenuTree::start_content_item');
        return $this->start_content_item($item, $depth, $parent);
    }

    /**
     * Fermeture d'un élément
     */
    final public function close_item($item, $depth, $parent)
    {
        Deprecated::add('function', '\tiFy\Abstracts\TreeMenuWalker::close_item', '1.0.384', '\tiFy\Lib\Walkers\MenuTree::end_content_item');
        return $this->end_content_item($item, $depth, $parent);
    }

    /**
     * Rendu d'un élément
     */
    final public function item($item, $depth, $parent)
    {
        Deprecated::add('function', '\tiFy\Abstracts\TreeMenuWalker::item', '1.0.384', '\tiFy\Lib\Walkers\MenuTree::content_item');
        return $this->content_item($item, $depth, $parent);
    }

    /**
     * Ouverture par défaut d'un sous-menu
     */
    public function open_submenu_default( $item, $depth = 0, $parent = '' )
    {
        Deprecated::add('function', '\tiFy\Abstracts\TreeMenuWalker::open_submenu_default', '1.0.384', '\tiFy\Lib\Walkers\MenuTree::default_start_content_items');
        return $this->default_start_content_items($item, $depth, $parent);
    }

    /**
     * Fermeture par défaut d'un sous-menu
     */
    public function close_submenu_default( $item, $depth = 0, $parent = '' )
    {
        Deprecated::add('function', '\tiFy\Abstracts\TreeMenuWalker::close_submenu_default', '1.0.384', '\tiFy\Lib\Walkers\MenuTree::default_end_content_items');
        return $this->default_end_content_items($item, $depth, $parent);
    }

    /**
     * Ouverture par défaut d'un élément
     */
    public function open_item_default($item, $depth = 0, $parent = '')
    {
        Deprecated::add('function', '\tiFy\Abstracts\TreeMenuWalker::open_item_default', '1.0.384', '\tiFy\Lib\Walkers\MenuTree::default_start_content_item');
        return $this->default_start_content_item($item, $depth, $parent);
    }

    /**
     * Fermeture par défaut d'un élément
     */
    public function close_item_default($item, $depth = 0, $parent = '')
    {
        Deprecated::add('function', '\tiFy\Abstracts\TreeMenuWalker::close_item_default', '1.0.384', '\tiFy\Lib\Walkers\MenuTree::default_end_content_item');
        return $this->default_end_content_item($item, $depth, $parent);
    }

    /**
     * Rendu par défaut d'un élément
     */
    public function item_default($item, $depth = 0, $parent = '')
    {
        Deprecated::add('function', '\tiFy\Abstracts\TreeMenuWalker::item_default', '1.0.384', '\tiFy\Lib\Walkers\MenuTree::default_content_item');
        return $this->default_content_item($item, $depth, $parent);
    }
}