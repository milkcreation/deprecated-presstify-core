<?php
/**
 * @Overrideable
 */
namespace tiFy\Components\NavMenu;

class Walker extends \tiFy\Abstracts\TreeMenuWalker
{            
    /**
     * Ouverture du menu
     */
    public function open_menu()
    {
        return $this->getIndent( 0 ) ."<ul class=\"tiFyNavMenu-items tiFyNavMenu-items--open\">\n";
    }
    
    /**
     * Ouverture par défaut d'un élément
     */
    public function open_item_default( $item, $depth = 0, $parent = '' )
    {
        return $this->getIndent( $depth ) ."\t<li class=\"tiFyNavMenu-item tiFyNavMenu-item--{$item['id']} tiFyNaMenu-item--depth{$depth}\">";
    }
    
    /**
     * Ouverture par défaut d'un sous-menu
     */
    public function open_submenu_default( $item, $depth = 0, $parent = '' )
    {
        return $this->getIndent( $depth ) ."<ul class=\"tiFyNavMenu-items\">\n";
    }
}