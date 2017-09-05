<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\SlickCarousel;

class Walker extends \tiFy\Lib\Walkers\Base
{
    /**
     * Récupération de la classe HTML d'un élément de menu
     */
    public function getItemClass($item = null, $depth = 0, $parent = '')
    {
        // Bypass
        if(!$item)
            return '';

        $classes = array();
        $classes[] = 'tiFyControlSlickCarousel-item';
        $classes[] = "tiFyControlSlickCarousel-item--depth{$depth}";
        if(! empty($item['class']))
            $classes[] = $item['class'];

        return implode(' ', $classes);
    }

    /**
     * Ouverture par défaut d'une liste de contenus d'éléments
     */
    public function default_start_content_items($item = null, $depth = 0, $parent = '')
    {
        return '';
    }
    
    /**
     * Fermeture par défaut d'une liste de contenus d'éléments
     */
    public function default_end_content_items($item = null, $depth = 0, $parent = '')
    {
        return '';
    } 
    
    /**
     * Ouverture par défaut d'un contenu d'élement
     */
    public function default_start_content_item($item, $depth = 0, $parent = '')
    {          
        return $this->getIndent($depth) . "<div class=\"" . $this->getItemClass($item, $depth, $parent) . "\" id=\"tiFyControlSlickCarousel-item--{$item['id']}\">\n";
    }
}