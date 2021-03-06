<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\CurtainMenu;

class Nodes extends \tiFy\Lib\Nodes\Base
{
    /**
     * Définition des méthodes de surchage des greffons par ordre d'exectution
     * @var string[]
     */
    public $MethodsMap = ['parent', 'content', 'class'];

    /**
     * GREFFONS PERSONNALISES
     */
    /**
     * Attribut "title" du greffon de terme lié à une taxonomie
     *
     * @param array $node Liste des attributs de configuration du greffon
     * @param array $extras Liste des arguments de configuration globaux
     *
     * @return string
     */
    public function custom_node_title(&$node, $extras = [])
    {
        return "<a href=\"#\" class=\"tiFyControlCurtainMenu-panelTitleLink tiFyControlCurtainMenu-panelTitleLink--{$node['id']}\">{$node['title']}</a>";
    }

    /**
     * Attribut "content" du greffon de terme lié à une taxonomie
     *
     * @param array $node Attributs du greffon
     * @param array $extras Liste des arguments de configuration globaux
     *
     * @return string
     */
    public function custom_node_content(&$node, $extras = [])
    {
        return "<a href=\"#\" class=\"tiFyControlCurtainMenu-itemLink tiFyControlCurtainMenu-itemLink--{$node['id']}\">{$node['content']}</a>";
    }

    /**
     * Attribut "class" du greffon de terme lié à une taxonomie
     *
     * @param array $node Attributs du greffon
     * @param array $extras Liste des arguments de configuration globaux
     *
     * @return string
     */
    public function custom_node_class(&$node, $extras = [])
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

    /**
     * GREFFONS DE TERME DE TAXONOMY
     */
    /**
     * Attribut "title" du greffon de terme lié à une taxonomie
     *
     * @param array $node Attributs du greffon
     * @param obj $term Attributs du terme courant
     * @param array $query_args Argument de requête de récupération des termes de taxonomie
     * @param array $extras Liste des arguments de configuration globaux
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
     * @param array $extras Liste des arguments de configuration globaux
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
     * @param array $extras Liste des arguments de configuration globaux
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