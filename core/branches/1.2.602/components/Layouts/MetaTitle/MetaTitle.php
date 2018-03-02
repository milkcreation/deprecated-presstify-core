<?php

/**
 * @name MetaTitle
 * @desc Controleur d'affichage de la balise meta title de l'entête du site
 * @package presstiFy
 * @namespace \tiFy\Core\Layouts\MetaTitle
 * @version 1.1
 * @subpackage Components
 * @since 1.2.571
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Components\Layouts\MetaTitle;

use tiFy\Core\Layout\AbstractFactory;

class MetaTitle extends AbstractFactory
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
    final protected function parse($attrs = [])
    {
        $defaults = [
            'separator'       => '&nbsp;|&nbsp;',
            'parts'           => [],
        ];
        $attrs = array_merge($defaults, $attrs);

        if ($parts = $this->get('parts', [])) :
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
        $separator = $this->get('separator');
        $parts = $this->getPartList();

        // Récupération du template d'affichage
        return implode($separator, $parts);
    }
}