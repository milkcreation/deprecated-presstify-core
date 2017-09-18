<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\AccordionMenu;

class Nodes extends \tiFy\Lib\Nodes\Base
{
    /**
     * Ordre d'éxecution des méthodes de surchage des greffons
     */
    public $MethodsMapOrder = ['parent', 'has_children', 'link', 'content', 'class'];

    /**
     * Attribut "link" du greffon de terme lié à une taxonomie
     *
     * @param array $node Attributs du greffon
     * @param obj $term Attributs du terme courant
     * @param array $query_args Argument de requête de récupération des termes de taxonomie
     * @param array $extras Données complémentaires (ex: selected)
     *
     * @return string
     */
    public function term_node_link(&$node, $term, $query_args = [], $extras = [])
    {
        return \get_term_link($term);
    }

    /**
     * Attribut "content" du greffon de terme lié à une taxonomie
     *
     * @param array $node Attributs du greffon
     * @param obj $term Attributs du terme courant
     * @param array $query_args Argument de requête de récupération des termes de taxonomie
     * @param array $extras Données complémentaires (ex: selected)
     *
     * @return string
     */
    public function term_node_content(&$node, $term, $query_args = [], $extras = [])
    {
        return $term->name;
    }

    /**
     * Attribut "class" du greffon de terme lié à une taxonomie
     *
     * @param array $node Attributs du greffon
     * @param obj $term Attributs du terme courant
     * @param array $query_args Argument de requête de récupération des termes de taxonomie
     * @param array $extras Données complémentaires (ex: selected)
     *
     * @return string
     */
    public function term_node_class(&$node, $term, $query_args = [], $extras = [])
    {
        $classes = [];
        if (!empty($node['has_children'])) :
            $classes[] = 'tiFyControlAccordionMenu-item--hasChildren';
        endif;

        if (!empty($node['ancestor'])) :
            $classes[] = 'tiFyControlAccordionMenu-item--ancestor';
        endif;

        if (!empty($node['current'])) :
            $classes[] = 'tiFyControlAccordionMenu-item--current';
        endif;

        if(!empty($node['ancestor']) || !empty($node['current'])) :
            $classes[] = 'active';
        endif;

        return implode(' ', $classes);
    }
}