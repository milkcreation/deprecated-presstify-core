<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\Tabs;

class Nodes extends \tiFy\Lib\Nodes\Base
{    
    /**
     * Attribut "title" d'un greffon
     */
    public function node_title($node, $args = [])
    {
        return isset( $node['title'] ) ? $node['title'] : '';
    }

    /**
     * Attribut "content" d'un greffon
     */
    public function node_content($node, $args = [])
    {
        return isset( $node['content'] ) ? $node['content'] : '';
    }
}