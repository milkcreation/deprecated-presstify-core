<?php
/**
 * @Overrideable
 */
namespace tiFy\Components\NavMenu;

class Nodes extends \tiFy\Lib\Nodes\Base
{
    /**
     * Attribut de contenu d'un greffon
     *
     * @param mixed $attrs Liste des attributs de configuration du greffon
     * @param $extras Liste des arguments globaux complémentaires
     *
     * @return string
     */
    public function node_content($attrs, $extras = [])
    {
        return isset($attrs['content']) ? $attrs['content'] : '';
    }
}