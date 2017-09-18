<?php
/**
 * @Overrideable
 */
namespace tiFy\Components\NavMenu;

class Nodes extends \tiFy\Lib\Nodes\Base
{
    /**
     * Attribut "content" d'un greffon de terme lié à une taxonomie
     */
    public function node_content($node, $args = [])
    {
        return isset($node['content']) ? $node['content'] : '';
    }
}