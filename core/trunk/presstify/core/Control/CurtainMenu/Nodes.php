<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\CurtainMenu;

class Nodes extends \tiFy\Lib\Nodes\Base
{    
    /**
     * Attribut "title" d'un greffon de terme lié à une taxonomie
     */
    public function term_node_title( $term, $args = array() )
    {
        return "<a href=\"". \get_term_link( $term ) ."\" class=\"tiFyControlCurtainMenu-panelTitleLink tiFyControlCurtainMenu-panelTitleLink--{$term->term_id}\">{$term->name}</a>";
    }
    
    /**
     * Attribut "content" d'un greffon de terme lié à une taxonomie
     */
    public function term_node_content( $term, $args = array())
    {
        return "<a href=\"". \get_term_link( $term ) ."\" class=\"tiFyControlCurtainMenu-itemLink tiFyControlCurtainMenu-itemLink--{$term->term_id}\">{$term->name}</a>";
    }
}