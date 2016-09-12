<?php
/**
 * Bouton de bascule on/off
 * @see http://php.quicoto.com/toggle-switches-using-input-radio-and-css3/
 * @see https://github.com/ghinda/css-toggle-switch
 * @see http://bashooka.com/coding/pure-css-toggle-switches/
 * @see https://proto.io/freebies/onoff/
 */ 

namespace tiFy\Core\Control\Switcher;

use tiFy\Core\Control\Factory;

class Switcher extends Factory
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'switch';
	
	// Nombre d'instance
	static $Instance = 0;
	
	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{
		wp_register_style( 'tify_control-switch', $this->Url ."/switch.css", array( ), '150310' );
	}
		
	/* = MISE EN FILE DES SCRIPTS = */
	final public function enqueue_scripts()
	{
		wp_enqueue_style( 'tify_control-switch' );
	}
		
	/* = AFFICHAGE = */
	public static function display( $args = array(), $instance = 0 )
	{
		if( ! $instance ) 
			$instance = self::$Instance++;
		
		$defaults = array(
			'id'				=> 'tify_control_switch-'. $instance,
			'class'				=> 'tify_control_switch',
			'name'				=> 'tify_control_switch-'. $instance,
			'label_on'			=> _x( 'Oui', 'tify_control_switch', 'tify' ),
			'label_off'			=> _x( 'Non', 'tify_control_switch', 'tify' ),
			'value_on'			=> 'on',
			'value_off'			=> 'off',
			'checked' 			=> null,
			'default'			=> 'on',
			'echo'				=> 1
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );	
		
		if( is_null( $checked ) )
			$checked = $default;

		$output  = "";
		$output .= "<div id=\"{$id}\" class=\"{$class}\" data-tify_control=\"switch\">\n";
		$output .= "\t<div class=\"tify_control_switch-wrapper\">\n";
	    $output .= "\t\t<input type=\"radio\" id=\"tify_control_switch-on-{$instance}\" class=\"tify_control_switch-radio tify_control_switch-radio-on\" name=\"{$name}\" value=\"{$value_on}\" autocomplete=\"off\" ". checked( ( $value_on === $checked ), true, false ) .">\n";
	    $output .= "\t\t<label for=\"tify_control_switch-on-{$instance}\" class=\"tify_control_switch-label tify_control_switch-label-on\">{$label_on}</label>\n";
	    $output .= "\t\t<input type=\"radio\" id=\"tify_control_switch-off-{$instance}\" class=\"tify_control_switch-radio tify_control_switch-radio-off\" name=\"{$name}\" value=\"{$value_off}\" autocomplete=\"off\" ". checked( ( $value_off === $checked ), true, false ) .">\n";
	    $output .= "\t\t<label for=\"tify_control_switch-off-{$instance}\" class=\"tify_control_switch-label tify_control_switch-label-off\">{$label_off}</label>\n";
	   	$output .= "\t\t<span class=\"tify_control_switch-selection\"></span>\n";
	  	$output .= "\t</div>\n";
		$output .= "</div>\n";
		
		if( $echo )
			echo $output;
		else
			return $output;
	}
}