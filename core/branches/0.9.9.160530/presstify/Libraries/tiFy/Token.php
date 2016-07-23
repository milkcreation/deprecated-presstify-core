<?php
/**
 * @see http://www.stepblogging.com/how-to-encrypt-and-decrypt-string-using-php-with-salt/
 */
namespace tiFy\Lib;

class Token
{
	/* = ARGUMENTS = */
	// Clé publique
	private static $PublicKey 	= NONCE_KEY;
	
	// Clé privée
	private static $PrivateKey 	= NONCE_SALT;
	
	/* = METHODES PUBLIQUES = */
	/** == Encryptage d'une chaîne de caractère == **/
	public static function Encrypt( $plain, $key = null, $hmacSalt = null ) 
	{
		if( $key === null ) 
			$key = self::$PublicKey;
		
		self::_checkKey( $key, 'encrypt()' );

		if( $hmacSalt === null ) 
			$hmacSalt = self::$PrivateKey;

		$key = substr( hash( 'sha256', $key . $hmacSalt ), 0, 32 ); # Generate the encryption and hmac key

		$algorithm 	= MCRYPT_RIJNDAEL_128; # encryption algorithm
		$mode 		= MCRYPT_MODE_CBC; # encryption mode

		$ivSize 	= mcrypt_get_iv_size( $algorithm, $mode ); # Returns the size of the IV belonging to a specific cipher/mode combination
		$iv 		= mcrypt_create_iv( $ivSize, MCRYPT_DEV_URANDOM ); # Creates an initialization vector (IV) from a random source
		$ciphertext = $iv . mcrypt_encrypt( $algorithm, $key, $plain, $mode, $iv ); # Encrypts plaintext with given parameters
		$hmac 		= hash_hmac( 'sha256', $ciphertext, $key ); # Generate a keyed hash value using the HMAC method
		
		return base64_encode( $hmac . $ciphertext );
	}
	
	/** == Décryptage d'une chaîne de caractère == **/
	public static function Decrypt( $cipher, $key = null, $hmacSalt = null ) 
	{
		if( $key === null ) 
			$key = self::$PublicKey;
		
		self::_checkKey( $key, 'decrypt()' );
		
		if( empty( $cipher ) )
			wp_die( __( 'Les données à décrypter ne peuvent être vide', 'tify' ), __( 'Erreur de données à décrypter', 'tify' ), 500 ); 
		
		$cipher = base64_decode( $cipher );	
			
		if ( $hmacSalt === null )
			$hmacSalt = self::$PrivateKey;

		$key = substr( hash( 'sha256', $key . $hmacSalt ), 0, 32 ); # Generate the encryption and hmac key.
		
		# Split out hmac for comparison
		$macSize 	= 64;
		$hmac 		= substr( $cipher, 0, $macSize );
		$cipher 	= substr( $cipher, $macSize );
		
		$compareHmac = hash_hmac( 'sha256', $cipher, $key );
		if ( $hmac !== $compareHmac )
			return false;

		$algorithm 	= MCRYPT_RIJNDAEL_128; # encryption algorithm
		$mode 		= MCRYPT_MODE_CBC; # encryption mode
		$ivSize 	= mcrypt_get_iv_size( $algorithm, $mode ); # Returns the size of the IV belonging to a specific cipher/mode combination

		$iv 		= substr( $cipher, 0, $ivSize );
		$cipher 	= substr( $cipher, $ivSize );
		$plain 		= mcrypt_decrypt( $algorithm, $key, $cipher, $mode, $iv );
		
		return rtrim( $plain, "\0" );
	}
	 
	/** == Générateur de clé publique == **/
	public static function KeyGen( $length = 32 ) 
	{
		$charset 	= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$str 		= '';
		$count 		= strlen( $charset );
		
		while( $length-- > 0 ) :
			$str .= $charset[ mt_rand( 0, $count-1 ) ];
		endwhile;
		
		return $str;
	}
	
	/* = METHODES PRIVEES = */
	/** == Vérification d'intégrité de la clé publique == **/
	private static function _checkKey( $key, $method ) 
	{
		if ( strlen( $key ) < 32 ) :
			wp_die( 
				sprintf( __( 'La clé publique %s est invalide, cette clé doit contenir un minimum de 256 bits (32 caractères de long).', 'tify' ), $key ),
				__( 'Clé publique invalide', 'tify' ),
				500
			);
		endif;	
	}
}