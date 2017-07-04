<?php
/**
 * Zone de texte limitée
 * 
 * @see http://www.w3schools.com/tags/tag_textarea.asp -> attributs possibles pour le selecteur textarea
 * @see http://www.w3schools.com/jsref/dom_obj_text.asp -> attributs possibles pour le selecteur input
 */

namespace tiFy\Core\Control\TextRemaining;

class TextRemaining extends \tiFy\Core\Control\Factory
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'text_remaining';
	
	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{
		$min = SCRIPT_DEBUG ? '' : '.min';
	    
	    wp_register_style( 'tify_control-text_remaining', self::getAssetsUrl( get_class() ) ."/TextRemaining'. $min .'.css", array( ), '141213' );
		wp_register_script( 'tify_control-text_remaining', self::getAssetsUrl( get_class() ) ."/TextRemaining'. $min .'.js", array( 'jquery' ), '141213', true );
		wp_localize_script( 'tify_control-text_remaining', 'tifyTextRemaining',
			array(
					'plural' => __( 'caractères restants', 'tify' ),
					'singular' => __( 'caractère restant', 'tify' ),
					'none' => __( 'Aucun caractère restant', 'tify' )
			)
		);
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function enqueue_scripts()
	{
		wp_enqueue_style( 'tify_control-text_remaining' );
		wp_enqueue_script( 'tify_control-text_remaining' );		
	}
		
	/* = AFFICHAGE = */
	public static function display( $args = array(), $echo = true )
	{
		static $instance = 0;
		$instance++;
		
		$defaults = array(
			'container_id'		=> 'tify_control_text_remaining-container-'. $instance,
			'id'				=> 'tify_control_text_remaining-'. $instance,
			'feedback_area'		=> '#tify_control_text_remaining-feedback-'. $instance,
			'name'				=> 'tify_control_text_remaining-'. $instance,
			'selector'			=> 'textarea',	// textarea (default) // @TODO | input 
			'value' 			=> '',		
			'attrs'				=> array(),
			'length'			=> 150,	
			'maxlength'			=> true 	// Stop la saisie en cas de dépassement
		);	
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		$output = "";
		$output .= "<div id=\"{$container_id}\" class=\"tify_control_text_remaining-container\">\n";
		switch( $selector ) :
			default :
			case 'textarea' :					
				$output .= "\t<textarea id=\"{$id}\" data-tify_control=\"text_remaining\" data-feedback_area=\"{$feedback_area}\"";
				if( $name )
					$output .= " name=\"{$name}\"";
				if( $maxlength )
					$output .= " maxlength=\"{$length}\"";
				if( $attrs )
					foreach( $attrs as $iattr => $vattr )
						$output .= " {$iattr}=\"{$vattr}\"";
				$output .= ">". wp_unslash( $value ) ."</textarea>\n";
				$output .= "\t<span id=\"tify_control_text_remaining-feedback-{$instance}\" class=\"feedback_area\" data-max-length=\"{$length}\" data-length=\"". strlen( $value ) ."\"></span>\n";
				break;
			case 'input' :					
				$output .= "\t<input id=\"{$id}\" data-tify_control=\"text_remaining\" data-feedback_area=\"{$feedback_area}\"";
				if( $name )
					$output .= " name=\"{$name}\"";
				if( $maxlength )
					$output .= " maxlength=\"{$length}\"";
				if( $attrs )
					foreach( $attrs as $iattr => $vattr )
						$output .= " {$iattr}=\"{$vattr}\"";
				$output .= " value=\"". wp_unslash( $value ) ."\">\n";
				$output .= "\t<span id=\"tify_control_text_remaining-feedback-{$instance}\" class=\"feedback_area\" data-max-length=\"{$length}\" data-length=\"". strlen( $value ) ."\"></span>\n";
				break;
		endswitch;
		$output .= "</div>\n";
		
		if( $echo )
			echo $output;
	
		return $output;
	}
}