<?php
namespace tiFy\Core\Taboox\Helpers\Option;

use tiFy\Core\Taboox\Helpers;

class Imprint extends Helpers
{
	/* = ARGUMENTS = */
	// Identifiant des fonctions
	protected $ID 				= 'imprint';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers 			= array( 'Display' );
	
	/* = AFFICHAGE = */
	public static function Display( $echo = true )
	{
		$page_for_imprint = get_option( 'page_for_imprint' );

		$output =  	"<a href=\"". ( $page_for_imprint ? get_permalink( $page_for_imprint ) : '#' ) ."\""
				. " title=\"". sprintf( __( 'En savoir plus sur %s', 'tify' ), ( $page_for_imprint ? get_the_title( $page_for_imprint ) : __( 'Les mentions légales', 'tify' ) ) ) ."\">"
				. ( $page_for_imprint ? get_the_title( $page_for_imprint ) : __( 'Mentions légales', 'tify' ) )
				. "</a>";

		echo $output;
	}
}