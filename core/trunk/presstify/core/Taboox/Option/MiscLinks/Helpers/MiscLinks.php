<?php
namespace tiFy\Core\Taboox\Option\MiscLinks\Helpers;

use tiFy\Core\Taboox\Helpers;

class MiscLinks extends Helpers
{
	/* = ARGUMENTS = */	
	// Identifiant des fonctions
	protected $ID 				= 'misclinks';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers 			= array( 'Has', 'Display' );
		
	// Attributs par défaut
	public static $DefaultAttrs	= array(
		'name'		=> 'tify_taboox_misclinks',
		'title'   	=> true,
		'caption' 	=> false,
		'image'	  	=> false
	);
		
	/* = VÉRIFICATION = */
	public static function Has( $args = array() )
	{
		return self::Get( $args );
	}
	
	/* = RÉCUPÉRATION = */
	public static function Get( $args = array() )
	{
		$args = wp_parse_args( $args, self::$DefaultAttrs );	
			
		return get_option( $args['name'], false );
	}
	
	/* = AFFICHAGE = */
	public static function Display( $args = array(), $echo = true )
	{
		
		if( ! $links = self::Get( $args ) )
			return;

		$output = "<ul class=\"tify_taboox_misclinks\">\n";
		
		foreach( (array) $links as $link ) :				
			$url 	= ( ! empty( $link['url'] ) ) 	? $link['url'] : '#';				
			$title 	= ( ! empty( $link['title'] ) )	? sprintf( __( 'Lien vers %s', 'tify' ), $link['title'] ) : ( ! empty( $link['url'] ) ? sprintf( __( 'Lien vers %s','tify' ), $link['url'] ) : '' );
			
			$output .= "\t<li>\n";
			$output .= "\t\t<a href=\"{$url}\"";
			if( $title )		
				$output .= " title=\"$title\"";
			$output .= ">\n";
			
			if( ! empty( $link['image'] ) )
				$output .= wp_get_attachment_image( $link['image'], 'thumbnail' );
				
			if( ! empty( $link['caption'] ) )
				$output .= "\t\t\t<div class=\"tify_taboox_misclinks_caption\">{$link['caption']}</div>";
				
			$output .= "\t\t</a>\n";
			$output .= "\t</li>\n";
		endforeach;
			
		$output .= "</ul>";
		
		if( $echo )
			echo $output; 
		
		return $output;
	}
}