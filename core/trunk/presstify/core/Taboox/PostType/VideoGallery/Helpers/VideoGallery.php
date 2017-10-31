<?php
namespace tiFy\Core\Taboox\PostType\VideoGallery\Helpers;

class VideoGallery extends \tiFy\Core\Taboox\Helpers
{
	/* = ARGUMENTS = */
	// Identifiant des fonctions
	protected $ID 				= 'video_gallery';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers 			= array( 'Has', 'Get' );
	
	// Attributs par défaut
	public static $DefaultAttrs	= array(
		'name' 	=> '_tify_taboox_video_gallery',
		'max'	=> -1
	);
	
	/* = VÉRIFICATION = */
	public static function Has( $post_id = null, $args = array() )
	{
		return self::Get( $post_id, $args );
	}
	
	/* = RÉCUPÉRATION = */
	public static function Get( $post_id = null, $args = array() )
	{
		$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
		
		// Traitement des arguments
		$args = wp_parse_args( $args, self::$DefaultAttrs );
		
		return get_post_meta( $post_id, $args['name'], true );
	}
}