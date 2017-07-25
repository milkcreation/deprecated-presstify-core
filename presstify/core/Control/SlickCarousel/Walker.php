<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\SlickCarousel;

class Walker extends \tiFy\Abstracts\Walker
{        
    /**
     * Ouverture par défaut d'une liste de contenus d'éléments
     */
    public function default_start_content_items( $item = null, $depth = 0, $parent = '' )
    {
        return '';
    }
    
    /**
     * Fermeture par défaut d'une liste de contenus d'éléments
     */
    public function default_end_content_items( $item = null, $depth = 0, $parent = '')
    {
        return '';
    } 
    
    /**
     * Ouverture par défaut d'un contenu d'élement
     */
    public function default_start_content_item( $item, $depth = 0, $parent = '' )
    {          
        return $this->getIndent( $depth ) ."<div class=\"tiFyControlSlickCarousel-item tiFyControlSlickCarousel-item--depth{$depth}\" id=\"tiFyControlSlickCarousel-item--{$item['id']}\">\n";
    }
}