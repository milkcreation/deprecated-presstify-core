<?php
namespace tiFy\Core\Forms\Form;

class Checker extends \tiFy\Lib\Checker 
{
	/* = ARGUMENTS = */
	// Paramètres
	/// Formulaire de référence
	private static $Field			= null;
	
	/// Champ de référence
	private static $Form			= null;
	
	/// Cartographie des alias de function
	private $Map 			= array(
		// Vérifie si une chaine de caractères est vide
		'is_empty' 				=> 'isEmpty',
		// Vérifie si une chaine de caractères ne contient que des chiffres	
		'is_integer'			=> 'isInteger',			
		// Vérifie si une chaine de caractères ne contient que des lettres
		'is_alpha'				=> 'isAlpha',				
		// Vérifie si une chaine de caractères ne contient que des chiffres et des lettres (spéciale dédicace à Bertrand Renard)
		'is_alphanum'			=> 'isAlphaNum',			
		// Vérifie si une chaine de caractères est un email valide
		'is_email'				=> 'isEmail',			
		// Vérifie si une chaine de caractères est une url valide
		'is_url'				=> 'isUrl',		
		// Vérifie si une chaîne de caractères est une date
		'is_date'				=> 'isDate',			
		// Vérifie si une chaine de caractères repond à un regex personnalisé 
		'check_regex'			=> 'customRegex',
		// Vérifie si une chaine de caractères contient un nombre de caractères maximum
		'check_max_length'		=> 'MaxLength',
		// Vérifie si une chaine de caractères contient un nombre de caractères minimum 
		'check_min_length'		=> 'MinLength',
		// Vérifie si une chaine de caractères contient un nombre de caractères défini
		'check_equal_length'	=> 'ExactLength',
		// Vérifie si une chaine de caractères contient des caractères spéciaux
		'check_specialchars'	=> 'hasSpecialChars',
		// Vérifie si une chaine de caractères contient des majuscules
		'check_maj'				=> 'hasMaj',
		// Vérifie si la valeur d'un champ est un mot de passe valide
		'is_valid_password'		=> 'isValidPassword'
	); 
	
	/* = CONSTRUCTEUR = */
	public function __construct( \tiFy\Core\Forms\Form\Field $Field )
	{			
		// Définition du champ de référence
		self::$Field = $Field;
		
		// Définition du formulaire de référence
		self::$Form = $Field->form();
	}		
	
	/* = CONTROLEUR = */
	/** == Appel == **/
	public function call( $value, $callback )
	{
		// Traitement des arguments
		$check  = false; 
		$fn 	= $callback['function'];
		$args 	= (array) $callback['args'];		
		array_unshift( $args, $value );
	
		if( is_string( $fn ) && method_exists( __CLASS__, $fn ) ) :
			$check = call_user_func_array( array( __CLASS__, $fn ), $args );
		elseif( is_string( $fn ) && isset( $this->Map[ $fn ] ) && method_exists( __CLASS__,  $this->Map[ $fn ] ) ) :
			$check = call_user_func_array( array( __CLASS__, $this->Map[ $fn ] ), $args );
		elseif( is_callable( $fn ) ) :
			$check = call_user_func_array( $fn, $args );
		endif;
		
		return $check;
	}
	
	/** == Méthode par défaut == **/ 
	public static function __return_true( $value )
	{
		return true;	
	}
					
	/** == Vérifie si la valeur d'un champ est égale à celle d'un autre champ (exemple : cas mot de passe/ confirmation mot de passe ) == **/
	public static function compare( $value, $compare = '' )
	{
		if( preg_match( '#%%(.*)%%#', $compare, $matches ) && ( $field = self::$Form->getField( $matches[1] ) ) ) :
			$compare = $field->getValue( true );
		endif;

		if( $value !== $compare ) 
			return false;	
	
		return true;		
	}	
}