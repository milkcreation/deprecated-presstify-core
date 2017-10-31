<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\Tabs;

class Nodes extends \tiFy\Lib\Nodes\Base
{
    /**
     * Attribut parent d'un greffon
     *
     * @param $attrs Attributs de configuration du greffon
     * @param $extras Liste des arguments globaux complémentaires
     *
     * @return string
     */
    public function node_parent($attrs, $extras = [])
    {
        return !empty($attrs['parent']) ? $attrs['parent'] : '';
    }

    /**
     * Attribut position d'un greffon
     *
     * @param $attrs Attributs de configuration du greffon
     * @param $extras Liste des arguments globaux complémentaires
     *
     * @return string
     */
    public function node_position($attrs, $extras = [])
    {
        return (isset($attrs['position']) && is_numeric($attrs['position'])) ? (int)$attrs['position'] : null;
    }

    /**
     * Attribut titre d'un greffon
     *
     * @param $attrs Attributs de configuration du greffon
     * @param $extras Liste des arguments globaux complémentaires
     *
     * @return string
     */
    public function node_title($attrs, $extras = [])
    {
        return isset($attrs['title']) ? $attrs['title'] : '';
    }

    /**
     * Attribut contenu d'un greffon
     *
     * @param $attrs Attributs de configuration du greffon
     * @param $extras Liste des arguments globaux complémentaires
     *
     * @return string
     */
    public function node_content($attrs, $extras = [])
    {
        return isset($attrs['content']) ? $attrs['content'] : '';
    }
}