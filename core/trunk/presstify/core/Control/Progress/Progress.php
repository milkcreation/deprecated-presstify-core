<?php
namespace tiFy\Core\Control\Progress;

use tiFy\Core\Control\Factory;

class Progress extends Factory
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'progress';
	
	// Instance
	private static $Instance;
	
	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{
		wp_register_style( 'tify_control-progress', $this->Url .'/Progress.css', array(), '160605' );
		wp_register_script( 'tify_control-progress', $this->Url .'/Progress.js', array( 'jquery' ), '160605', true );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	final public function enqueue_scripts()
	{
		wp_enqueue_style( 'tify_control-progress' );		
		wp_enqueue_script( 'tify_control-progress' );
	}
		
	/* = AFFICHAGE = */
	public static function display( $args = array() )
	{
		self::$Instance++;
				
		$defaults = array(
			'id'		=> 'tify_control-progress-'. self::$Instance,
			'class'		=> '',
			'title'		=> '',
			'percent'	=> 0,
			'text'		=> '',
			'info'		=> '',
			'cancel'	=> false,
			'backdrop'	=> true
		);	
		$args = wp_parse_args( $args, $defaults );
					
		$footer = function() use ( $args ){
			extract( $args );
			$output  = "";
			$output .= "<div id=\"{$id}\" class=\"tify_control-progress {$class}\" data-tify_control=\"progress\">\n";
			$output .= "\t<div class=\"tify_control-progress-container\">";
			$output .= "\t\t<div class=\"tify_control-progress-header\">\n";
			if( $title )
				$output .= "\t\t\t<h3 class=\"tify_control-progress-title\">{$title}</h3>\n";
			$output .= "\t\t</div>\n";
			$output .= "\t\t<div class=\"tify_control-progress-body\">\n";
			$output .= "\t\t\t<div class=\"tify_control-progress-bar\" style=\"background-position:-{$percent}% 0;\">\n";
			$output .= "\t\t\t\t<div class=\"tify_control-progress-bar-text\">{$text}</div>\n";
			$output .= "\t\t\t</div>\n";
			$output .= "\t\t</div>\n";
			$output .= "\t\t<div class=\"tify_control-progress-footer\">\n";
			$output .= "\t\t\t<div class=\"infos\">{$info}</div>\n";
			$output .= "\t\t</div>\n";
			$output .= "\t</div>\n";
			if( $backdrop )
				$output .= "\t<div id=\"{$id}-backdrop\" class=\"tify_control-progress-backdrop\"></div>\n";
			$output .= "</div>\n";			
			
			echo $output;
		};
			
		add_action( 'wp_footer', $footer ); 
		add_action( 'admin_footer', $footer ); 
	}
}