<?php
Class tiFy_Forms_Integrity{
	/* = ARGUMENTS = */
	public	// Configuration
	
			// Paramètres
			$callbacks		= array(),
			$errors 		= array(),
			
			// Références
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Forms $master ) {
        // Définition du contrôleur principal
        $this->master = $master;
		
		// Initialisation de la configuration
		$this->config();
    }
	
	/* = CONFIGURATION = */
	private function config(){}
	
	/* = PARAMETRAGE = */	
	/** == Traitement des fonctions de callback liées aux tests d'intégrités == **/
	public function check( $callback, $value ){
		// Bypass		
		if( empty( $callback ) /*|| empty( $value )*/ )
			return;
		
		if( is_string( $callback ) ) :			
			$cb = $this->parse( array( 'function' => $callback, 'value' => $value ) );
			$this->errors[] = $this->execute( $cb );		
		elseif( is_array( $callback ) ) :
			if( isset( $callback['function'] ) ) :
				$callback['value'] = $value;
				$cb = $this->parse( $callback );
				$this->errors[] = $this->execute( $cb );
			else :
				foreach( $callback as $cb ) :
					$this->check( $cb, $value );
				endforeach;		
			/*if( $cb = $this->get( $callback ) )	:							
				if( is_array( $cb['args']) ) :				
					array_unshift( $cb['args'], $value );	
					$this->errors[] = $this->execute( $cb );
				else :
					$cb['args'] = array( $value );					
					$this->errors[] = $this->execute( $cb );	
				endif;			
			else :
				foreach( $callback as $cb )
					$this->check( $cb, $value );
			endif;*/
			endif;
		endif;
		
	}
	
	/** == Récupération d'un objet callback == **/
	private function parse( $callback ){	
		$defaults = array(
			'value' => '',
			'args' => null,
			'error' => __( 'Le format du champ "%s" est invalide', 'tify' )
		);
		return $this->master->functions->parse_options( $callback, $defaults );
	}
	
	/**
	 * Execute la fonction de callback
	 */
	 private function execute( $callback ){
	 	if( ! method_exists( $this, $callback['function'] ) )
			return;

	 	if( call_user_func( array( &$this, $callback['function'] ), $callback['value'], $callback['args'] ) === false )
			return $output = $callback['error'];
	 }
		
	/**
	 * TEST D'INTEGRITE
	 */
	/**
	 * Methode par défaut
	 */ 
	public function return_true( $value, $args = array() ){
		return true;	
	}	
	
	/**
	 * Vérifie si une chaine de caractères est vide
	 */ 
	public function is_empty( $value, $args = array() ){
		if( ! $value )
			return false;
		return true;	
	}	
	
	/**
	 * Vérifie si une chaine de caractères ne contient que des chiffres
	 */ 
	public function is_integer( $value, $args = array() ){
		if( ! preg_match( '/^[[:digit:]]*$/', $value ) )
			return false;
		return true;	
	}
	
	/**
	 * Vérifie si une chaine de caractères ne contient que des lettres
	 */ 
	public function is_alpha( $value, $args = array() ){
		if( ! preg_match( '/^[[:alpha:]]*$/', $value ) )
			return false;
		return true;	
	}
	
	/**
	 * Vérifie si une chaine de caractères ne contient que des chiffres et des lettres (tadantantan...tadantan)
	 */ 
	public function is_alphanum( $value, $args = array() ){
		if( ! preg_match( '/^[[:alnum:]]*$/', $value ) )
			return false;
		return true;	
	}
	
	/**
	 * Vérifie si une chaine de caractères est un email valide
	 */ 
	public function is_email( $value, $args = array() ){
		if( ! preg_match( '/^[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+\.[a-zA-Z]{2,4}$/', $value ) )
			return false;
		return true;
	}
	
	/**
	 * Vérifie si une chaine de caractères est une url valide
	 */ 
	public function is_url( $value, $args = array() ){
		if( ! preg_match( '/^(http|https|ftp)\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&amp;%\$#\=~])*$/', $value ) )
			return false;
		return true;
	}
	
	/**
	 * Vérifie si une chaîne de caractères est une date
	 */
	public function is_date( $value, $args = array() ){
		if( ! preg_match( '/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $value ) )
			return false;
		return true;
	}
	
	/**
	 * Vérifie si une chaine de caractères repond à un regex personnalisé
	 */ 
	public function check_regex( $value, $args = array() ){
		$regex = $args[0];
		if( ! preg_match( '#'.$regex.'#', $value ) )
			return false;
		return true;	
	}
	
	/**
	 * Vérifie si une chaine de caractères ne contient un nombre de caractères maximum 
	 */
	public function check_maxlength( $value, $args = array() ){
		$limit = $args[0];
		if( strlen( $value ) > $limit )
			return false;
		return true;	
	}
	
	/**
	 * Vérifie si une chaine de caractères contient un nombre de caractères minimum 
	 */
	public function check_minlength( $value, $args = array() ){
		$limit = $args[0];
		if( strlen( $value ) < $limit )
			return false;
		return true;	
	}
	
	/**
	 * Vérifie si une chaine de caractères contient un nombre de caractères requis 
	 */
	public function check_equallength( $value, $args = array() ){
		$limit = $args[0];
		if( strlen( $value ) != $limit )
			return false;
		return true;	
	}
	
	/**
	 * Vérifie si une chaine de caractères contient des caractères spéciaux 
	 */
	public function check_specialchars( $value, $args = array() ){
		if( ! preg_match( '/^.*(?=.{7,})(?=.*\d)(?=.*[a-z])(?=.*[\W_]).*$/', $value ) )
			return false;
		return true;
	
	}
	
	/**
	 * Vérifie si une chaine de caractères contient des majuscules 
	 */
	public function check_maj( $value, $args = array() ){
		if( ! preg_match( '/^.*(?=.*[A-Z]).*$/', $value ) )
			return false;
		return true;
	}
			
	/**
	 * Vérifie si la valeur d'un champ est égale à celle d'un autre champ (exemple : cas mot de passe/ confirmation mot de passe)
	 */
	public function compare( $value, $args = array() ){
		$_request = $this->master->handle->original_request;
		
		if( ! $_form = $this->master->forms->get_current() )
			return false;
		
		if( preg_match( '#%%(.*)%%#', $args[0], $matches ) && ( isset( $_request[$_form['prefix']][$_form['ID']][$matches[1]] ) ) ) :
			if( $value === $_request[$_form['prefix']][$_form['ID']][$matches[1]] ) : 
				return true;	
			endif;
		endif;	
		return false;
		
	}
	
	/**
	 * Verifie si la valeur d'un champ est un mot de passe valide
	 * 
	 * Le mot de passe doit contenir au moins 1 chiffre, 1 minuscule, 1 majuscule et entre 8 et 16 caractères
	 */
	public function is_valid_password( $value, $args = array( ) ){
		$defaults = array(
			'digit' 		=> 1,
			'letter'		=> 1,
			'maj'			=> 1,
			'special_char' 	=> 1,	
			'min'			=> 8,
			'max'			=> 16			
		);
		$args = $this->master->functions->parse_options( $args, $defaults );
		extract( $args );

		$regex  = "";
		if( $digit )
			$regex .= "(?=(?:.*\d){". (int) $digit .",})";		
		if( $letter )
			$regex .= "(?=(?:.*[a-z]){". (int) $letter .",})";
		if( $maj )
			$regex .= "(?=(?:.*[A-Z]){". (int) $maj .",})";
		if( $special_char )
			$regex .= "(?=(?:.*[!@#$%^&*()\[\]\-_=+{};:,<.>]){". (int) $special_char .",})";	
		if( $min && ( strlen( $value ) < (int) $min ) )
			return false;
		if( $max && ( strlen( $value ) > (int) $max ) )
			return false;
		
		if( preg_match( '/'. $regex .'/', $value ) )
			return true;
		return false;
	}
}