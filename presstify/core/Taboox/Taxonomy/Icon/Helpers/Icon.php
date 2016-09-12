<?php
namespace tiFy\Core\Taboox\Taxonomy\Icon\Helpers;

use tiFy\Core\Taboox\Helpers;

class Icon extends Helpers
{
	/* = ARGUMENTS = */
	// Identifiant des fonctions
	protected $ID 				= 'term_icon';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers 			= array( 'Get', 'Display' );
	
	// Attributs par défaut
	public static $DefaultArgs	= array();
	
	// Attributs courants
	protected static $Args = array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		static::$DefaultArgs	= array(
			'name' 	=> '_icon',
			'dir' 	=> \tiFy\tiFy::$AbsDir .'/vendor/Assets/svg',
			'attrs'	=> array()
		);
	}
	
	/* == Récupération de l'icône == **/
	public static function Get( $term, $args = array() )
	{
		$term_id = 0;
		if( is_int( $term ) ) :
			$term_id = $term;
		elseif( is_object( $term ) ) :
			$term_id = $term->term_id;
		endif;
		
		if( ! $term_id )
			return;
		
		static::$Args = wp_parse_args( $args, static::$DefaultArgs );

		return get_term_meta( $term_id, static::$Args['name'], true );
	}
	
	/* == Affichage de l'icône == **/
	public static function Display( $term, $args = array(), $echo = true )
	{
		if( ! $icon = static::Get( $term, $args ) )
			return;	
	
		if( file_exists( static::$Args['dir'] ."/{$icon}" ) && ( $content = file_get_contents( static::$Args['dir'] ."/{$icon}" ) ) ) :
		else :
			return;
		endif;	
		
		$ext = pathinfo( static::$Args['dir'] ."/{$icon}", PATHINFO_EXTENSION );
		if( ! in_array( $ext, array( 'svg', 'png', 'jpg', 'jpeg' ) ) )
			return;
		
		switch( $ext ) :
			case 'svg' : 
				$data = 'image/svg+xml';
				break;
			default :
				$data = 'image/'. $ext;
				break;
		endswitch;
		
		$output  = "<img src=\"data:{$data};base64,". base64_encode( $content ) ."\" alt=\"{$icon}\"";
		foreach( (array) static::$Args['attrs'] as $k => $v ) :
			$output .= " {$k}=\"{$v}\"";
		endforeach;
		$output .= "/>";
		
		if( $echo ) :
			echo $output;
		else :
			return $output;
		endif;
	}
}

