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
	
	// Paramétrage
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
	
	/* = CONFIGURATION = */			
	/* = PARAMETRAGE = */
	/** == Déclaration de bouton == **/
	public static function register( $id, $callback, $args = array() )
	{
		if( array_keys( self::$Registered, $id ) )
			return;
		if ( ! class_exists( $callback ) )
			return;
		
		self::$Registered[$id] = new $callback( $args );
	}
	
	/* = CONTROLEUR = */
	/** == Récupération d'un addon == **/
	public static function get( $id )
	{
		if( isset( self::$Registered[$id] ) )
			return self::$Registered[$id];
	}
		
	/** == Récupération de la liste des addons == **/
	public static function getIds()
	{
		return array_keys( self::$Registered );
	}
	
	/* = CONTROLEURS = */
	/** == Affichage des boutons du fomulaire == **/
	public static function display()
	{		
		////Callbacks::call( 'form_buttons_before_display', array( &$_form['buttons'], $this->master ) );
		
		$form 		= Forms::getCurrent();
		$buttons	= $form->getButtons();
		
		$output = "";
		foreach( (array) $buttons as $id => $attrs ) :
			if( ( ! $button = self::get( $id ) ) || ( $attrs === false ) )
				continue;
				
			$attrs = $button->parseAttrs( $attrs );
			$output .= self::get( $id )->display( $form, $attrs );
		endforeach;
		
		return $output;
	}
}