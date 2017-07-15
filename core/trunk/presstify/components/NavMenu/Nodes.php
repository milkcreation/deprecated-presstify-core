<?php
/**
 * @Overrideable
 */
namespace tiFy\Components\NavMenu;

class Nodes extends \tiFy\Abstracts\Nodes
{
    /**
     * Attribut "content" d'un greffon de terme lié à une taxonomie
     */
    public function node_content( $node, $args = array() )
    {
        return isset( $node['content'] ) ? $node['content'] : '';
    }
}