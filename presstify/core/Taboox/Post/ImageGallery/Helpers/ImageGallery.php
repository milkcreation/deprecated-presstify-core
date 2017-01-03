<?php
namespace tiFy\Core\Taboox\Post\ImageGallery\Helpers;

class ImageGallery extends \tiFy\Core\Taboox\Helpers
{
	/* = ARGUMENTS = */
	// Identifiant des fonctions
	protected $ID 				= 'image_gallery';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers 			= array( 'Has', 'Get', 'Display' );

	// Attributs par défaut
	public static $DefaultAttrs	= array(
		'name' 	=> '_tify_taboox_image_gallery',
		'max'	=> -1
	);
	
	/* = VÉRIFICATION = */
	public static function Has( $post_id = null, $args = array() )
	{
		return static::Get( $post_id, $args );
	}
	
	/* = RÉCUPÉRATION = */
	public static function Get( $post_id = null, $args = array() )
	{
		$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
		
		// Traitement des arguments
		$args = wp_parse_args( $args, static::$DefaultAttrs );
		
		return get_post_meta( $post_id, $args['name'], true );
	}
	
	/* = AFFICHAGE = */
	public static function Display( $post_id = null, $args = array(), $echo = true )
	{
		$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
		
		$output  = "";
		if( $output = apply_filters( 'tify_taboox_image_gallery_pre_output', $output, $post_id, $args ) ) :
			if( $echo )
				echo $output;
			else
				return $output; 
			return;
		endif;
		
		// Bypass
		if( ! $images = static::Get( $post_id, $args ) )
			return;
			
		static $instances = 0; $instances++;
	
		$output  = "";
		$output .= "<div id=\"tify_taboox_image_gallery-{$post_id}-{$instances}\" class=\"tify_taboox_image_gallery\">\n";
		$output .= "\t<div class=\"viewer\">\n";
		$output .= "\t\t<ul class=\"roller\">\n";
		foreach( (array) $images as $img_id )
			if( ! empty( $img_id ) && ( $src = wp_get_attachment_image_src( $img_id, 'full' ) ) )
				$output .= "\n\t\t\t<li><div class=\"item-image\" style=\"background-image:url({$src[0]});\"></div></li>\n";
		$output .= "\t\t</ul>\n";
		$output .= "\t</div>\n";
		$output .= "\t<a href=\"#tify_taboox_image_gallery-{$post_id}-{$instances}\" class=\"nav prev\"></a>\n";
		$output .= "\t<a href=\"#tify_taboox_image_gallery-{$post_id}-{$instances}\" class=\"nav next\"></a>\n";
		$output .= "\t\t<ul class=\"tabs\">";
		
		foreach( (array) $images as $n => $img_id )
			if( ! empty( $img_id ) && ( $src = wp_get_attachment_image_src( $img_id, 'full' ) ) )
				$output .= "\n\t\t<li><a href=\"#tify_taboox_image_gallery-{$post_id}-{$instances}\">".( $n+1 )."</a></li>\n";	
		$output .= "\t\t</ul>\n"; 
		$output .= "</div>\n";	
		
		$output = apply_filters( 'tify_taboox_image_gallery', $output, $images, $instances, $post_id );
		
		if( $echo )
			echo $output;
		return $output; 
	}
}