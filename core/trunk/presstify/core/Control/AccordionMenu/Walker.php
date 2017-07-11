<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\AccordionMenu;

class Walker extends \tiFy\Abstracts\TreeMenuWalker
{            
    /**
     * Ouverture du menu
     */
    public function open_menu()
    {
        return "<ul class=\"tiFyControlAccordionMenu-items tiFyControlAccordionMenu-items--open\">\n";
    }
    
    /**
     * Ouverture par défaut d'un élément
     */
    public function open_item_default( $item, $depth = 0, $parent = '' )
    {        
        return $this->getIndent( $depth ) ."\t<li class=\"tiFyControlAccordionMenu-item tiFyControlAccordionMenu-item--{$depth}". ( $item['has_children'] ? ' tiFyControlAccordionMenu-item--hasChildren' : '' )."\">\n";
    }
    
    /**
     * Ouverture par défaut d'un sous-menu
     */
    public function open_submenu_default( $item, $depth = 0, $parent = '' )
    {
        return $this->getIndent( $depth ) ."\t\t<ul class=\"tiFyControlAccordionMenu-items tiFyControlAccordionMenu-items--{$depth}\">\n";
    }
}