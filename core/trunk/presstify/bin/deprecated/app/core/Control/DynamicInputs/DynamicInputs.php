<?php
/**
 * @deprecated
 */
namespace tiFy\Core\Control\DynamicInputs;

use tiFy\Core\Control\Factory;

class DynamicInputs extends Factory
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'dynamic_inputs';

	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{
		wp_register_style( 'tify_control-dynamic_inputs', self::tFyAppUrl( get_class() ) ."/dynamic_inputs.css", array( ), '150525' );
		wp_register_script( 'tify_control-dynamic_inputs', self::tFyAppUrl( get_class() ) ."/dynamic_inputs.js", array( 'jquery' ), '150525', true );
		wp_localize_script( 'tify_control-dynamic_inputs', 'tyctrl_dinputs', array( 'MaxAttempt' => __( 'Nombre de valeur maximum atteinte', 'tify' ) ) );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	final public function enqueue_scripts()
	{
		wp_enqueue_style( 'tify_control-dynamic_inputs' );
		wp_enqueue_script( 'tify_control-dynamic_inputs' );
	}
		
	/* = AFFICHAGE = */
	public static function display( $args = array() )
	{
		static $instance = 0;
		$instance++;
				
		$defaults = array(
			'id'				=> 'tify_control_dynamic_inputs-'. $instance,
			'class'				=> 'tify_control_dynamic_inputs',
			'name'				=> 'tify_control_dynamic_inputs-'. $instance,
			'sample_html'		=> '',
			'values'			=> array(),
			'values_cb'			=> false,
			'add_button_txt'	=> __( 'Ajouter', 'tify' ),
			'default'			=> '',
			'max'				=> -1,
			'echo'				=> 1
		);
		$args = wp_parse_args( $args, $defaults );

		if( ! $args['sample_html'] )
			$args['sample_html'] = "<input type=\"text\" name=\"%%name%%[%%index%%]\" value=\"%%value%%\">";
					
		$output  = "";		
		$output .= "<div id=\"{$args['id']}\" class=\"{$args['class']}\" data-tify_control=\"dynamic_inputs\">\n";
		$output .= "\t<input class=\"dynamic_inputs-max\" type=\"hidden\" value=\"{$args['max']}\">";
		$output .= "\t<ul>";
		if( ! empty( $args['values'] ) ) :
			if( ! empty( $args['values_cb'] ) &&  is_callable( $args['values_cb'] ) ) :
				foreach( (array) $args['values'] as $i => $v ) :
					$output .= "<li data-index=\"{$i}\">";
					$output .= "\t". call_user_func( $args['values_cb'], $i, $v );
					$output .= "\t<a href=\"#tify_control_dynamic_inputs-remove_button\" class=\"tify_button_remove\"></a>\n";
					$output .= "</li>";
				endforeach;
			else :				
				foreach( (array) $args['values'] as $i => $v ) :					
					$output .= "\t\t<li data-index=\"{$i}\">\n";
					$value = ( is_string( $v ) ) ? $v : wp_parse_args( $v, $args['default'] );
					$sample_html = $args['sample_html'];
					
					if( is_array( $value ) ) :
						$sample_html = preg_replace_callback( '/%%value%%\[([a-zA-Z0-9_\-]*)\]/', function( $matches ) use ( $value ) { return ( isset( $value[ $matches[1] ] ) ) ? $value[ $matches[1] ] : ''; }, $sample_html );
					else:
						$sample_html = preg_replace( '/%%value%%/', $value, $sample_html );
					endif;			
					$patterns = array(); $replacements = array();
					array_push( $patterns, '/%%name%%/', '/%%index%%/' );
					array_push( $replacements, $args['name'], $i );			
					$sample_html = preg_replace( $patterns, $replacements, $sample_html );			
								
					$output .= $sample_html;
					
					$output .= "\t\t\t<a href=\"#tify_control_dynamic_inputs-remove_button\" class=\"tify_button_remove\"></a>\n";
					$output .= "\t\t</li>\n";
				endforeach;			
			endif;
		endif;
		$output .= "\t</ul>\n";

		// Éditeur
		$output .= "\t<div>\n";		
		$output .= "\t\t<div style=\"display:none;\">\n";
		$value = $args['default'];
		if( is_array( $value ) ) :	
			$output .= preg_replace_callback( '/%%value%%\[([a-zA-Z0-9_\-]*)\]/', function( $matches ) use ( $value ) { return ( isset( $value[ $matches[1] ] ) ) ? $value[ $matches[1] ] : '';  }, $args['sample_html'] );
		else :
			$output .= preg_replace( '/%%value%%/', $value, (string) $args['sample_html'] );
		endif;
		$output .= "\t\t</div>\n";
		
		$output .= "\t\t<a href=\"#tify_control_dynamic_inputs-add_button\" data-name=\"{$args['name']}\" data-default=\"". ( htmlentities( json_encode( $args['default'] ) ) ) ."\" class=\"tify_control_dynamic_inputs-add_button button-secondary\">\n";
		$output .= $args['add_button_txt'];
		$output .= "\t\t</a>\n";

		$output .= "\t</div>\n";
			
		$output .= "</div>\n";
		
		if( $args['echo'] )
			echo $output;
		
		return $output;
	}
}