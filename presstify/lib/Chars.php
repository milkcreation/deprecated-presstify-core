<?php
namespace tiFy\Lib;

use tiFy\tiFy;

class Chars
{
	/**
	 * Création d'un extrait de texte basé sur les nombre de caractères
	*
	* @param (string) 	$string 	- chaîne de caractère à traiter
	* @param (int) 		$length		- Nombre maximum de caractères de la chaîne
	* @param (string) 	$teaser 	- Délimiteur de fin de chaîne réduite (ex : [...])
	* @param (string) 	$use_tag 	- Détection d'une balise d'arrêt du type <!--more-->
	* @param (bool) 	$uncut		- Préservation de la découpe de mots en fin de chaîne
	*
	* @return string
	*/
	public static function excerpt( $string, $args = array() )
	{
		$defaults = array(
			'length' 		=> 255,				
			'teaser' 		=> ' [&hellip;]',
			'use_tag' 		=> true,
			'uncut' 		=> true
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
	
		$length = abs( $length );
	
		if ( $use_tag && preg_match('/<!--more(.*?)?-->/', $string, $matches) ) :
			$strings = preg_split( '/<!--more(.*?)?-->/', $string );
			$teased = str_replace(']]>', ']]&gt;', $strings[0]);
			$teased = strip_tags( $teased );
			$teased = trim( $teased );
			
			if( $length > strlen( $teased ) ) :
				return $teased . $teaser;
			endif;
		else :
			$string = str_replace(']]>', ']]&gt;', $string);
			$string = strip_tags( $string );
			$string = trim( $string );
			if( $length > strlen( $string ) ) :
				return $string;
			endif;
		endif;
	
		if( $uncut ):
			$string = substr( $string, 0, $length );
			$pos = strrpos( $string, " ");
			
			if( $pos === false ) :
				return substr( $string, 0, $length ) . $teaser;
			endif;
			
			return substr( $string, 0, $pos ) . $teaser;
		else:
			return substr( $string, 0, $length ) . $teaser;
		endif;
	}
	
	/* = Convertion des variables d'environnements d'une chaîne de caractères = */
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