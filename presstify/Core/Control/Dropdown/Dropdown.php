<?php
namespace tiFy\Core\Control\Dropdown;

use tiFy\Core\Control\Factory;

class Dropdown extends Factory
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'dropdown';
	
	// Instance Courante
	static $Instance = 0;
	
	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{
		wp_register_style( 'tify_control-dropdown', $this->Url .'/Dropdown.css', array( ), '141212' );
		wp_register_script( 'tify_control-dropdown', $this->Url .'/Dropdown.js', array( 'jquery' ), '141212', true );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	final public function enqueue_scripts()
	{
		wp_enqueue_style( 'tify_control-dropdown' );
		wp_enqueue_script( 'tify_control-dropdown' );
	}
		
	/* = AFFICHAGE = */
	public static function display( $args = array() )
	{
		self::$Instance++;
		
		$defaults = array(
			// Conteneur
			'id'				=> 'tify_control_dropdown-'. self::$Instance,
			'class'				=> '',
			'name'				=> 'tify_control_dropdown-'. self::$Instance,		
			'type'				=> 'single',	// @TODO single | multi
			// Liste de selection
			'picker'			=> array(
				'id'		=> 'tify_control_dropdown-picker-'. self::$Instance,
				'append' 	=> 'body',
				'position'	=> 'default', // default: vers le bas | top |  clever: positionnement intelligent
				'width'		=> 'auto'
			),				
				
			'echo'				=> 1,			
			
			'choices'			=> array(),
			'selected' 			=> 0,
			'show_option_none' 	=> __( 'Aucun', 'tify' ),
			'option_none_value' => -1
		);
		
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		// Traitement des arguments de la liste de selection
		$picker = wp_parse_args(
			$picker,
			array(
				'id'		=> 'tify_control_dropdown-picker-'. self::$Instance,
				'append' 	=> 'body',
				'position'	=> 'default', // default: vers le bas | top |  clever: positionnement intelligent
				'width'		=> 'auto'
			)
		);		
		
		if( is_string( $choices ) )
			$choices = array_map( 'trim', explode( ',', $choices ) );
		
		// Ajout du choix aucun au début de la liste des choix
		if( $show_option_none ) :
			$choices = array_reverse( $choices, true );
			$choices[$option_none_value] = $show_option_none;
			$choices = array_reverse($choices, true);
		endif;
		
		if( $show_option_none && ! $selected  )
			$selected = $option_none_value;
		
		// Selecteur HTML (JS désactivé)
		$output  = "";
		$output .= "<noscript>\n";
		$output .= "\t<style type=\"text/css\">.tify_control_dropdown{ display:none; }</style>";
		$output .= "\t<select name=\"{$name}\">";
		foreach( (array) $choices as $value => $label )
			$output .= "<option value=\"{$value}\">{$label}</option>";
		$output .= "\t</select>\n";
		$output .= "</noscript>\n";
		
		// Selecteur HTML
		$output .= "<div id=\"{$id}\" class=\"tify_control_dropdown {$class}\" data-tify_control=\"dropdown\" data-picker=\"". htmlentities( json_encode( $picker ), ENT_QUOTES, 'UTF-8') ."\">\n";
		$output .= "\t<span class=\"selected\">\n";
		$output .= "\t\t<b class=\"selection\">";
		$output .= "\t\t\t<input type=\"radio\" name=\"{$name}\" value=\"{$selected}\" autocomplete=\"off\" checked=\"checked\">\n";		
		$output .= isset( $choices[$selected] ) ? $choices[$selected] : ( $show_option_none ? $show_option_none : current( $choices ) );
		$output .= "\t\t</b>\n";
		$output .= "\t\t<i class=\"caret\"></i>\n";
		$output .= "\t</span>\n";
		$output .= "</div>\n";
		
		// Picker HTML
		$output  .= "<div id=\"{$picker['id']}\" class=\"tify_control_dropdown-picker\" data-selector=\"#{$id}\">\n";
		$output .= "\t<ul>\n";		
		foreach( $choices as $value => $label ) :
			$output .= "\t\t<li";
			if( $selected == $value ) :
				 $output .= " class=\"checked\"";
			endif;
			$output .= ">\n";
			switch( $type ) :
				default :
				case 'single' :
					$output .= "\t\t\t<label>\n";
					$output .= "\t\t\t\t<b class=\"selection\">";
					$output .= "\t\t\t\t\t<input type=\"radio\" name=\"{$name}\" value=\"{$value}\" autocomplete=\"off\" ". checked( $selected == $value, true, false ) .">\n";
					$output .= "\t\t\t\t\t{$label}";
					$output .= "\t\t\t\t</b>";
					$output .= "\t\t\t</label>\n";
					break;
				case 'multi' :
					// @TODO
					break;		
			endswitch;
			$output .= "\t\t</li>\n";
		endforeach;
		$output .= "\t</ul>\n";
		$output .= "</div>\n";
		
		if( $echo )
			echo $output;
		else
			return $output;
	}
}