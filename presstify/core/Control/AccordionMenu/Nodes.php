<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\AccordionMenu;

class Nodes extends \tiFy\Lib\Nodes\Base
{    
    /**
     * Attribut "content" d'un greffon de terme lié à une taxonomie
     */
    public function term_node_content( $term, $args = array() )
    {
        return "<a href=\"". \get_term_link( $term ) ."\" class=\"tiFyControlAccordionMenu-itemLink tiFyControlAccordionMenu-itemLink--{$term->term_id}\">{$term->name}</a>";
    }
}