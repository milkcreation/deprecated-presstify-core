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
     * @param mixed $attrs Liste des attributs de configuration du greffon
     * @param array $args Liste des arguments généraux
     *
     * @return string
     */
    final public function node_parent($attrs, $args = [])
    {
        return '';
    }

    /**
     * Attribut de contenu d'un greffon
     *
     * @param mixed $attrs Liste des attributs de configuration du greffon
     * @param array $args Liste des arguments généraux
     *
     * @return string
     */
    public function node_content($attrs, $args = [])
    {
        return isset($attrs['content']) ? $attrs['content'] : '';
    }
}