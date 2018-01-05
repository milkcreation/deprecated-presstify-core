<?php
/**
 * @Overrideable
 */

namespace tiFy\Core\Control\SlickCarousel;

class Walker extends \tiFy\Lib\Walkers\Base
{
    /**
     * Récupération de la classe HTML d'un élément
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
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
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    public function default_start_content_items($item = null, $depth = 0, $parent = '')
    {
        return '';
    }
    
    /**
     * Fermeture par défaut d'une liste de contenus d'éléments
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    public function default_end_content_items($item = null, $depth = 0, $parent = '')
    {
        return '';
    } 
    
    /**
     * Ouverture par défaut d'un contenu d'élement
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    public function default_start_content_item($item = null, $depth = 0, $parent = '')
    {          
        return $this->getIndent($depth) . "<div class=\"" . $this->getItemClass($item, $depth, $parent) . "\" id=\"tiFyControlSlickCarousel-item--{$item['id']}\">\n";
    }
}