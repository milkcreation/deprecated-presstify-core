<?php 
namespace tiFy\Core\Control\HolderImage;

class HolderImage extends \tiFy\Core\Control\Factory
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'holder_image';
	
	
	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{
		wp_register_style( 'tify_control-holder_image', self::tFyAppUrl() ."/HolderImage.css", array(), 160714 );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function enqueue_scripts()
	{	
		wp_enqueue_style( 'tify_control-holder_image' );
	}	
	
	/* = Affichage du controleur = */
	public static function display( $args = array(), $echo = true )
	{
		$defaults = array(
			'text'				=> "<span class=\"tiFyControlHolderImage-content--default\">". __( 'Aucun visuel disponible', 'tify' ) ."</span>",
			'ratio'				=> '1:1',
			'background-color'	=> '#E4E4E4',
			'foreground-color'	=> '#AAA',
			'font-size'			=> '1em'
		);
		$args = wp_parse_args( $args, $defaults );
		
		list( $w, $h ) = preg_split( '/:/', $args['ratio'], 2 );
		$sizer = ( $w && $h ) ? "<span class=\"tiFyControlHolderImage-sizer\" style=\"padding-top:". ( ceil( ( 100/$w )*$h ) ) ."%\" ></span>" : "";				
		
		$output  = "";
		$output .= "<div class=\"tiFyControlHolderImage\" data-tify_control=\"holder_image\" style=\"background-color:{$args['background-color']};color:{$args['foreground-color']};\">\n";			
		$output .= $sizer;
		$output .= "\t<div class=\"tiFyControlHolderImage-content\" style=\"font-size:{$args['font-size']}\">{$args['text']}</div>\n";
		$output .= "</div>\n";
		
		if( $echo )
			echo $output;
		
		return $output;
	}
}