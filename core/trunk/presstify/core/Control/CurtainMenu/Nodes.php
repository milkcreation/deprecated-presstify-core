<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\CurtainMenu;

class Nodes extends \tiFy\Lib\Nodes\Base
{
    /**
     * Ordre d'éxecution des méthodes de surchage des greffons
     */
    public $MethodsMapOrder = ['parent', 'link', 'content', 'class'];

    /**
     * Attribut "title" du greffon de terme lié à une taxonomie
     *
     * @param array $node Attributs du greffon
     * @param obj $term Attributs du terme courant
     * @param array $query_args Argument de requête de récupération des termes de taxonomie
     * @param array $extras Liste des arguments globaux complémentaires (ex: selected)
     *
     * @return string
     */
    public function term_node_title(&$node, $term, $query_args = [], $extras = [])
    {
        return "<a href=\"". \get_term_link($term) ."\" class=\"tiFyControlCurtainMenu-panelTitleLink tiFyControlCurtainMenu-panelTitleLink--{$term->term_id}\">{$term->name}</a>";
    }

    /**
     * Attribut "content" du greffon de terme lié à une taxonomie
     *
     * @param array $node Attributs du greffon
     * @param obj $term Attributs du terme courant
     * @param array $query_args Argument de requête de récupération des termes de taxonomie
     * @param array $extras Liste des arguments globaux complémentaires (ex: selected)
     *
     * @return string
     */
    public function term_node_content(&$node, $term, $query_args = [], $extras = [])
    {
        return "<a href=\"". \get_term_link($term) ."\" class=\"tiFyControlCurtainMenu-itemLink tiFyControlCurtainMenu-itemLink--{$term->term_id}\">{$term->name}</a>";
    }

    /**
     * Attribut "class" du greffon de terme lié à une taxonomie
     *
     * @param array $node Attributs du greffon
     * @param obj $term Attributs du terme courant
     * @param array $query_args Argument de requête de récupération des termes de taxonomie
     * @param array $extras Liste des arguments globaux complémentaires (ex: selected)
     *
     * @return string
     */
    public function term_node_class(&$node, $term, $query_args = [], $extras = [])
    {
        $classes = [];
        if (!empty($node['has_children'])) :
            $classes[] = 'tiFyControlCurtainMenu-item--hasChildren';
        endif;

        if (!empty($node['is_ancestor'])) :
            $classes[] = 'tiFyControlCurtainMenu-item--ancestor';
        endif;

        if (!empty($node['current'])) :
            $classes[] = 'tiFyControlCurtainMenu-item--current';
        endif;

        return implode(' ', $classes);
    }
}