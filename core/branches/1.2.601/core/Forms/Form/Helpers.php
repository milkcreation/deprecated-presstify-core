<?php
namespace tiFy\Core\Forms\Form;

class Helpers
{
	/* = Génération d'une chaine de caractère encodée = */
	public static function hash( $data )
	{
		return wp_hash( $data );
	}
	
	/* = = */
	public static function referer()
	{
		$current_domain = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'];
		
		wp_unslash( $_SERVER['REQUEST_URI'] );
	}
	
	/* = = */
	public static function base64Decode( $data, $unserialize = true )
	{
		if( ! is_string( $data ) )
			return $data;
		
		$_data = $data;
		if( self::isBase64( $_data ) )
			$_data = base64_decode( $data, true );

		if( $unserialize )
			$_data = maybe_unserialize( $_data );

		return $_data;
	}
	
	/* = = */
	public static function base64Encode( $data )
	{
		if( ! is_serialized( $data ) )
			$data = maybe_serialize( $data );
		
		return base64_encode( $data );
	}
	
	/* = = */
	private static function is_base64( $str ) 
	{
	    if( ! preg_match( '~[^0-9a-zA-Z+/=]~', $str ) ) :
	        $check = str_split( base64_decode( $str ) );
	        $x = 0;
	        foreach( $check as $char ) :
	        	if( ord( $char ) > 126 ) : 
	        		$x++;
	        	endif;
	        endforeach;
	        if( $x/count( $check )*100 < 30 )
	        	return  true;
	    endif;
	    
	    return false;
	}
	
	/* = = */
	public static function parseArgs( $options, $defaults )
	{		
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
						$_options[$key] = self::parseArgs( $options[ $key ], $default );
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
	
	/* = = */
	public static function parseMergeVars( $subject, $form )
	{		
		if( is_string( $subject ) ) :
			if( preg_match_all( '/([^%%]*)%%(.*?)%%([^%%]*)?/', $subject, $matches ) ) :
				$subject = "";
				foreach( $matches[2] as $i => $slug ) :
					$subject .= $matches[1][$i] . ( ( $field = $form->getField( $slug ) ) ? $field->getValue() : $matches[2][$i] ) . $matches[3][$i];
				endforeach;
			endif;			
		elseif( is_array( $subject ) ) :
			foreach( $subject as $k => &$i ) :
				$i = self::parseMergeVars( $i, $form );
			endforeach;
		endif;
		
		return $subject;
	}
}