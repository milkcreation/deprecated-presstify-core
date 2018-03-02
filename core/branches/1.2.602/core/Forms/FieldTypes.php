<?php
namespace tiFy\Core\Forms;

class FieldTypes extends \tiFy\App\Factory
{
	/* = ARGUMENTS = */
	// Liste des addons prédéfinis
	private static $Predefined					= array(
		'button'				=> 'Button',
		'checkbox'				=> 'Checkbox',
		'dropdown'				=> 'Dropdown',
		'file'					=> 'File',
		'hidden'				=> 'Hidden',
		'html'					=> 'Html',
		'input' 				=> 'Input',
        'number' 				=> 'Number',
		'password'				=> 'Password',
		'radio'					=> 'Radio',
		'recaptcha'				=> 'Recaptcha',
		'simple-captcha-image'	=> 'SimpleCaptchaImage',
		'textarea'				=> 'Textarea',
		'tify_checkbox'			=> 'tiFyCheckbox',
		'tify_dropdown'			=> 'tiFyDropdown',
		'tify_text_remaining'	=> 'tiFyTextRemaining',
		'tify_touchtime'		=> 'tiFyTouchtime'
	);
	
	// Parametres
	/// Liste des type de champs déclarés
	private static $Registered			= array();
			
	/* = PARAMETRAGE = */
	/** == Initialisation des types de champs prédéfinis == **/
	public static function init()
	{
		foreach( (array) self::$Predefined as $id => $name ) :
			self::register( $id, "\\tiFy\\Core\\Forms\\FieldTypes\\{$name}\\{$name}" );
		endforeach;
	}
	
	/** == Déclaration d'un type de champ == **/
	public static function register( $id, $callback, $args = array() )
	{
		// Bypass
		if( array_keys( self::$Registered, $id ) )
			return;
		if ( ! class_exists( $callback ) )
			return;
		
		self::$Registered[$id] = array( 'callback' => $callback, 'args' => $args );    			
	}
	
	/* = CONTROLEURS = */	
	/** == Instanciation d'un élément == **/
	public static function set( $id, $field )
	{
		if( ! isset( self::$Registered[$id] ) )
			return;
		
		$ClassName = self::getOverride( self::$Registered[$id]['callback'] );	
			
		$item = new $ClassName( self::$Registered[$id]['args'] );
		$item->initField( $field );
		
		return $item;		
	}
}