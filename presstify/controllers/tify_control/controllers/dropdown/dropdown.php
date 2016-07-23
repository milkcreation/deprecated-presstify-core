<?php
class tiFy_Control_Dropdown extends tiFy_Control{
	public $id = 'dropdown';
	
	/* = Déclaration des scripts = */
	public function register_scripts(){
		wp_register_style( 'tify_controls-dropdown', $this->uri ."/dropdown.css", array( ), '141212' );
		wp_register_script( 'tify_controls-dropdown', $this->uri ."/dropdown.js", array( 'jquery' ), '141212', true );
	}
		
	/* = Affichage du controleur = */
	public function display( $args = array() ){
		static $instance = 0;
		$instance++;
		
		$defaults = array(
			'id'				=> 'tify_control_dropdown-'. $instance,
			'class'				=> '',
			'name'				=> 'tify_control_dropdown-'. $instance,		
			'type'				=> 'single',	// @TODO single | multi
			'selected' 			=> 0,			
			'echo'				=> 1,			
			
			'choices'			=> array(),
			'show_option_none' 	=> __( 'Aucun', 'tify' )
		);
		
		$args = wp_parse_args( $args, $defaults );
		extract( $args );

		$output  = "";
		$output .= "<noscript>\n";
		$output .= "\t<style type=\"text/css\">";
		$output .= "\t\t.tify_dropdown{ display:none; }\n";
		$output .= "\t</style>";
		$output .= "\t<select name=\"{$name}\">";
		foreach( $choices as $value => $label )
			$output .= "";
		$output .= "\t</select>\n";
		$output .= "</noscript>\n";
		
		$output .= "<div id=\"{$id}\" class=\"tify_control_dropdown {$class}\" data-tify_control=\"dropdown\">\n";
		$output .= "\t<span class=\"selected\">\n";
		$output .= "\t\t<b>". ( ( isset( $choices[$selected] ) ) ? $choices[$selected] : ( $show_option_none ? $show_option_none : current( $choices ) ) ) ."</b>\n";
		$output .= "\t\t<i class=\"caret\"></i>\n";
		$output .= "\t</span>\n";
		$output .= "\t<ul>\n";
		if( $show_option_none ) :
			$output .= "\t\t<li";
			if( ! $selected ) 
				$output .= " class=\"checked\"";
			$output .= ">\n";
	    	$output .= "\t\t\t<label>\n";
			$output .= "\t\t\t\t<input type=\"radio\" name=\"{$name}\" value=\"0\" autocomplete=\"off\" ". checked( ! $selected, true, false ) .">\n";
			$output .= "\t\t\t\t<span>{$show_option_none}</span>\n";
			$output .= "\t\t\t</label>\n";
	    	$output .= "\t\t</li>\n";		
		endif;
		
		foreach( $choices as $value => $label ) :
			$output .= "\t\t<li";
			if( $selected === $value )
				 $output .= " class=\"checked\""; 
			$output .= ">\n";
			switch( $type ) :
				default :
				case 'single' :
					$output .= "\t\t\t<label>\n";
					$output .= "\t\t\t\t<input type=\"radio\" name=\"{$name}\" value=\"{$value}\" autocomplete=\"off\" ". checked( $selected  === $value, true, false ) .">\n";
					$output .= "\t\t\t\t<span>{$label}</span>\n";
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