<?php
/**
 * @name Notices
 * @desc Controleur d'affichage de message de notification
 * @package presstiFy
 * @namespace tiFy\Core\Control\Notices
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\Notices;

/**
 * @Overrideable \App\Core\Control\Notices\Notices
 *
 * <?php
 * namespace \App\Core\Control\Notices
 *
 * class Notices extends \tiFy\Core\Control\Notices\Notices
 * {
 *
 * }
 */

class Notices extends \tiFy\Core\Control\Factory
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
            'tify_control-notices',
            self::tFyAppAssetsUrl('Notices.css', get_class()),
            [],
            170130
        );
        \wp_register_script(
            'tify_control-notices',
            self::tFyAppAssetsUrl('Notices.js', get_class()),
            ['jquery'],
            170130,
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
        \wp_enqueue_style('tify_control-notices');
        \wp_enqueue_script('tify_control-notices');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return string
     */
    protected function display($attrs = [])
    {
        // Traitement des attributs de configuration
        $defaults = [
            'text'        => 'Lorem ipsum dolor site amet',
            'id'          => 'tiFyControl-Notices--' . $this->getId(),
            'class'       => '',
            'dismissible' => false,
            'type'        => 'info'
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

        $output = "";
        $output .= "<div" . ($id ? " id=\"{$id}\"" : "") . " class=\"tiFyNotice tiFyNotice--" . strtolower($type) . "" . ($class ? " " . $class : "") . "\" >";

        if ($dismissible) :
            $output .= "<button type=\"button\" data-dismiss=\"tiFyNotice\">";
            if (is_bool($dismissible)) :
                $output .= "&times;";
            else :
                $output .= (string)$dismissible;
            endif;
            $output .= "</button>";
        endif;

        $output .= "<div>{$text}</div>";
        $output .= "</div>";

        echo $output;
    }
}