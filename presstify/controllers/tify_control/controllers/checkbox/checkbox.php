<?php
class tiFy_Control_Checkbox extends tiFy_Control{	
	/* = Déclaration des scripts = */
	public function register_scripts(){
		wp_register_style( 'tify_controls-checkbox', $this->uri ."/checkbox.css", array( 'dashicons' ), '150420' );
		wp_register_script( 'tify_controls-checkbox', $this->uri ."/checkbox.js", array( 'jquery' ), '150420', true );
	}
		
	/* = Affichage du controleur = */
	public function display( $args = array() ){
		static $instance = 0;
		$instance++;
		
		$defaults = array(
			'id'				=> 'tify_control_checkbox-'. $instance,
			'class'				=> 'tify_control_checkbox',
			'name'				=> 'tify_control_checkbox-'. $instance,		
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
		
		$class  .= ( (bool)$checked === true ) ? ' checked' : '';
		
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

/**
 * Affichage de liste déroulante
 */
function tify_control_checkbox( $args = array() ){
	global $tiFy;
	
	return $tiFy->control->checkbox->display( $args );
}