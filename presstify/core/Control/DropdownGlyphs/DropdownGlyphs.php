<?php
namespace tiFy\Core\Control\DropdownGlyphs;

use tiFy\Core\Control\Factory;
use tiFy\Lib\File;

class DropdownGlyphs extends Factory
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'dropdown_glyphs';
	
	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{
		wp_register_style( 'tify_control-dropdown_glyphs', self::tFyAppUrl() . '/dropdown_glyphs.css', array( ), '141212' );
		wp_register_script('tify_control-dropdown_glyphs', self::tFyAppUrl() . '/dropdown_glyphs.js', array( 'jquery' ), '141212', true );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	final public function enqueue_scripts()
	{
		wp_enqueue_style( 'tify_control-dropdown_glyphs' );
		wp_enqueue_script( 'tify_control-dropdown_glyphs' );
	}
		
	/* = AFFICHAGE = */
	public static function display( $args = array() )
	{
		static $instance = 0;
		$instance++;
		
		$defaults = array(
				'selected' 			=> 0,
				'echo'				=> 1,
				'name'				=> 'tify_control_dropdown_glyphs-'. $instance,
				'id'				=> 'tify_control_dropdown_glyphs-'. $instance,
				'links'				=> array(),
				'show_option_none'	=> ' ', 
				// Chemin vers le feuille de style des glyphs
				'css'				=> '',//$this->master->script_loader->css['font-awesome']['dev'],
				// Prefixe des classes css à parser (requis)
				'prefix'			=> 'fa',	
				'cols'				=> 32 // Nombre de colonnes d'icônes à afficher par ligne
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		$glyphs = array();
		$css_content = File::getContents( $css );
		preg_match_all( '/.'. $prefix .'-(.*):before\s*\{\s*content\:\s*"(.*)";\s*\}\s*/', $css_content, $matches );
		foreach( $matches[1] as $i => $class )
			$glyphs[$class] = $matches[2][$i];
	
		$output  = "";
		$output .= "<div";
		if( $id )
			$output .= " id=\"$id\"";
		$output .= " class=\"tify_dropdown_glyphs\">\n";
		$output .= "\t<span class=\"selected\">\n";
		$output .= "\t\t<b>". ( ( isset( $glyphs[$selected] ) ) ? "<span class=\"$prefix $prefix-$selected\"></span>" : ( $show_option_none ? $show_option_none : current( $glyphs ) ) ) ."</b>\n";
		$output .= "\t\t<i class=\"caret\"></i>\n";
		$output .= "\t</span>\n";
		$output .= "\t<ul>\n";
		$col = 0;
		
		if( $show_option_none ) :
			if( ! $col )
				$output .= "\t\t<li>\n\t\t\t<ul>\n";
			$output .= "\t\t\t\t<li";
			if( ! $selected ) 
				$output .= " class=\"checked\"";
			$output .= ">\n";
	    	$output .= "\t\t\t\t\t<label>\n";
			$output .= "\t\t\t\t\t\t<input type=\"radio\" name=\"$name\" value=\"0\" autocomplete=\"off\" ". checked( ! $selected, true, false ) .">\n";
			$output .= "\t\t\t\t\t\t<span>$show_option_none</span>\n";
			$output .= "\t\t\t\t\t</label>\n";
	    	$output .= "\t\t\t\t</li>\n";
	    	$col++;	
		endif;
		
		foreach( $glyphs as $value => $label ) :
			// Ouverture de ligne
			if( ! $col )
				$output .= "\t\t<li>\n\t\t\t<ul>\n";
			$output .= "\t\t\t\t<li";
			if( $selected === $value )
				 $output .= " class=\"checked\""; 
			$output .= ">\n";
			$output .= "\t\t\t\t\t<label>\n";
			$output .= "\t\t\t\t\t\t<input type=\"radio\" name=\"$name\" value=\"$value\" autocomplete=\"off\" ". checked( ( $selected === $value ), true, false ) .">\n";
			$output .= "\t\t\t\t\t\t<span class=\"$prefix $prefix-$value\"></span>\n";
			$output .= "\t\t\t\t\t</label>\n";
	    	$output .= "\t\t\t\t</li>\n";				
	
			// Fermeture de ligne
			if( ++$col >= $cols ) :
				$output .= "\t\t\t</ul>\n\t\t</li>\n";
				$col = 0;
			endif;
		endforeach;
		// Fermeture de ligne si requise
		if( $col )
			$output .= "\t\t\t</ul>\n\t\t</li>\n";
		$output .= "\t</ul>\n";
		$output .= "</div>\n";
		
		if( $echo )
			echo $output;
		else
			return $output;
	}
}