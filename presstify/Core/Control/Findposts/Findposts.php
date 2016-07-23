<?php
namespace tiFy\Core\Control\Findposts;

use tiFy\Core\Control\Factory;

class Findposts extends Factory
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'findposts';
	
	// 
	static $Instance = 0;
	
	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{
		wp_register_style( 'tify_control-findposts', $this->Url ."/findposts.css", array( 'tify-admin_styles' ), '160104' );
		wp_register_script( 'tify_control-findposts', $this->Url ."/findposts.js", array( 'media' ), '160104' );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function enqueue_scripts()
	{
		wp_enqueue_style( 'tify_control-findposts' );
		wp_enqueue_script( 'tify_control-findposts' );
	}
		
	/* = AFFICHAGE = */
	public static function display( $args = array() )
	{
		if( ! self::$Instance++ ) :
			$admin_footer = function(){
			?>
				<div id="ajax-response"></div>
				<?php find_posts_div();
			};
			add_action( 'admin_footer' , $admin_footer );
		endif;
		
		$defaults = array(
			'id'				=> 'tify_control-findposts-'. self::$Instance,
			'class'				=> '',
			'name'				=> '',
			'value' 			=> '',
			'placeholder'		=> '',
			'attrs'				=> array(),
			'echo'				=> 1
		);	
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
			
		$output = "";
		$output .= "<div class=\"tify_findposts findposts_btn_link {$class}\">\n";
		$output .= "<input type=\"text\" id=\"{$id}\"";
		if( $name )
			$output .= " name=\"{$name}\"";
		$output .= " value=\"{$value}\" placeholder=\"{$placeholder}\"";
		foreach( (array) $attrs as $k => $v )
			$output .= " {$k}=\"{$v}\"";
		$output .= "/><button onclick=\"findPosts.open( 'target', '#{$id}' ); return false;\">trouver</button>";
		$output .= "</div>";
				
		if( $echo )
			echo $output;
		else
			return $output;
	}
}