<?php
namespace tiFy\Core\Control\Token;

use tiFy\Core\Control\Factory;

class Token extends Factory
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'token';
	
	// Instance Courante
	static $Instance = 0;
	
	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{
		wp_register_style( 'tify_control-token', $this->Url .'/Token.css', array( 'dashicons' ), '141212' );
		wp_register_script( 'tify_control-token', $this->Url .'/Token.js', array( 'jquery' ), '141212', true );
		
		add_action( 'wp_ajax_tify_control_token_keygen', array( $this, 'AjaxKeyGen' ) );
		add_action( 'wp_ajax_nopriv_tify_control_token_keygen', array( $this, 'AjaxKeyGen' ) );
		add_action( 'wp_ajax_tify_control_token_unmask', array( $this, 'AjaxUnMask' ) );
		add_action( 'wp_ajax_nopriv_tify_control_token_unmask', array( $this, 'AjaxUnMask' ) );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	final public function enqueue_scripts()
	{
		wp_enqueue_style( 'tify_control-token' );
		wp_enqueue_script( 'tify_control-token' );
	}
		
	/* = AFFICHAGE = */
	public static function display( $args = array() )
	{
		self::$Instance++;
		
		$defaults = array(
			// Conteneur
			'id'				=> 'tify_control_token-'. self::$Instance,
			'class'				=> '',
			'name'				=> 'tify_control_token-'. self::$Instance,	
			'value'				=> '',
			'length'			=> 32,
			'maskable'			=> true,	
			'editable'			=> false,	
			'public_key'		=> null,
			'private_key'		=> null,	
			'keygen'			=> true,	
			'default'			=> true,	
			'echo'				=> 1
		);
		
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
					
		// Décryptage
		$plain 		= ! $value ? \tiFy\Lib\Token::KeyGen( $length ) : \tiFy\Lib\Token::Decrypt( $value, $public_key, $private_key );
		// Encryptage
		$hash		= ! $value ? \tiFy\Lib\Token::Encrypt( $plain, $public_key, $private_key ) : $value;
		// Masque
		$mask = '';
		while( strlen( $mask ) < $length )
			$mask .= '*';
		
		// Selecteur HTML
		$output  = "";
		$output .= "<div id=\"{$id}\" class=\"tify_control_token". ( $class ? ' '.$output : '' ) ."". ( $maskable ? ' maskable mask_plain' : 'unmask_plain' ) ."\" data-tify_control=\"token\">\n";
		if( $maskable )
			$output .= "\t<a href=\"#{$id}\" class=\"tify_control_token-unmask\" data-tify_control_token=\"unmask\" data-target=\"#{$id}\" data-mask=\"{$mask}\" data-public=\"". \tiFy\Lib\Token::Encrypt( $public_key ) ."\" data-private=\"". \tiFy\Lib\Token::Encrypt( $private_key ) ."\"></a>\n";
		$output .= "\t<div class=\"tify_control_token-wrapper\" style=\"width:21em;\">\n";

		
		
		$output .= "\t\t<input class=\"tify_control_token-plain\" type=\"". ( $maskable ? 'password' : 'text' ) ."\" size=\"{$length}\" value=\"". ( $maskable ? $mask : $plain ) ."\" autocomplete=\"off\"";
		/** @todo Rendre la chaîne éditable - implique la récupération JS du hashage lors de l'événement keyup **/
		//if( ! $editable )
		$output .= " readonly=\"readonly\"";
		$output .= "/>\n";
		$output .= "\t\t<input class=\"tify_control_token-hash\" type=\"hidden\" name=\"{$name}\" value=\"{$hash}\" autocomplete=\"off\"/>\n";
		$output .= "\t</div>";
		if( $keygen )
			$output .= "\t<a href=\"#{$id}\" class=\"tify_control_token-keygen\" data-tify_control_token=\"keygen\" data-target=\"#{$id}\" data-length=\"{$length}\" data-public=\"". \tiFy\Lib\Token::Encrypt( $public_key ) ."\" data-private=\"". \tiFy\Lib\Token::Encrypt( $private_key ) ."\">". __( 'Générer', 'tify' ) ."</a>\n";	
		$output .= "</div>\n";
		
		if( $echo )
			echo $output;
		else
			return $output;
	}
	
	/** == Génération de clé via AJAX == **/
	final public function AjaxKeyGen()
	{
		$plain			= \tiFy\Lib\Token::KeyGen( (int) $_POST['length'] );
		$public_key 	= \tiFy\Lib\Token::Decrypt( $_POST['public_key'] );
		$private_key 	= \tiFy\Lib\Token::Decrypt( $_POST['private_key'] );
		$hash			= \tiFy\Lib\Token::Encrypt( $plain, $public_key, $private_key );
		
		wp_send_json_success( compact( 'plain', 'hash' ) );
	}
	
	/** == Génération de clé via AJAX == **/
	final public function AjaxUnMask()
	{
		$public_key 	= \tiFy\Lib\Token::Decrypt( $_POST['public_key'] );
		$private_key 	= \tiFy\Lib\Token::Decrypt( $_POST['private_key'] );
		$plain			= \tiFy\Lib\Token::Decrypt( $_POST['hash'], $public_key, $private_key );
		
		wp_send_json_success( compact( 'plain' ) );
	}
}