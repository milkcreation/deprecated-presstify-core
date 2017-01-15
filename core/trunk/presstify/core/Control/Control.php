<?php
namespace tiFy\Core\Control;

class Control
{
	/* = ARGUMENTS = */
	// Liste des classes des contrôleurs
	public static $Factories = array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		foreach( glob( __DIR__.'/*', GLOB_ONLYDIR ) as $filename ) :
			$basename 	= basename( $filename );
			$ClassName	= "\\tiFy\\Core\\Control\\$basename\\$basename";
			
			self::register( $ClassName );
		endforeach;
	}
	
	/* = CONTROLEUR = */
	/** == Déclaration == **/
	final public static function register( $ClassName )
	{
		// Bypass
		if( ! class_exists( $ClassName ) )
			return;
		$Class = new $ClassName;
		
		if( ! empty( $Class->ID ) && ! isset( self::$Factories[$Class->ID] ) )
			self::$Factories[$Class->ID] = $Class;
	}
}