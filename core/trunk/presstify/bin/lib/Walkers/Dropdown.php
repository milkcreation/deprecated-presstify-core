<?php

namespace tiFy\Lib\Walkers;

abstract class Dropdown extends \tiFy\Lib\Walkers\Base
{
    /**
     * Liste des attributs par défaut d'un élément
     * @var array()
     */
    protected $ItemDefaults     = array(
        'parent'        => '',
        'content'       => '',
        'value'         => ''
    );

    /**
     * Liste des attributs de configuration
     * @var string
     */
    protected $Attrs         = array();

    /**
     * CONTROLEURS
     */
    /**
     * Ouverture par défaut d'une liste de contenus d'éléments
     */
    public function default_start_content_items($item = null, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "<select name=\"\" class=\"tiFyWalkerDropdown-contentItems tiFyWalkerDropdown-contentItems--depth{$depth}\">\n";
    }

    /**
     * Fermeture par défaut d'une liste de contenus d'éléments
     */
    public function default_end_content_items($item = null, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "</select>\n";
    }

    /**
     * Ouverture par défaut d'un contenu d'élement
     */
    public function default_start_content_item($item, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "<option value=\"{$item['value']}\" selected=\"" . \selected($item['value'], $this->Current, false) . "\" class=\"tiFyWalkerDropdown-contentItem tiFyWalkerDropdown-contentItem--depth{$depth}\" id=\"tiFyWalkerDropdown-contentItem--{$item['id']}\">\n";
    }

    /**
     * Fermeture par défaut d'un contenu d'élement
     */
    public function default_end_content_item($item, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "</option>\n";
    }

    /**
     * Rendu par défaut d'un contenu d'élément
     */
    public function default_content_item($item, $depth = 0, $parent = '')
    {
        return ! empty($item['content']) ? $item['content'] : '';
    }
}