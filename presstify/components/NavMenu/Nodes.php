<?php
/**
 * @Overrideable
 */
namespace tiFy\Components\NavMenu;

class Nodes extends \tiFy\Lib\Nodes\Base
{
    /**
     * GREFFONS PERSONNALISES
     */
    /**
     * Attribut de titre d'un greffon
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
     * Attribut de lien d'un greffon
     *
     * @param array $node Liste des attributs de configuration du greffon
     * @param array $extras Liste des arguments de configuration globaux
     *
     * @return string
     */
    public function custom_node_href(&$node, $extras = [])
    {
        return isset($node['href']) ? $node['href'] : '';
    }

    /**
     * Attribut d'icône d'un greffon
     *
     * @param array $node Liste des attributs de configuration du greffon
     * @param array $extras Liste des arguments de configuration globaux
     *
     * @return string
     */
    public function custom_node_icon(&$node, $extras = [])
    {
        return isset($node['icon']) ? $node['icon'] : '';
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
        if (isset($node['content'])) :
            return $node['content'];
        endif;

        $content = "";
        if (!empty($node['href'])) :
            $content .= "<a href=\"{$node['href']}\" title=\"{$node['title']}\">";
        endif;
        if (!empty($node['icon'])) :
            $content .= "<i class=\"{$node['icon']}\"></i>";
        endif;

        $content .= $node['title'];
        if (!empty($node['href'])) :
            $content .= "</a>";
        endif;

        return $content;
    }

    /**
     * Attribut d'élément courant d'un greffon
     *
     * @param array $node Liste des attributs de configuration du greffon
     * @param array $extras Liste des arguments de configuration globaux
     *
     * @return string
     */
    public function custom_node_current(&$node, $extras = [])
    {
        $current_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $current_url = preg_replace('#\?.*#', '', $current_url);

        return (!empty($node['href']) && ($current_url === $node['href']));
    }
}