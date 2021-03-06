<?php
/**
 * @Overrideable
 */

namespace tiFy\Core\Control\AccordionMenu;

class Walker extends \tiFy\Lib\Walkers\MenuTree
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

        $classes = [];
        $classes[] = 'tiFyControlAccordionMenu-item';
        $classes[] = "tiFyControlAccordionMenu-item--depth{$depth}";
        if(! empty($item['class'])) :
            $classes[] = $item['class'];
        endif;

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
        if(!$depth) :
            return "<ul class=\"tiFyControlAccordionMenu-items tiFyControlAccordionMenu-items--open\">\n";
        else :
            return $this->getIndent($depth) . "\t\t<ul class=\"tiFyControlAccordionMenu-items tiFyControlAccordionMenu-items--{$depth}\">\n";
        endif;
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
        return $this->getIndent($depth) . "\t<li class=\"" . $this->getItemClass($item, $depth, $parent) . "\">\n";
    }

    /**
     * Rendu par défaut d'un contenu d'élément
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    public function default_content_item($item = null, $depth = 0, $parent = '')
    {
        $output = "";
        $output .= "<a href=\"{$item['link']}\" class=\"tiFyControlAccordionMenu-itemLink tiFyControlAccordionMenu-itemLink--{$item['id']}\">";
        $output .= str_repeat("<span class=\"tiFyControlAccordionMenu-itemPad\"></span>", $depth);
        $output .= $item['content'];
        if ($item['has_children']) :
            $output .= "<span class=\"tiFyControlAccordionMenu-itemHandler\"></span>";
        endif;
        $output .= "</a>";

        return $output;
    }
}