<?php
/**
 * @Overrideable
 */

namespace tiFy\Components\Sidebar;

class Walker extends \tiFy\Lib\Walkers\MenuTree
{
    /**
     * CONTROLEURS
     */
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
        if (! $depth) :
            return "\t\t\t\t<ul class=\"tiFySidebar-items tiFySidebar--open\">\n";
        else :
            return parent::default_start_content_items($item, $depth, $parent);
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
        return $this->getIndent( $depth ) ."\t\t\t\t\t<li id=\"tiFySidebar-node--{$item['id']}\" class=\"tiFySidebar-node tiFySidebar-node--{$item['id']}". ( $item['class'] ? ' '. $item['class'] : '') ."\">\n";
    }
}