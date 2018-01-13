<?php
/**
 * @name Progress
 * @desc Controleur d'affichage d'un indicateur de progression
 * @package presstiFy
 * @namespace tiFy\Core\Control\Progress
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\Progress;

/**
 * @Overrideable \App\Core\Control\Progress\Progress
 *
 * <?php
 * namespace \App\Core\Control\Progress
 *
 * class Progress extends \tiFy\Core\Control\Progress\Progress
 * {
 *
 * }
 */

class Progress extends \tiFy\Core\Control\Factory
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
            'tify_control-progress',
            self::tFyAppAssetsUrl('Progress.css', get_class()),
            [],
            160605
        );
        \wp_register_script(
            'tify_control-progress',
            self::tFyAppAssetsUrl('Progress.js', get_class()),
            ['jquery-ui-widget'],
            160605,
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
        \wp_enqueue_style('tify_control-progress');
        \wp_enqueue_script('tify_control-progress');
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
            'id'        => 'tiFyControlProgress--' . $this->getId(),
            'class'     => '',
            'title'     => '',
            'value'     => 100,
            'max'       => 100,
            'in_footer' => true
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

        $percent = ceil(($value / $max) * 100);

        $output = "";
        $output .= "<div id=\"{$id}\" class=\"tiFyControlProgress" . ($class ? ' ' . $class : '') . "\" data-tify_control=\"progress\">\n";
        $output .= "\t<div class=\"tiFyControlProgress-content\">";
        $output .= "\t\t<div class=\"tiFyControlProgress-contentHeader\">\n";
        $output .= "\t\t\t<h3 class=\"tiFyControlProgress-headerTitle\" data-role=\"header-title\">{$title}</h3>\n";
        $output .= "\t\t</div>\n";
        $output .= "\t\t<div class=\"tiFyControlProgress-contentBody\">\n";
        $output .= "\t\t\t<div class=\"tiFyControlProgress-bar\" style=\"background-position:-{$percent}% 0;\" data-role=\"bar\" data-max=\"" . intval($max) . "\">\n";
        $output .= "\t\t\t\t<div class=\"tiFyControlProgress-indicator\" data-role=\"indicator\"></div>\n";
        $output .= "\t\t\t</div>\n";
        $output .= "\t\t\t<div class=\"tiFyControlProgress-infos\" data-role=\"info\"></div>\n";
        $output .= "\t\t</div>\n";
        $output .= "\t\t<div class=\"tiFyControlProgress-contentFooter\">\n";
        $output .= "\t\t\t<button type=\"button\" class=\"tiFyButton--primary tiFyControlProgress-close\" data-role=\"close\">" . __('Annuler',
                'tify') . "</button>\n";
        $output .= "\t\t</div>\n";
        $output .= "\t</div>\n";
        $output .= "</div>\n";

        if ($in_footer) :
            $footer = function () use ($output) { echo $output; };
            \add_action((!is_admin() ? 'wp_footer' : 'admin_footer'), $footer);
        else :
            echo $output;
        endif;
    }
}