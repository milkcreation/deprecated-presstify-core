<?php
namespace tiFy\Control\Colorpicker;

use tiFy\Control\Control;

class Colorpicker extends Control
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'colorpicker';
			
	/* = MISE EN FILE DES SCRIPTS = */
	public function enqueue_scripts()
	{
		wp_enqueue_style( 'tify_controls-colorpicker', $this->Url ."/colorpicker.css", array( 'spectrum' ), '141216' );
		$spectrum_depts = array( 'jquery', 'spectrum' );
		if( wp_script_is( 'spectrum-i10n', 'registered' ) )
			$spectrum_depts[] = 'spectrum-i10n';
		wp_enqueue_script( 'tify_controls-colorpicker', $this->Url ."/colorpicker.js", $spectrum_depts, '141216', true );
	}
		
	/* = AFFICHAGE = */
	public static function display( $args = array() )
	{
		$defaults = array(				
			'name'				=> '',
			'value' 			=> '',
			'attrs'				=> array(),
			'options'			=> array(), // @see https://bgrins.github.io/spectrum/#options
			'echo'				=> 1
		);	
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		// Traitement des options
		$options = wp_parse_args( $options, array(
		        'preferredFormat' => "hex"
			)
		);
		
		$output = "";
		$output .= "<div class=\"tify_colorpicker\">\n";
		$output .= "<input type=\"hidden\"";
		if( $name )
			$output .= " name=\"$name\"";	
		if( $attrs )
			foreach( $attrs as $iattr => $vattr )
				$output .= " $iattr=\"$vattr\"";		
		if( $options )
			$output .= " data-options=\"". esc_attr( json_encode( $options ) ) ."\"";
		$output .= " value=\"$value\" />";
		$output .= "</div>";
		
		if( $echo )
			echo $output;
		else
			return $output;
	}
}