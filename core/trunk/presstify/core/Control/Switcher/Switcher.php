<?php
/**
 * Bouton de bascule on/off
 * @see http://php.quicoto.com/toggle-switches-using-input-radio-and-css3/
 * @see https://github.com/ghinda/css-toggle-switch
 * @see http://bashooka.com/coding/pure-css-toggle-switches/
 * @see https://proto.io/freebies/onoff/
 */ 

namespace tiFy\Core\Control\Switcher;

class Switcher extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'switch';

    /**
     * Instance courante
     * @var integer
     */
    protected static $Instance = 0;

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation générale
     */
    final public function init()
    {
        wp_register_style('tify_control-switch', self::tFyAppAssetsUrl('Switcher.css', get_class()), array( ), '150310');
        wp_register_script('tify_control-switch', self::tFyAppAssetsUrl('Switcher.js', get_class()), array( 'jquery' ), 170724);
    }

    /**
     * Mise en file des scripts
     */
    public static function enqueue_scripts()
    {
        wp_enqueue_style('tify_control-switch');
        wp_enqueue_script('tify_control-switch');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $args
     * @param number $instance
     *
     * @return string
     */
    public static function display( $args = array(), $echo = true  )
    {
        self::$Instance++;

        $defaults = array(
            'id'                => 'tify_control_switcher-'. self::$Instance,
            'container_id'      => 'tifyControlSwitcher--'. self::$Instance,
            'container_class'   => '',
            'name'              => 'tify_control_switcher-'. self::$Instance,
            'label_on'          => _x( 'Oui', 'tify_control_switch', 'tify' ),
            'label_off'         => _x( 'Non', 'tify_control_switch', 'tify' ),
            'value_on'          => 'on',
            'value_off'         => 'off',
            'checked'           => null,
            'default'           => 'on'
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args );    
        
        if( is_null( $checked ) )
            $checked = $default;

        $output  = "";
        $output .= "<div id=\"{$container_id}\" class=\"tifyControlSwitcher". ( $container_class ? ' '. $container_class : '' ) ."\" data-tify_control=\"switcher\">\n";
        $output .= "\t<div class=\"tifyControlSwitcher-wrapper\">\n";
        $output .= "\t\t<input type=\"radio\" id=\"{$id}-on\" class=\"tifyControlSwitcher-input tifyControlSwitcher-input--on\" name=\"{$name}\" value=\"{$value_on}\" autocomplete=\"off\" ". checked( ( $value_on === $checked ), true, false ) .">\n";
        $output .= "\t\t<label for=\"{$id}-on\" class=\"tifyControlSwitcher-label tifyControlSwitcher-label--on\">{$label_on}</label>\n";
        $output .= "\t\t<input type=\"radio\" id=\"{$id}-off\" class=\"tifyControlSwitcher-input tifyControlSwitcher-input--off\" name=\"{$name}\" value=\"{$value_off}\" autocomplete=\"off\" ". checked( ( $value_off === $checked ), true, false ) .">\n";
        $output .= "\t\t<label for=\"{$id}-off\" class=\"tifyControlSwitcher-label tifyControlSwitcher-label--off\">{$label_off}</label>\n";
        $output .= "\t\t<span class=\"tifyControlSwitcher-handler\"></span>\n";
        $output .= "\t</div>\n";
        $output .= "</div>\n";
        
        if( $echo )
            echo $output;

        return $output;
    }
}