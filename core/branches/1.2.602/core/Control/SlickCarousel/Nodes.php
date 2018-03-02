<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\SlickCarousel;

class Nodes extends \tiFy\Lib\Nodes\Base
{    
    /**
     * Attribut parent d'un greffon
     *
     * @param array $node Liste des attributs de configuration du greffon
     * @param array $extras Liste des arguments de configuration globaux
     *
     * @return string
     */
    final public function custom_node_parent(&$node, $extras = [])
    {
        return '';
    }

    /**
     * Attribut de contenu d'un greffon
     *
     * @param array $node Liste des attributs de configuration du greffon
     * @param array $extras Liste des arguments de configuration globaux
     *
     * @return string
     */
    public function custom_node_content(&$node, $extras = [])
    {
        return isset($node['content']) ? $node['content'] : '';
    }
}