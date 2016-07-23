<?php
Class tiFy_Forms_Functions{
	/* = ARGUMENTS = */
	public	// Configuration
	
			// Paramètres
			$options,
			
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
	
	/* = FONCTIONS = */
	/** == Génération d'une chaine de caractère encodée == **/
	function hash( $data ){
		return wp_hash( $data );
	}
	
	/** == == **/
	function referer(){
		$current_domain = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'];
		
		wp_unslash( $_SERVER['REQUEST_URI'] );
	}
	
	/** == == **/
	function get_referer(){
		
	}
	
	/** == == **/
	function base64_decode( $data, $unserialize = true ){
		if( ! is_string( $data ) )
			return $data;
		
		$_data = $data;
		if( $this->is_base64( $_data ) )
			$_data = base64_decode( $data, true );

		if( $unserialize )
			$_data = maybe_unserialize( $_data );

		return $_data;
	}
	
	/** == == **/
	function base64_encode( $data ){
		if( ! is_serialized( $data ) )
			$data = maybe_serialize( $data );
		
		return base64_encode( $data );
	}
	
	private function is_base64( $str ) {
	    if (!preg_match('~[^0-9a-zA-Z+/=]~', $str)) {
	        $check = str_split(base64_decode($str));
	        $x = 0;
	        foreach ($check as $char) if (ord($char) > 126) $x++;
	        if ($x/count($check)*100 < 30) return  true;
	    }
	    return false;
	}
	
	/** == == **/
	public function parse_options( $options, $defaults ){		
		$_options = array();
		if( ! $options ) :
			$_options = $defaults;
		elseif( ! $defaults ) :
			$_options = $options;
		elseif( is_array( $options ) ) :	
			foreach( (array) $defaults as $key => $default ) :
				if( ! is_array( $default ) ) :
					if( isset( $options[ $key ] ) ) :
						$_options[$key] =  $options[ $key ];
					else : 						
						$_options[$key] = $default;
					endif;
				else :
					if( isset( $options[ $key ] ) && is_array(  $options[ $key ] ) ) :
						$_options[$key] = $this->parse_options( $options[ $key ], $default );
					elseif( isset( $options[ $key ] ) )	:				
						$_options[$key] = $options[ $key ];
					else :
						$_options[$key] = $default;
					endif;
				endif;
				// Nettoyage
				if( isset( $options[ $key ] ) && is_array( $options[ $key ] ) )
					unset( $options[ $key ] );
				unset( $defaults[ $key ] );
			endforeach;
			$_options += $options;
		endif;
		
		return $_options;			
	}
	
	/** == == **/
	public function translate_field_value( $subject, $fields, $default = '' ){
		// Bypass 
		if( ! is_string( $subject ) )
			return $default; 
		if( ! preg_match_all( '/%%(.*?)%%([^%%]*)?/', $subject, $matches ) ) // regex plus simple : '/([^\%\%]+)*/'
			return $default;
		if( ! is_array( $matches[1] ) )
			return $default;
		
		$output = "";
		foreach( $matches[1] as $i => $match ) : 
			if( isset( $fields[$match]['value'] ) ) :
				$output .= $fields[$match]['value'];
			endif;
			if( isset( $matches[2][$i] ) ) :
				$output .= $matches[2][$i];
			endif;
		endforeach;			
		
		if( ! $output )
			return $default; 
		else
			return $output; 
	}
}