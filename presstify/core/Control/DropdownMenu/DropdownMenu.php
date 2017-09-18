<?php
namespace tiFy\Core\Control\DropdownMenu;

use tiFy\Core\Control\Factory;

class DropdownMenu extends Factory
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'dropdown_menu';
	
	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{
		wp_register_style( 'tify_control-dropdown_menu', self::tFyAppUrl() . "/DropdownMenu.css", array( ), '160913' );
		wp_register_script('tify_control-dropdown_menu', self::tFyAppUrl() . "/DropdownMenu.js", array( 'jquery' ), '160913', true );
	}
			
	/* = Déclaration des scripts = */
	final public function enqueue_scripts()
	{
		wp_enqueue_style( 'tify_control-dropdown_menu' );
		wp_enqueue_script( 'tify_control-dropdown_menu' );
	}
		
	/* = Affichage du controleur = */
	public static function display( $args = array(), $echo = true )
	{
		static $instance = 0;
		$instance++;
		
		$defaults = array(
			'id'				=> 'tify_control_dropdown_menu-'. $instance,
			'class'				=> 'tify_control_dropdown_menu',
			'selected' 			=> 0,
			'links'				=> array(),
			'show_option_none'	=> __( 'Aucun', 'tify' ),
				
			// Liste de selection
			'picker'			=> array(
				'class'		=> '',
				'append' 	=> 'body',
				'position'	=> 'default', // default: vers le bas | top |  clever: positionnement intelligent
				'width'		=> 'auto'
			)
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
				
		// Traitement des arguments de la liste de selection
		$picker = wp_parse_args(
			$picker,
			array(
				'id'		=> $id .'-picker',
				'append' 	=> 'body',
				'position'	=> 'default', // default: vers le bas | top | clever: positionnement intelligent
				'width'		=> 'auto'
			)
		);
		
		$output  = "";
		$output .= "<div id=\"{$id}\" class=\"{$class}\" data-tify_control=\"dropdown_menu\" data-picker=\"". htmlentities( json_encode( $picker ), ENT_QUOTES, 'UTF-8') ."\">\n";	
		$output .= "\t<span class=\"selected\">";
		$output .= isset( $links[$selected] ) ? strip_tags( $links[$selected] ) : $show_option_none;
		$output .= "</span>\n";
		$output .= "</div>\n";
					
		// Picker HTML
		$output  .= "<div id=\"{$picker['id']}\" data-tify_control=\"dropdown_menu-picker\" class=\"tify_control_dropdown_menu-picker". ( $picker['class'] ? ' '. $picker['class'] : '' ) ."\" data-selector=\"#{$id}\" data-handler=\"#{$id}-handler\">\n";
		$output .= "\t<ul>\n";	
		foreach( (array) $links as $value => $link ) : if( $value === $selected ) continue;
			$output .= "\t\t<li>{$link}</li>\n";
		endforeach;
		$output .= "\t</ul>\n";
		$output .= "</div>\n";
		
		if( $echo )
			echo $output;
		
		return $output;
	}
}