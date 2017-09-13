<?php 
namespace tiFy\Core\Control\Notices;

class Notices extends \tiFy\Core\Control\Factory
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'notices';
	
	// Instance
	private static $Instance;
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		self::$Instance++;
	}
	
	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{
		wp_register_style( 'tify_control-notices', static::tFyAppUrl( get_class() ) ."/Notices.css", array(), 170130 );
		wp_register_script( 'tify_control-notices', static::tFyAppUrl( get_class() ) ."/Notices.js", array( 'jquery' ), 170130 );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public static function enqueue_scripts()
	{	
		wp_enqueue_style( 'tify_control-notices' );
		wp_enqueue_script( 'tify_control-notices' );
	}	
	
	/* = Affichage du controleur = */
	public static function display( $args = array(), $echo = true )
	{		
		$defaults = array(
			'text'				=> '',
			'id'				=> 'tiFyControl-Notices--'. self::$Instance,
			'class'				=> '',
			'dismissible'		=> false,
			'type'				=> 'info' 
		);		
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		$output  = "";
		$output .= "<div". ( $id ? " id=\"{$id}\"" : "" ) ." class=\"tiFyNotice tiFyNotice--". strtolower( $type )."". ( $class ? " ".$class : "" ) ."\" >";
		
		if( $dismissible ) :
			$output .= "<button type=\"button\" data-dismiss=\"tiFyNotice\">";
			if( is_bool( $dismissible ) ) :
				$output .= "&times;";
			else :
				$output .= (string) $dismissible;
			endif;
			$output .= "</button>";
		endif;
		
		$output .= "<div>{$text}</div>";
		$output .= "</div>";		
		
		if( ! wp_style_is( 'tify_control-notices' ) ) :
			wp_enqueue_style( 'tify_control-notices' );
		endif;
		if( ! wp_script_is( 'tify_control-notices' ) ) :
			wp_enqueue_script( 'tify_control-notices' );
		endif;		
		
		if( $echo )
			echo $output;
			
		return $output;
	}
}