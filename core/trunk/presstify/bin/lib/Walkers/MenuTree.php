<?php
namespace tiFy\Lib\Walkers;

abstract class MenuTree extends \tiFy\Lib\Walkers\Base
{
    /**
     * CONTROLEURS
     */
    /**
     * Ouverture par défaut d'une liste de contenus d'éléments
     */
    public function default_start_content_items($item = null, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "\t\t<ul class=\"tiFyWalkerMenuTree-items tiFyWalkerMenuTree-items--depth{$depth}\">\n";
    }

    /**
     * Fermeture par défaut d'une liste de contenus d'éléments
     */
    public function default_end_content_items($item = null, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "\t\t</ul>\n";
    }

    /**
     * Ouverture par défaut d'un contenu d'élement
     */
    public function default_start_content_item($item, $depth = 0, $parent = '')
    {        
        return $this->getIndent($depth) . "\t<li class=\"tiFyWalkerMenuTree-item tiFyWalkerMenuTree-item--depth{$depth}\">\n";
    }

    /**
     * Fermeture par défaut d'un contenu d'élement
     */
    public function default_end_content_item($item, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "\t</li>\n";
    }

    /**
     * Rendu par défaut d'un contenu d'élément
     */
    public function default_content_item($item, $depth = 0, $parent = '')
    {
        return ! empty($item['content']) ? $item['content'] : '';
    } 
}