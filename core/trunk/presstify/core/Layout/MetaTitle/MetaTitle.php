<?php
/**
 * @name MetaTitle
 * @desc Controleur d'affichage de la balise meta title de l'entête du site
 * @package presstiFy
 * @namespace \tiFy\Core\Layout\MetaTitle
 * @version 1.1
 * @subpackage Core
 * @since 1.2.571
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Layout\MetaTitle;

class MetaTitle extends \tiFy\Core\Layout\Factory
{
    /**
     * Liste des éléments contenus dans le fil d'ariane
     * @var array
     */
    private static $Parts = [];

    /**
     * Traitement des attributs de configuration
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return array
     */
    final protected function parseAttrs($attrs = [])
    {
        $defaults = [
            'separator'       => '&nbsp;|&nbsp;',
            'parts'           => [],
        ];
        $attrs = array_merge($defaults, $attrs);

        if ($parts = $this->getAttr('parts', [])) :
            self::$Parts = $parts;
        endif;

        return $attrs;
    }

    /**
     * Récupération de la liste des éléments contenus dans le fil d'ariane
     *
     * @return array
     */
    private function getPartList()
    {
        if (!self::$Parts) :
            self::$Parts = (new WpQueryPart())->getList();
        endif;

        return self::$Parts;
    }

    /**
     * Ajout d'un élément de contenu au fil d'arianne
     *
     * @return $this
     */
    final public function addPart($string)
    {
        self::$Parts[] = $string;

        return $this;
    }

    /**
     * Supprime l'ensemble des éléments de contenu prédéfinis
     *
     * @return $this
     */
    public function reset()
    {
        self::$Parts = [];

        return $this;
    }

    /**
     * Affichage
     *
     * @return string
     */
    final protected function display()
    {
        // Définition des arguments de template
        $separator = $this->getAttr('separator');
        $parts = $this->getPartList();

        // Récupération du template d'affichage
        ob_start();
        echo implode($separator, $parts);

        return ob_get_clean();
    }
}