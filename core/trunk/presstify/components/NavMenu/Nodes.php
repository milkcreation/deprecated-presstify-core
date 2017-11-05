<?php
/**
 * @Overrideable
 */
namespace tiFy\Components\NavMenu;

class Nodes extends \tiFy\Lib\Nodes\Base
{
    /**
     * Attribut de titre d'un greffon
     *
     * @param mixed $attrs Liste des attributs de configuration du greffon
     * @param $extras Liste des arguments globaux complémentaires
     *
     * @return string
     */
    public function node_title($attrs, $extras = [])
    {
        return isset($attrs['title']) ? $attrs['title'] : '';
    }

    /**
     * Attribut de lien d'un greffon
     *
     * @param mixed $attrs Liste des attributs de configuration du greffon
     * @param $extras Liste des arguments globaux complémentaires
     *
     * @return string
     */
    public function node_href($attrs, $extras = [])
    {
        return isset($attrs['href']) ? $attrs['href'] : '';
    }

    /**
     * Attribut d'icône d'un greffon
     *
     * @param mixed $attrs Liste des attributs de configuration du greffon
     * @param $extras Liste des arguments globaux complémentaires
     *
     * @return string
     */
    public function node_icon($attrs, $extras = [])
    {
        return isset($attrs['icon']) ? $attrs['icon'] : '';
    }

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
        if (isset($attrs['content'])) :
            return $attrs['content'];
        endif;

        $content = "";
        if (!empty($attrs['href'])) :
            $content .= "<a href=\"{$attrs['href']}\" title=\"{$attrs['title']}\">";
        endif;
        if (!empty($attrs['icon'])) :
            $content .= "<i class=\"{$attrs['icon']}\"></i>";
        endif;

        $content .= $attrs['title'];
        if (!empty($attrs['href'])) :
            $content .= "</a>";
        endif;

        return $content;
    }

    /**
     * Attribut d'élément courant d'un greffon
     *
     * @param mixed $attrs Liste des attributs de configuration du greffon
     * @param $extras Liste des arguments globaux complémentaires
     *
     * @return string
     */
    public function node_current($attrs, $extras = [])
    {
        $current_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $current_url = preg_replace('#\?.*#', '', $current_url);

        return (!empty($attrs['href']) && ($current_url === $attrs['href']));
    }
}