<?php
namespace tiFy\Lib;

use tiFy\tiFy;

class Chars
{
	/** == Convertion des variables d'environnements d'une chaîne de caractères == **/
	public static function mergeVars( $output, $vars = array(), $regex = "/\*\|(.*?)\|\*/" )
	{
		$callback = function( $matches ) use( $vars )
		{
			if( ! isset( $matches[1] ) )
				return $matches[0];
					
			if( isset( $vars[$matches[1]] ) )
				return $vars[$matches[1]];
						
			return $matches[0];
		};
		
		$output = preg_replace_callback( $regex, $callback, $output );
			
		return $output;
	}
}