<?php
namespace tiFy\Core\Control\Checkbox;

class Checkbox extends \tiFy\Core\Control\Factory
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'checkbox';
	
	// Instance
	private static $Instance;
	
	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{
		wp_register_style( 'tify_control-checkbox', self::tFyAppUrl() .'/Checkbox.css', array( 'dashicons' ), '150420' );
		wp_register_script( 'tify_control-checkbox', self::tFyAppUrl() .'/Checkbox.js', array( 'jquery' ), '150420', true );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	final public function enqueue_scripts()
	{
		wp_enqueue_style( 'tify_control-checkbox' );
		wp_enqueue_script( 'tify_control-checkbox' );
	}
		
	/* = AFFICHAGE = */
	public static function display( $args = array() )
	{
		self::$Instance++;
		
		$defaults = array(
			'id'				=> 'tify_control_checkbox-'. self::$Instance,
			'class'				=> 'tify_control_checkbox',
			'name'				=> 'tify_control_checkbox-'. self::$Instance,		
			'value'				=> 0,
			'label'				=> __( 'Aucun', 'tify' ),
			'label_class'		=> 'tify_control_checkbox-label',
			'label_position'	=> 'R',
			'checked' 			=> 0,			
			'echo'				=> 1
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		$output  = "";
		$output .= "<noscript>\n";
		$output .= "\t<style type=\"text/css\">";
		$output .= "\t\t.tify_checkbox{ display:none; }\n";
		$output .= "\t</style>";
		$output .= "\t<div class=\"checkbox\">\n";
		$output .= "\t\t<input type=\"checkbox\" value=\"{$value}\" name=\"{$name}\">";
		$output .= "\t\t<label>{$label}</label>";
		$output .= "\t</div>\n";
		$output .= "</noscript>\n";
		
		$class  .= ( (bool) $checked === true ) ? ' checked' : '';
		
		$output .= "<div id=\"{$id}\" class=\"{$class}\" data-tify_control=\"checkbox\" data-label_position=\"". ( $label_position === 'R' ? 'right' : 'left' ) ."\">\n";
		$output .= "\t<label class=\"{$label_class}\">";
		if( $label_position != 'R' )
			$output .= $label;

		$output .= "<input type=\"checkbox\" value=\"{$value}\" name=\"{$name}[]\" autocomplete=\"off\" ". checked( (bool)$checked, true, false ) .">";
		if( $label_position == 'R' )
			$output .= "$label";
		$output .= "\t</label>";
		$output .= "</div>\n";
		
		if( $echo )
			echo $output;
		else
			return $output;
	}
}