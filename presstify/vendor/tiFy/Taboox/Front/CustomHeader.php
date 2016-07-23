<?php
namespace tiFy\Taboox\Front;

use tiFy\Taboox\Front;

class CustomHeader extends Front
{
	/* = ARGUMENTS = */
	// Identifiant des fonctions
	protected $ID 				= 'custom_header';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers 			= array();
	
	// Attributs par défaut
	public static $DefaultAttrs	= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();	
	
		add_filter( 'theme_mod_header_image', array( $this, 'theme_mod_header_image' ) );
	}
	
	/** == == **/
	final public function theme_mod_header_image( $url )
	{
		if( is_singular() ) :
			$header = get_post_meta( get_the_ID(), '_custom_header', true );
			if( is_numeric( $header ) && ( $image = wp_get_attachment_image_src( $header, 'full' ) ) ) :
				$url = $image[0];	
			elseif( is_string( $header ) ) :
				$url = $header;
			endif;
		elseif( is_home() && get_option( 'page_for_posts' ) ) :
			$header = get_post_meta( get_option( 'page_for_posts' ), '_custom_header', true );
			if( is_numeric( $header ) && ( $image = wp_get_attachment_image_src( $header, 'full' ) ) ) :
				$url = $image[0];	
			elseif( is_string( $header ) ) :
				$url = $header;
			endif;
		endif;
			
		return $url;
	}
}