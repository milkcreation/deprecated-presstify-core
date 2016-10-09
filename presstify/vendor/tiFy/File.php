<?php
namespace tiFy\Lib;

use tiFy\tiFy;
use tiFy\Lib\Checker;

class File
{
	/** 
	 * Récupère le contenu d'un fichier
	 * @param $filename chemin relatif ou chemin absolue ou url du fichier  
	 */
	public static function getContents( $filename )
	{
		$contents = ''; 
		
		// Vérifie si le chemin du fichier est une url
		if( Checker::isUrl( $filename ) ) :
			if( preg_match( '/^'. preg_quote( site_url( '/' ), '/' ) .'/', $filename ) ) :
				$filename = preg_replace( '/^'. preg_quote( site_url( '/' ), '/' ) .'/', tiFy::$AbsPath, $filename );
				$contents = file_get_contents( $filename );
			else :
				$ch = curl_init();
	
				curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_URL, $filename);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			
				$contents = curl_exec($ch);
				curl_close($ch);
			endif;
		elseif( file_exists( tiFy::$AbsPath . ltrim( $filename ) ) ) :
			$contents = file_get_contents( tiFy::$AbsPath . ltrim( $filename ) );
		elseif( file_exists( $filename ) ) :
			$contents = file_get_contents( $filename );
		endif;
		
		return $contents;
	}
	
}