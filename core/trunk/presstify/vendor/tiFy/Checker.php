<?php
namespace tiFy\Lib;

class Checker
{
	/* = CONTROLEURS = */	
	/** == Vérifie si une chaine de caractères est vide == **/
	public static function isEmpty( $value )
	{
		if( ! $value )
			return false;
		return true;	
	}	
	
	/** == Vérifie si une chaine de caractères ne contient que des chiffres == **/ 
	public static function isInteger( $value )
	{
		if( ! preg_match( '/^[[:digit:]]*$/', $value ) )
			return false;
		return true;	
	}
	
	/** == Vérifie si une chaine de caractères ne contient que des lettres == **/ 
	public static function isAlpha( $value )
	{
		if( ! preg_match( '/^[[:alpha:]]*$/', $value ) )
			return false;
		return true;	
	}
	
	/** == Vérifie si une chaine de caractères ne contient que des chiffres et des lettres == **/ 
	public static function isAlphaNum( $value )
	{
		if( ! preg_match( '/^[[:alnum:]]*$/', $value ) )
			return false;
		return true;	
	}
	
	/** == Vérifie si une chaine de caractères est un email valide == **/ 
	public static function isEmail( $value )
	{
		if( ! preg_match( '/^[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+\.[a-zA-Z]{2,4}$/', $value ) )
			return false;
		return true;
	}
	
	/** == Vérifie si une chaine de caractères est une url valide == **/ 
	public static function isUrl( $value )
	{
		if( ! preg_match( '@^(https?|ftp)://[^\s/$.?#].[^\s]*$@iS', $value ) )
			return false;
		return true;
	}
	
	/** == Vérifie si une chaîne de caractères est une date == **/
	public static function isDate( $value, $format = 'd/m/Y' )
	{
		switch( $format ) :
			default :
				$regex = '^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$';
				break;
		endswitch;
		
		if( ! preg_match( '/'. $regex .'/', $value ) )
			return false;
		return true;
	}
	
	/** == Vérifie si une chaine de caractères repond à un regex personnalisé == **/ 
	public static function customRegex( $value, $regex )
	{
		if( ! preg_match( '#'. preg_quote( $regex ) .'#', $value ) )
			return false;
		return true;	
	}
	
	/** == Vérifie si une chaine de caractères ne contient un nombre de caractères maximum == **/
	public static function MaxLength( $value, $max = 0 )
	{
		if( strlen( $value ) > $max )
			return false;
		return true;	
	}
	
	/** == Vérifie si une chaine de caractères contient un nombre de caractères minimum == **/
	public static function MinLength( $value, $min = 0 )
	{
		if( strlen( $value ) < $min )
			return false;
		return true;	
	}
	
	/** == Vérifie si une chaine de caractères contient un nombre de caractères défini == **/
	public static function ExactLength( $value, $length = 0 )
	{
		if( strlen( $value ) != $length )
			return false;
		return true;	
	}
	
	/** == Vérifie si une chaine de caractères contient des caractères spéciaux  == **/
	public static function hasSpecialChars( $value )
	{
		if( ! preg_match( '/^.*(?=.{7,})(?=.*\d)(?=.*[a-z])(?=.*[\W_]).*$/', $value ) )
			return false;
		return true;	
	}
	
	/** == Vérifie si une chaine de caractères contient des majuscules == **/ 
	public static function hasMaj( $value )
	{
		if( ! preg_match( '/^.*(?=.*[A-Z]).*$/', $value ) )
			return false;
		return true;
	}
	
	/** == Vérifie si une chaine est un mot de passe valide == **
	 * Par défaut le mot de passe doit contenir au moins 1 chiffre, 1 minuscule, 1 majuscule et entre 8 et 16 caractères
	 */
	public static function isValidPassword( $value, $args = array( ) )
	{
		$defaults = array(
			'digit' 		=> 1,
			'letter'		=> 1,
			'maj'			=> 1,
			'special_char' 	=> 1,	
			'min'			=> 8,
			'max'			=> 16			
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );

		if( $min && ( strlen( $value ) < (int) $min ) )
			return false;
		if( $max && ( strlen( $value ) > (int) $max ) )
			return false;
		
		$regex  = "";
		if( $digit )
			$regex .= "(?=(?:.*\d){". (int) $digit .",})";		
		if( $letter )
			$regex .= "(?=(?:.*[a-z]){". (int) $letter .",})";
		if( $maj )
			$regex .= "(?=(?:.*[A-Z]){". (int) $maj .",})";
		if( $special_char )
			$regex .= "(?=(?:.*[!@#$%^&*()\[\]\-_=+{};:,<.>]){". (int) $special_char .",})";	
		
		if( preg_match( '/'. $regex .'/', $value ) )
			return true;
		return false;
	}
	
	/** == Compare deux chaînes de caractères (exemple : cas mot de passe/ confirmation mot de passe ) == **/
	public static function compare( $value, $compare = '' )
	{
		if( $value !== $compare ) 
			return false;	
	
		return true;		
	}
}