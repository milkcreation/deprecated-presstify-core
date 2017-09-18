<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\CurtainMenu;

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
        $classes[] = 'tiFyControlCurtainMenu-item';
        $classes[] = "tiFyControlCurtainMenu-item--{$item['id']}";
        $classes[] = "tiFyControlCurtainMenu-item--depth{$depth}";
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
        $output  = "";
        if(! $depth) :
            $output .= $this->getIndent($depth) . "<ul class=\"tiFyControlCurtainMenu-items tiFyControlCurtainMenu--open\">\n";
        else :
            if ($item['ancestor'] ||
                $item['current'] ||
                (isset($this->Attrs['selected']) && ($item['parent'] === $this->Attrs['selected']))
            ) :
                $isOpenClass = ' tiFyControlCurtainMenu-panel--open';
            else :
                $isOpenClass = '';
            endif;

            $output .= $this->getIndent($depth) . "\t\t<div id=\"tiFyControlCurtainMenu-panel--{$item['parent']}\" class=\"tiFyControlCurtainMenu-panel{$isOpenClass}\" style=\"z-index:{$depth};\">\n";
            $output .= $this->getIndent($depth) . "\t\t\t<div class=\"tiFyControlCurtainMenu-panelWrapper\">\n";
            $output .= $this->getIndent($depth) . "\t\t\t\t<div class=\"tiFyControlCurtainMenu-panelContainer\">\n";
            if($title = $this->getItemAttr($parent, 'title')) :
                $output .= $this->getIndent($depth) . "\t\t\t\t\t<h2 class=\"tiFyControlCurtainMenu-panelTitle\">{$title}</h2>\n";
            endif;
            $output .= $this->getIndent($depth) . "\t\t\t\t\t<a href=\"#tiFyControlCurtainMenu-panel--{$item['parent']}\" class=\"tiFyControlCurtainMenu-panelBack\" data-toggle=\"curtain_menu-back\">". __( 'Retour', 'Theme' ) ."</a>\n";

            $output .= $this->getIndent($depth) . "\t\t\t\t\t<ul class=\"tiFyControlCurtainMenu-items tiFyControlCurtainMenu-items--depth{$depth}\">\n";
        endif;

        return $output;
    }

    /**
     * Fermeture par défaut d'une liste de contenus d'éléments
     */
    public function default_end_content_items($item = null, $depth = 0, $parent = '')
    {
        $output  = "";

        $output  = "";
        if(! $depth) :
            $output .= "</ul>\n";
        else :
            $output .= $this->getIndent( $depth ) ."\t\t\t\t\t</ul>\n";
            $output .= $this->getIndent( $depth ) ."\t\t\t\t</div>\n";
            $output .= $this->getIndent( $depth ) ."\t\t\t</div>\n";
            $output .= $this->getIndent( $depth ) ."\t\t</div>\n";
        endif;

        return $output;
    }

    /**
     * Ouverture par défaut d'un contenu d'élement
     */
    public function default_start_content_item($item, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "\t\t\t\t\t\t<li class=\"" . $this->getItemClass($item, $depth, $parent) . "\">\n";
    }

    /**
     * Rendu par défaut d'un contenu d'élément
     */
    public function default_content_item($item, $depth = 0, $parent = '')
    {
        return ! empty($item['content']) ? $item['content'] : $item['title'];
    } 
}