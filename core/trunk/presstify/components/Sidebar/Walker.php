<?php
/**
 * @Overrideable
 */
namespace tiFy\Components\Sidebar;

class Walker extends \tiFy\Abstracts\TreeMenuWalker
{            
    /**
     * Ouverture du menu
     */
    public function open_menu()
    {
        return "\t\t\t\t<ul class=\"tiFySidebar-items tiFySidebar--open\">\n";
    }
    
    /**
     * Ouverture par défaut d'un élément
     */
    public function open_item_default( $item, $depth = 0, $parent = '' )
    {        
        return $this->getIndent( $depth ) ."\t\t\t\t\t<li id=\"tiFySidebar-node--{$item['id']}\" class=\"tiFySidebar-node tiFySidebar-node--{$item['id']}". ( $item['class'] ? ' '. $item['class'] : '') ."\">\n";
    }
}