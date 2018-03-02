<?php
namespace tiFy\Lib;

class Svg
{
	/* = Transforme le chemin absolue = */
    public static function pathToDataImage( $filename )
	{
    	if( 'svg' !== pathinfo( $filename, PATHINFO_EXTENSION ) )
    		return;
    	if( ! $svg = @ file_get_contents( $filename ) )
    		return;
    	if( ! $base64_img = base64_encode( $svg ) )
    		return;
    
    	return 'data:image/svg+xml;base64,'.$base64_img;
    }
}