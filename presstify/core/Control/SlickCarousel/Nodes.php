<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\SlickCarousel;

class Nodes extends \tiFy\Lib\Nodes\Base
{    
    /**
     * 
     * @return string
     */
    private function node_parent()
    {
        return '';
    }
    
    /**
     *
     * @return string
     */
    public function node_content($node)
    {
        return isset($node['content']) ? $node['content'] : '';
    }
}