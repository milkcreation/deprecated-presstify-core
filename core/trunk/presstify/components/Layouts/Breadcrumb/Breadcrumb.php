<?php
/**
 * @name Breadcrumb
 * @desc Controleur d'affichage de fil d'ariane
 * @package presstiFy
 * @namespace \tiFy\Components\Layouts\Breadcrumb
 * @version 1.1
 * @subpackage Components
 * @since 1.2.571
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Components\Layouts\Breadcrumb;

use tiFy\Core\Layout\AbstractFactory;

class Breadcrumb extends AbstractFactory
{
    /**
     * Liste des éléments contenus dans le fil d'ariane
     * @var array
     */
    private static $Parts = [];

    /**
     * Indicateur de désactivation d'affichage du fil d'ariane
     * @var bool
     */
    private static $Disabled = false;

    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        // Déclaration des scripts
        \wp_register_style(
            'tiFyLayoutBreadcrumb',
            self::tFyAppAssetsUrl('Breadcrumb.css', get_class()),
            [],
            180122
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    final public function enqueue_scripts()
    {
        \wp_enqueue_style('tiFyLayoutBreadcrumb');
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
     * @param array {
     *      Liste des attributs de configuration de l'élément
     *
     * }
     *
     * @return $this
     */
    final public function addPart($attrs)
    {
        $defaults = [
            'class'     => '',
            'content'   => ''
        ];

        self::$Parts[] = array_merge($defaults, $attrs);

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
     * Désactivation de l'affichage
     *
     * @return $this
     */
    public function disable()
    {
        self::$Disabled = true;

        return $this;
    }

    /**
     * Activation de l'affichage
     *
     * @return $this
     */
    public function enable()
    {
        self::$Disabled = false;

        return $this;
    }

    /**
     * Affichage
     *
     * @return string
     */
    final protected function display()
    {
        if (self::$Disabled) :
            return '';
        endif;

        // Définition des arguments de template
        $container_id = $this->get('container_id');
        $container_class = $this->get('container_class', '');
        $parts = $this->getPartList();

        // Récupération du template d'affichage
        ob_start();
        self::tFyAppGetTemplatePart('breadcrumb', $this->getId(), compact('container_id', 'container_class', 'parts'));

        return ob_get_clean();
    }
}