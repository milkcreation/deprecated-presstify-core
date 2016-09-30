<?php
namespace tiFy\Core\Forms;

class Addons
{
	/* = ARGUMENTS = */
	// Configuration
	// Liste des addons prédéfinis
	private static $Predefined					= array(
		//'ajax_submit' 		=> 'AjaxSubmit', 
		'cookie_transport' 	=> 'CookieTransport', 
		'mailer' 			=> 'Mailer', 
		//'preview' 			=> 'Preview', 
		'record' 			=> 'Record', 
		'user' 				=> 'User'	
	);
	
	// Paramétrage
	/// Liste des addons déclarés
	private static $Registered			= array();
	
	/// Liste des formulaires actifs par addon
	private static $ActiveForms			= array();
					
	/* = PARAMETRAGE = */
	/** == Initialisation des addons prédéfinis == **/
	public static function init()
	{
		foreach( (array) self::$Predefined as $id => $name ) :
			self::register( $id, "\\tiFy\\Core\\Forms\\Addons\\{$name}\\{$name}" );
		endforeach;
	}				
				
	/** == Déclaration d'un addon == **/
	public static function register( $id, $callback, $args = array() )
	{
		// Bypass
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
		
		// Instanciation de l'addon	
		$item = new self::$Registered[$id]['callback']( self::$Registered[$id]['args'] );
		$item->initForm( $form, $attrs );
		
		// Mise à jour de la liste des formulaires actifs par addon
		if( ! isset( self::$ActiveForms[$id] ) )
			self::$ActiveForms[$id] = array();
		array_push( self::$ActiveForms[$id], $form );			
		
		return $item;		
	}
			
	/* = CONTROLEUR = */
	/** == Récupére les formulaires actif pour un addon ==**/
	public static function activeForms( $addon_id )
	{
		if( isset( self::$ActiveForms[$addon_id] ) )
			return self::$ActiveForms[$addon_id];	
	}
	
}