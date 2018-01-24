<?php
/**
 * @name Breadcrumb
 * @desc Controleur d'affichage de fil d'ariane
 * @package presstiFy
 * @namespace \tiFy\Core\Layout\Breadcrumb
 * @version 1.1
 * @subpackage Core
 * @since 1.2.571
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Layout\Breadcrumb;

class Breadcrumb extends \tiFy\Core\Layout\Factory
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
            'tiFyCore-layoutBreadcrumb',
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
        \wp_enqueue_style('tiFyCore-layoutBreadcrumb');
    }

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
            'container_id'    => 'tiFyCore-layoutBreadcrumb--' . $this->getIndex(),
            'container_class' => '',
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
        $container_id = $this->getAttr('container_id');
        $container_class = $this->getAttr('container_class', '');
        $parts = $this->getPartList();

        // Récupération du template d'affichage
        ob_start();
        self::tFyAppGetTemplatePart('breadcrumb', null, compact('container_id', 'container_class', 'parts'));

        return ob_get_clean();
    }
}