<?php
/**
 * @Overrideable
 */
namespace tiFy\Components\NavMenu;

class Walker extends \tiFy\Lib\Walkers\MenuTree
{
    /**
     * Récupération de la classe HTML d'un élément de menu
     */
    public function getItemClass($item = null, $depth = 0, $parent = '')
    {
        // Bypass
        if(!$item)
            return '';

        $classes = [];
        $classes[] = 'tiFyNavMenu-item';
        $classes[] = "tiFyNavMenu-item--{$item['id']}";
        $classes[] = "tiFyNaMenu-item--depth{$depth}";
        if(! empty($item['class'])) :
            $classes[] = $item['class'];
        endif;

        return implode(' ', $classes);
    }

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
        return $this->getIndent( $depth ) ."\t<li class=\"" . $this->getItemClass($item, $depth, $parent) . "\">";
    }
}