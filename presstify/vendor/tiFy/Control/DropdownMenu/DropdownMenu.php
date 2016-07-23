<?php
namespace tiFy\Control\DropdownMenu;

use tiFy\Control\Control;

class DropdownMenu extends Control
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'dropdown_menu';
			
	/* = Déclaration des scripts = */
	public function register_scripts(){
		wp_enqueue_style( 'tify_controls-dropdown_menu', $this->Url ."/dropdown_menu.css", array( ), '141212' );
		wp_enqueue_script( 'tify_controls-dropdown_menu', $this->Url ."/dropdown_menu.js", array( 'jquery' ), '141212', true );
	}
		
	/* = Affichage du controleur = */
	public static function display( $args = array() ){
		static $instance = 0;
		$instance++;
		
		$defaults = array(
			'id'				=> 'tify_control_dropdown_menu-'. $instance,
			'class'				=> 'tify_control_dropdown_menu',
			'selected' 			=> 0,	
			'echo'				=> 1,
			'links'				=> array(),
			'show_option_none'	=> __( 'Aucun', 'tify' )
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		$output  = "";
		$output .= "<div id=\"{$id}\" class=\"{$class}\" data-tify_control=\"dropdown_menu\">\n";	
		$output .= "\t<span class=\"selected\"><b>". ( isset( $links[$selected] ) ? strip_tags( $links[$selected] ) : $show_option_none  ). "</b><i class=\"caret\"></i></span>\n";
		$output .= "\t<ul>\n";
		foreach( $links as $value => $link ) :  if( $value === $selected ) continue;
			$output .= "\t\t<li>{$link}</li>\n";
		endforeach;
		$output .= "\t</ul>\n";
		$output .= "</div>\n";
		
		if( $echo )
			echo $output;
		else
			return $output;
	}
}