<?php
namespace
{
	/* = Récupération des valeurs hexadécimal de la palette de couleur = */
	function tify_theme_get_colors_hex()
	{	
		$hex = array();
		foreach( \tiFy\Plugins\Theme\Theme::getColors() as $color ) :
			if( ! isset( $color['hex'] ) )
				continue;
			array_push( $hex, $color['hex'] );
		endforeach;
			
		return $hex;
	}
}