<?php
/**
 * @name Switcher
 * @desc Controleur d'affichage de champs bouton de bascule
 * @see http://php.quicoto.com/toggle-switches-using-input-radio-and-css3/
 * @see https://github.com/ghinda/css-toggle-switch
 * @see http://bashooka.com/coding/pure-css-toggle-switches/
 * @see https://proto.io/freebies/onoff/
 * @package presstiFy
 * @namespace tiFy\Core\Control\Calendar
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 *
 * @deprecated \tiFy\Core\Fields\Switcher\Switcher
 */

namespace tiFy\Core\Control\Switcher;

/**
 * @Overrideable \App\Core\Control\Switcher\Switcher
 *
 * <?php
 * namespace \App\Core\Control\Switcher
 *
 * class Switcher extends \tiFy\Core\Control\Switcher\Switcher
 * {
 *
 * }
 */

class Switcher extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     * @var string
     */
    protected $ID = 'switch';

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public static function init()
    {
        \wp_register_style(
            'tify_control-switch',
            self::tFyAppAssetsUrl('Switcher.css', get_class()),
            [],
            150310
        );
        \wp_register_script(
            'tify_control-switch',
            self::tFyAppAssetsUrl('Switcher.js', get_class()),
            ['jquery'],
            170724,
            true
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    public static function enqueue_scripts()
    {
        \wp_enqueue_style('tify_control-switch');
        \wp_enqueue_script('tify_control-switch');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $attrs Liste des attributs de configuration
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    public static function display($attrs = [], $echo = true)
    {
        // Incrémentation du nombre d'instance
        self::$Instance++;

        // Traitement des attributs de configuration
        $defaults = [
            'id'              => 'tify_control_switcher-' . self::$Instance,
            'container_id'    => 'tifyControlSwitcher--' . self::$Instance,
            'container_class' => '',
            'name'            => 'tify_control_switcher-' . self::$Instance,
            'label_on'        => _x('Oui', 'tify_control_switch', 'tify'),
            'label_off'       => _x('Non', 'tify_control_switch', 'tify'),
            'value_on'        => 'on',
            'value_off'       => 'off',
            'checked'         => null,
            'default'         => 'on'
        ];
        $attrs = \wp_parse_args($attrs, $defaults);
        extract($attrs);

        if (is_null($checked)) :
            $checked = $default;
        endif;

        $output = "";
        $output .= "<div id=\"{$container_id}\" class=\"tifyControlSwitcher" . ($container_class ? ' ' . $container_class : '') . "\" data-tify_control=\"switcher\">\n";
        $output .= "\t<div class=\"tifyControlSwitcher-wrapper\">\n";
        $output .= "\t\t<input type=\"radio\" id=\"{$id}-on\" class=\"tifyControlSwitcher-input tifyControlSwitcher-input--on\" name=\"{$name}\" value=\"{$value_on}\" autocomplete=\"off\" " . checked(($value_on === $checked),
                true, false) . ">\n";
        $output .= "\t\t<label for=\"{$id}-on\" class=\"tifyControlSwitcher-label tifyControlSwitcher-label--on\">{$label_on}</label>\n";
        $output .= "\t\t<input type=\"radio\" id=\"{$id}-off\" class=\"tifyControlSwitcher-input tifyControlSwitcher-input--off\" name=\"{$name}\" value=\"{$value_off}\" autocomplete=\"off\" " . checked(($value_off === $checked),
                true, false) . ">\n";
        $output .= "\t\t<label for=\"{$id}-off\" class=\"tifyControlSwitcher-label tifyControlSwitcher-label--off\">{$label_off}</label>\n";
        $output .= "\t\t<span class=\"tifyControlSwitcher-handler\"></span>\n";
        $output .= "\t</div>\n";
        $output .= "</div>\n";

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}