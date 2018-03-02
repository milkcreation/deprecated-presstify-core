<?php

namespace tiFy\Core\Field;

use tiFy\Lib\Walkers\Base;

class WalkerOptions extends Base
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
        if (!$item) :
            return '';
        endif;

        $classes = [];
        $classes[] = 'tiFyField-itemOption';
        $classes[] = "tiFyField-itemOption--depth{$depth}";
        if (!empty($item['class'])) :
            $classes[] = $item['class'];
        endif;

        return implode(' ', $classes);
    }

    /**
     * Récupération des attributs de la balise HTML d'un élément
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    public function getItemHtmlAttrs($item = null, $depth = 0, $parent = '')
    {
        $attrs = $item['attrs'];

        $attrs['id'] = "tiFyField-itemOption--{$item['id']}";
        
        $attrs['class'] = $this->getItemClass($item, $depth, $parent);

        if ($item['group']) :
            $attrs['label'] = !empty($item['label']) ? $item['label'] : $item['id'];
        else :
            $attrs['value'] = $item['value'];
            if (!is_null($this->getAttr('selected'))) :
                foreach($this->getAttr('selected', []) as $selected) :
                    if ($selected == $attrs['value']) :
                        array_push($attrs, 'selected');
                    endif;
                endforeach;
            endif;
        endif;

        $html_attrs = [];
        foreach ($attrs as $k => $v) :
            if (is_array($v)) :
                $v = rawurlencode(json_encode($v));
            endif;
            if (is_int($k)) :
                $html_attrs[]= "{$v}";
            else :
                $html_attrs[]= "{$k}=\"{$v}\"";
            endif;
        endforeach;

        return implode(' ', $html_attrs);
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
        return "";
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
        return "";
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
        if ($item['group']) :
            return $this->getIndent($depth) . "<optgroup ". $this->getItemHtmlAttrs($item, $depth, $parent) . ">\n";
        else :
            return $this->getIndent($depth) . "<option ". $this->getItemHtmlAttrs($item, $depth, $parent) . ">\n";
        endif;

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
        if ($item['group']) :
            return $this->getIndent($depth) . "</optgroup>\n";
        else :
            return $this->getIndent($depth) . "</option>\n";
        endif;
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
        if (!$item['group']) :
            return ! empty($item['label']) ? esc_attr($item['label']) : '';
        endif;

        return '';
    }
}