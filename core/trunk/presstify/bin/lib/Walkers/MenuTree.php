<?php
namespace tiFy\Lib\Walkers;

abstract class MenuTree extends \tiFy\Lib\Walkers\Base
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
        return $this->getIndent($depth) . "\t\t<ul class=\"tiFyWalkerMenuTree-items tiFyWalkerMenuTree-items--depth{$depth}\">\n";
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
        return $this->getIndent($depth) . "\t\t</ul>\n";
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
        return $this->getIndent($depth) . "\t<li class=\"tiFyWalkerMenuTree-item tiFyWalkerMenuTree-item--depth{$depth}\">\n";
    }

    /**
     * Fermeture par défaut d'un contenu d'élement
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    public function default_end_content_item($item = null, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "\t</li>\n";
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
        return ! empty($item['content']) ? $item['content'] : '';
    } 
}