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
     * @param array $node Liste des attributs de configuration du greffon
     * @param array $extras Liste des arguments de configuration globaux
     *
     * @return string
     */
    public function custom_node_parent(&$node, $extras = [])
    {
        return !empty($node['parent']) ? $node['parent'] : '';
    }

    /**
     * Attribut position d'un greffon
     *
     * @param array $node Liste des attributs de configuration du greffon
     * @param array $extras Liste des arguments de configuration globaux
     *
     * @return string
     */
    public function custom_node_position(&$node, $extras = [])
    {
        return (isset($node['position']) && is_numeric($node['position'])) ? (int)$node['position'] : null;
    }

    /**
     * Attribut titre d'un greffon
     *
     * @param array $node Liste des attributs de configuration du greffon
     * @param array $extras Liste des arguments de configuration globaux
     *
     * @return string
     */
    public function custom_node_title(&$node, $extras = [])
    {
        return isset($node['title']) ? $node['title'] : '';
    }

    /**
     * Attribut contenu d'un greffon
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