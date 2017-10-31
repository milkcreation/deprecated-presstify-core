<?php
namespace tiFy\Core\Taboox\Post\RichExcerpt\Helpers;

class RichExcerpt extends \tiFy\Core\Taboox\Helpers
{
	/* = ARGUMENTS = */
	// Intitulés des prefixes des fonctions		
	protected $Prefix 			= 'the';
	// Identifiant des fonctions
	protected $ID 				= 'rich';
	// Liste des methodes à translater en Helpers
	protected $Helpers		= array( 'Excerpt' );
	
	/* = RÉCUPÉRATION = */
	public static function Excerpt( $post_id = null )
	{
		$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
		
		echo apply_filters( 'the_content', get_post_field( 'post_excerpt', $post_id ) );
	}
}