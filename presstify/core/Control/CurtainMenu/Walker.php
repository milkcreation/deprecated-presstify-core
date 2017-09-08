<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\CurtainMenu;

class Walker extends \tiFy\Lib\Walkers\MenuTree
{
    public function default_start_content_items($item = null, $depth = 0, $parent = '')
    {
        $output  = "";
        if(! $depth) :
            $output .= $this->getIndent($depth) . "<ul class=\"tiFyControlCurtainMenu-items tiFyControlCurtainMenu--open\">\n";
        else :
            $output .= $this->getIndent($depth ) . "\t\t<div id=\"tiFyControlCurtainMenu-panel--{$item['id']}\" class=\"tiFyControlCurtainMenu-panel\">\n";
            $output .= $this->getIndent($depth ) . "\t\t\t<div class=\"tiFyControlCurtainMenu-panelWrapper\">\n";
            $output .= $this->getIndent($depth ) . "\t\t\t\t<div class=\"tiFyControlCurtainMenu-panelContainer\">\n";
            if($title = $this->getItemAttr($parent, 'title')) :
                $output .= $this->getIndent($depth) . "\t\t\t\t\t<h2 class=\"tiFyControlCurtainMenu-panelTitle\">{$title}</h2>\n";
            endif;
            $output .= $this->getIndent($depth) . "\t\t\t\t\t<a href=\"#tiFyControlCurtainMenu-panel--{$item['id']}\" class=\"tiFyControlCurtainMenu-panelBack\" data-toggle=\"curtain_menu-back\">". __( 'Retour', 'Theme' ) ."</a>\n";

            $output .= $this->getIndent($depth) . "\t\t\t\t\t<ul class=\"tiFyControlCurtainMenu-items tiFyControlCurtainMenu-items--depth{$depth}\">\n";
        endif;

        return $output;
    }

    public function default_end_content_items($item = null, $depth = 0, $parent = '')
    {
        $output  = "";

        $output  = "";
        if(! $depth) :
            $output .= "</ul>\n";
        else :
            $output .= $this->getIndent( $depth ) ."\t\t\t\t\t</ul>\n";
            $output .= $this->getIndent( $depth ) ."\t\t\t\t</div>\n";
            $output .= $this->getIndent( $depth ) ."\t\t\t</div>\n";
            $output .= $this->getIndent( $depth ) ."\t\t</div>\n";
        endif;

        return $output;
    }

    public function default_start_content_item($item, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "\t\t\t\t\t\t<li class=\"tiFyControlCurtainMenu-item tiFyControlCurtainMenu-item--{$item['id']} tiFyControlCurtainMenu-item--depth{$depth}". ( $item['has_children'] ? ' tiFyControlCurtainMenu-item--hasChildren' : '' )."\">\n";
    }

    public function default_content_item( $item, $depth = 0, $parent = '' )
    {
        return ! empty( $item['content'] ) ? $item['content'] : $item['title'];
    } 
}