<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\Tabs;

class Nodes extends \tiFy\Abstracts\Nodes
{    
    /**
     * Attribut "title" d'un greffon
     */
    public function node_title( $node, $args = array() )
    {
        return isset( $node['title'] ) ? $node['title'] : '';
    }

    /**
     * Attribut "content" d'un greffon
     */
    public function node_content( $node, $args = array() )
    {
        return isset( $node['content'] ) ? $node['content'] : '';
    }
}