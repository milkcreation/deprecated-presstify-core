<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\CurtainMenu;

class Walker extends \tiFy\Abstracts\TreeMenuWalker
{        
    /**
     * Ouverture du menu
     */
    public function open_menu()
    {
        return "<ul class=\"tiFyControlCurtainMenu-items tiFyControlCurtainMenu--open\">\n";
    }

    /**
     * Ouverture par défaut d'un sous-menu
     */
    public function open_submenu_default( $item, $depth = 0, $parent = '' )
    {       
        $output  = "";
        $output .= $this->getIndent( $depth ) ."\t\t<div id=\"tiFyControlCurtainMenu-panel--{$item['id']}\" class=\"tiFyControlCurtainMenu-panel\">\n";
        $output .= $this->getIndent( $depth ) ."\t\t\t<div class=\"tiFyControlCurtainMenu-panelWrapper\">\n";
        $output .= $this->getIndent( $depth ) ."\t\t\t\t<div class=\"tiFyControlCurtainMenu-panelContainer\">\n";
        if( $title = $this->getItemAttr( $parent, 'title' ) ) :
            $output .= $this->getIndent( $depth ) ."\t\t\t\t\t<h2 class=\"tiFyControlCurtainMenu-panelTitle\">{$title}</h2>\n";
        endif;
        $output .= $this->getIndent( $depth ) ."\t\t\t\t\t<a href=\"#tiFyControlCurtainMenu-panel--{$item['id']}\" class=\"tiFyControlCurtainMenu-panelBack\" data-toggle=\"curtain_menu-back\">". __( 'Retour', 'Theme' ) ."</a>\n";
        
        $output .= $this->getIndent( $depth ) ."\t\t\t\t\t<ul class=\"tiFyControlCurtainMenu-items tiFyControlCurtainMenu-items--depth{$depth}\">\n";
        
        return $output;
    }
    
    /**
     * Ouverture par défaut d'un élément
     */
    public function open_item_default( $item, $depth = 0, $parent = '' )
    {        
        return $this->getIndent( $depth ) ."\t\t\t\t\t\t<li class=\"tiFyControlCurtainMenu-item tiFyControlCurtainMenu-item--{$item['id']} tiFyControlCurtainMenu-item--depth{$depth}". ( $item['has_children'] ? ' tiFyControlCurtainMenu-item--hasChildren' : '' )."\">\n";
    }
        
    /**
     * Fermeture par défaut d'un sous-menu
     */
    public function close_submenu_default( $item, $depth = 0, $parent = '' )
    {        
        $output  = "";
        $output .= $this->getIndent( $depth ) ."\t\t\t\t\t</ul>\n";
        $output .= $this->getIndent( $depth ) ."\t\t\t\t</div>\n";
        $output .= $this->getIndent( $depth ) ."\t\t\t</div>\n";
        $output .= $this->getIndent( $depth ) ."\t\t</div>\n";
        
        return $output;
    }
    
    /**
     * Rendu par défaut d'un élément
     */
    public function item_default( $item, $depth = 0, $parent = '' )
    {
        return ! empty( $item['content'] ) ? $item['content'] : $item['title'];
    } 
}