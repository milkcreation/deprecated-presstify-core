<?php
namespace tiFy\Core\Control\Colorpicker;

class Colorpicker extends \tiFy\Core\Control\Factory
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'colorpicker';
	
	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{
		wp_register_style( 'tify_control-colorpicker', static::tFyAppUrl( get_class() ) .'/Colorpicker.css', array( 'spectrum' ), '141216' );
		$deps = array( 'jquery', 'spectrum' );
		if( wp_script_is( 'spectrum-i10n', 'registered' ) )
			$deps[] = 'spectrum-i10n';
		wp_register_script( 'tify_control-colorpicker', static::tFyAppUrl( get_class() ) .'/Colorpicker.js', $deps, '141216', true );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	final public function enqueue_scripts()
	{
		wp_enqueue_style( 'tify_control-colorpicker' );		
		wp_enqueue_script( 'tify_control-colorpicker' );
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