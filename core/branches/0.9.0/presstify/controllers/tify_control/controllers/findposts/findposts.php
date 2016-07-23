<?php
class tiFy_Control_Findposts extends tiFy_Control{
	public $id = 'findposts';
			
	/* = Déclaration des scripts = */
	public function register_scripts()
	{
		wp_register_script( 'tify_controls-findposts', $this->uri ."/findposts.js", array( 'media' ), '160104' );
	}
		
	/* = Affichage du controleur = */
	public function display( $args = array() )
	{
		static $instance;
		if( $instance++ )
			return;
		add_action( 'admin_footer' , array( $this, '_admin_footer' ) );
		
		$defaults = array(
			'id'				=> 'tify_controls-findposts-'. $instance,		
			'name'				=> '',
			'value' 			=> '',
			'echo'				=> 1
		);	
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
			
		$output = "";
		$output .= "<div class=\"tify_findposts\">\n";
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
	
	final public function _admin_footer()
	{
		?>
		<div id="ajax-response"></div>
		<?php find_posts_div();
	}
}