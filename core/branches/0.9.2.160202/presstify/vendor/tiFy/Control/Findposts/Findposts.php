<?php
namespace tiFy\Control\Findposts;

use tiFy\Control\Control;

class Findposts extends Control
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'findposts';
	
	// 
	static $Instance = 0;
			
	/* = MISE EN FILE DES SCRIPTS = */
	public function enqueue_scripts()
	{
		wp_enqueue_style( 'tify_controls-findposts', $this->Url ."/findposts.css", array( 'tify-admin_styles' ), '160104' );
		wp_enqueue_script( 'tify_controls-findposts', $this->Url ."/findposts.js", array( 'media' ), '160104' );
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
			'id'				=> 'tify_controls-findposts-'. self::$Instance,		
			'name'				=> '',
			'value' 			=> '',
			'echo'				=> 1
		);	
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
			
		$output = "";
		$output .= "<div class=\"tify_findposts findposts_btn_link\">\n";
		$output .= "<input type=\"text\" id=\"{$id}\"";
		if( $name )
			$output .= " name=\"{$name}\"";
		$output .= " value=\"{$value}\" /><button onclick=\"findPosts.open( 'target', '#{$id}' ); return false;\">trouver</button>";
		$output .= "</div>";
				
		if( $echo )
			echo $output;
		else
			return $output;
	}
}