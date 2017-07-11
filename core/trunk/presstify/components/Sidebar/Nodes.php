<?php
/**
 * @Overrideable
 */
namespace tiFy\Components\Sidebar;

class Nodes extends \tiFy\Abstracts\Nodes
{    
    /**
     * Attribut "class" global d'un greffon personnalisé
     */
    public function node_class( $attrs = array(), $args = array() )
    {
        return ( isset( $attrs['class'] ) ) ? $attrs['class'] : '';
    }
}