<?php
/**
 * @Overrideable
 */
namespace tiFy\Components\NavMenu;

class Walker extends \tiFy\Lib\Walkers\MenuTree
{
    /**
     * Ouverture par défaut d'une liste de contenus d'éléments
     */
    public function default_start_content_items($item = null, $depth = 0, $parent = '')
    {
        return $this->getIndent( $depth ) ."<ul class=\"tiFyNavMenu-items\">\n";
    }

    /**
     * Ouverture par défaut d'un contenu d'élement
     */
    public function default_start_content_item($item, $depth = 0, $parent = '')
    {
        return $this->getIndent( $depth ) ."\t<li class=\"tiFyNavMenu-item tiFyNavMenu-item--{$item['id']} tiFyNaMenu-item--depth{$depth}\">";
    }
}