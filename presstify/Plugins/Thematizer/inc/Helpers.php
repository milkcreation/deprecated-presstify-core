<?php
namespace
{
	/* = Récupération des valeurs hexadécimal de la palette de couleur = */
	function tify_thematizer_get_colors_hex()
	{
		if( ! $palette = get_option( 'tify_thematizer_color_palette', false ) )
			return;
		$colors = $palette['colors'];
		$sort	= $palette['order'];
		
		@array_multisort( $colors, $sort, ASC );
		
		return array_values( $colors );		
	}
}