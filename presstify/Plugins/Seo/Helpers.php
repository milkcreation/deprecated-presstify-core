<?php
/** == Récupération des données opengraph déclarée == **/
function tify_seo_opengraph_meta( $property = null )
{
	global $tify_opengraph_meta;

	if( ! $property )
		return $tify_opengraph_meta;
	elseif( isset( $tify_opengraph_meta[$property] ) )
		return $tify_opengraph_meta[$property];
}