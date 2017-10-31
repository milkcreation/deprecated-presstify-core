<?php
namespace tiFy\Core\Taboox\PostType\CustomHeader\Helpers;

class CustomHeader extends \tiFy\Core\Taboox\Helpers
{
	/* = ARGUMENTS = */
	// Identifiant des fonctions
	protected $ID 				= 'custom_header';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers 			= array( 'Get' );
	
	// Attributs par défaut
	public static $DefaultAttrs	= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();	

		add_filter( 'theme_mod_header_image', array( $this, 'theme_mod_header_image' ) );
	}
	
	/** == Récupération de l'url de l'image d'entête == **/
	public static function Get( $url = null )
	{
		if( $global = get_option( 'custom_header', false ) ) :
			if( is_numeric( $global ) && ( $image = wp_get_attachment_image_src( $global, 'full' ) ) ) :
				$url = $image[0];	
			elseif( is_string( $global ) ) :
				$url = $global;
			endif;
		endif;
				
		if( is_home() && get_option( 'page_for_posts' ) ) :
			$header = get_post_meta( get_option( 'page_for_posts' ), '_custom_header', true );
			if( $header && is_numeric( $header ) && ( $image = wp_get_attachment_image_src( $header, 'full' ) ) ) :
				$url = $image[0];	
			elseif( $header && is_string( $header ) ) :
				$url = $header;
			endif;
		else :
			$header = get_post_meta( get_the_ID(), '_custom_header', true );
			if( $header && is_numeric( $header ) && ( $image = wp_get_attachment_image_src( $header, 'full' ) ) ) :
				$url = $image[0];	
			elseif( $header && is_string( $header ) ) :
				$url = $header;
			endif;
		endif;

		return $url;
	}
	
	
	/** == == **/
	final public function theme_mod_header_image( $url )
	{
		$url = static::Get( $url );
		
		return $url;
	}
}