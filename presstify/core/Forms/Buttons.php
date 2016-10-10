<?php
namespace tiFy\Core\Forms;

class Buttons
{
	/* = ARGUMENTS = */
	// Configuration
	// Liste des addons prédéfinis
	private static $Predefined					= array(
		'submit'	=> 'Submit'	
	);
	
	// Paramétres
	/// Liste des addons déclarés
	private static $Registered					= array();
	
	/* = PARAMETRAGE = */
	/** == Initialisation des addons prédéfinis == **/
	public static function init()
	{
		foreach( (array) self::$Predefined as $id => $name ) :
			self::register( $id, "\\tiFy\\Core\\Forms\\Buttons\\{$name}\\{$name}" );
		endforeach;
	}
	
	/** == Déclaration de bouton == **/
	public static function register( $id, $callback, $args = array() )
	{
		if( array_keys( self::$Registered, $id ) )
			return;
		if ( ! class_exists( $callback ) )
			return;
		
		self::$Registered[$id] = array( 'callback' => $callback, 'args' => $args ); 
	}
	
	/** == Instanciation d'un élément == **/
	public static function set( $id, $form, $attrs = array() )
	{
		if( ! isset( self::$Registered[$id] ) )
			return;
		
		$item = new self::$Registered[$id]['callback']( self::$Registered[$id]['args'] );
		$item->init( $form, $attrs );
		
		return $item;			
	}
}