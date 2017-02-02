<?php
namespace tiFy\Core\Control\Dropdown;

class Dropdown extends \tiFy\Core\Control\Factory
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
			'name'				=> '',		
			'type'				=> 'single',	// @TODO single | multi
			'attrs'				=> array(),
			// Liste de selection
			'picker'			=> array(
				'class'		=> '',
				'append' 	=> 'body',
				'position'	=> 'default', // default: vers le bas | top |  clever: positionnement intelligent
				'width'		=> 'auto'
			),				
			
			'choices'			=> array(),
			'selected' 			=> 0,
			'disabled'			=> false,
			'show_option_none' 	=> __( 'Aucun', 'tify' ),
			'option_none_value' => -1,
				
			'echo'				=> 1
		);
		
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		// Traitement des arguments de la liste de selection
		$picker = wp_parse_args(
			$picker,
			array(
				'id'		=> $id .'-picker',
				'class'		=> '',
				'append' 	=> 'body',
				'position'	=> 'default', // default: vers le bas | top | clever: positionnement intelligent
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
		
		// Selecteur de traitement
		$output  = "";
		$output .= "\t<select id=\"{$id}-handler\" name=\"{$name}\" data-tify_control=\"dropdown-handler\" data-selector=\"#{$id}\" data-picker=\"#{$picker['id']}\"". ( $disabled ? " disabled=\"disabled\"" : "" ) .">";
		foreach( (array) $choices as $value => $label ) :
			$output .= "<option value=\"{$value}\" ". selected( ( ( ! empty( $selected ) && ! empty( $value ) && ( $selected == $value ) ) || ( $selected === $value ) ), true, false ) .">". wp_strip_all_tags( $label, true ) ."</option>";
		endforeach;
		$output .= "\t</select>\n";		
		
		// Selecteur HTML
		if( $disabled )
			$class .= " disabled";
		
		$output .= "<div id=\"{$id}\" class=\"tify_control_dropdown {$class}\" data-tify_control=\"dropdown\" data-handler=\"#{$id}-handler\" data-picker=\"". htmlentities( json_encode( $picker ), ENT_QUOTES, 'UTF-8') ."\"";
		foreach( (array) $attrs as $k => $v )
			$output .= " {$k}=\"{$v}\"";
		$output .= ">\n";
		$output .= "\t<span class=\"selected\">\n";
		$output .= isset( $choices[$selected] ) ? $choices[$selected] : ( $show_option_none ? $show_option_none : current( $choices ) );
		$output .= "\t</span>\n";
		$output .= "</div>\n";
		
		// Picker HTML
		$output  .= "<div id=\"{$picker['id']}\" data-tify_control=\"dropdown-picker\" class=\"tify_control_dropdown-picker". ( $picker['class'] ? ' '. $picker['class'] : '' ) ."\" data-selector=\"#{$id}\" data-handler=\"#{$id}-handler\">\n";
		$output .= "\t<ul>\n";	
		foreach( (array) $choices as $value => $label ) :
			$output .= "\t\t<li";
			
			if( ( ! empty( $selected ) && ! empty( $value ) && ( $selected == $value ) ) || ( $selected === $value ) ) :
				 $output .= " class=\"checked\"";
			endif;
			$output .= ">\n";
			$output .= $label;
			$output .= "\t\t</li>\n";
		endforeach;
		$output .= "\t</ul>\n";
		$output .= "</div>\n";
		
		if( $echo )
			echo $output;
		
		return $output;
	}
}