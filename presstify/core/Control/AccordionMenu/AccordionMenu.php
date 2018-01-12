<?php
/**
 * @name AccordionMenu
 * @desc Controleur d'affichage de menu accordéon
 * @package presstiFy
 * @namespace tiFy\Core\Control\AccordionMenu
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\AccordionMenu;

/**
 * @Overrideable \App\Core\Control\AccordionMenu\AccordionMenu
 *
 * <?php
 * namespace \App\Core\Control\AccordionMenu
 *
 * class AccordionMenu extends \tiFy\Core\Control\AccordionMenu\AccordionMenu
 * {
 *
 * }
 */

class AccordionMenu extends \tiFy\Core\Control\Factory
{
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    protected function init()
    {
        // Déclaration des scripts
        \wp_register_style(
            'tify_control-accordion_menu',
            self::tFyAppAssetsUrl('AccordionMenu.css', get_class()),
            [],
            170704
        );
        \wp_register_script(
            'tify_control-accordion_menu',
            self::tFyAppAssetsUrl('AccordionMenu.js', get_class()),
            ['jquery-ui-widget'],
            170704,
            true
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    protected function enqueue_scripts()
    {
        \wp_enqueue_style('tify_control-accordion_menu');
        \wp_enqueue_script('tify_control-accordion_menu');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $attrs {
     *      Liste des attributs de configuration
     *
     *      @var string $id Marqueur d'identification unique
     *      @var string $container_id Id HTML du conteneur
     *      @var string $container_class Classe HTML du conteneur
     *      @var string $theme Couleur du thème (light|dark| false)
     *      @var array $nodes Liste des greffons {
     *      }
     *      @var mixed $selected Selection active
     * }
     *
     * @return string
     */
    protected function display($attrs = [])
    {
        // Traitement des attributs de configuration
        $defaults = [
            'id'                => 'tiFyControlAccordionMenu-' . $this->getId(),
            'container_id'      => 'tiFyControlAccordionMenu--' . $this->getId(),
            'container_class'   => '',
            'theme'             => 'dark',
            'nodes'             => [],
            'selected'          => 0
        ];
        $attrs    = wp_parse_args($attrs, $defaults);

        /**
         *  @var string $id Marqueur d'identification unique
         *  @var string $container_id Id HTML du conteneur
         *  @var string $container_class Classe HTML du conteneur
         *  @var string $theme Couleur du thème (light|dark| false)
         *  @var array $nodes Liste des greffons {
         *  }
         *  @var mixed $selected Selection active
         */
        extract($attrs);

        if (count($nodes) === 2) :
            $type       = $nodes[0];
            $query_args = $nodes[1];
        else :
            $type       = 'custom';
            $query_args = [];
        endif;

        $Nodes = self::tFyAppLoadOverride('tiFy\Core\Control\AccordionMenu\Nodes');
        switch ($type) :
            case 'terms' :
                $nodes = $Nodes->terms($query_args,['selected' => $selected]);
                break;
            default:
            case 'custom' :
                break;
        endswitch;

        $output = "";
        $output .= "<div id=\"{$container_id}\" class=\"tiFyControlAccordionMenu tiFyControlAccordionMenu--{$theme}" . ($container_class ? ' ' . $container_class : '') . "\" data-tify_control=\"accordion_menu\">\n";
        $output .= "\t<nav class=\"tiFyControlAccordionMenu-nav\">\n";
        $Walker = self::tFyAppLoadOverride('tiFy\Core\Control\AccordionMenu\Walker');
        $output .= $Walker->output($nodes);
        $output .= "\t</nav>\n";
        $output .= "</div>\n";

        echo $output;
    }
}