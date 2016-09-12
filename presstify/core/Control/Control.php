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
			$ClassName	= "tiFy\\Core\\Control\\$basename\\$basename";
			
			if( class_exists( $ClassName ) ) :
				$Class = new $ClassName;
				self::$Factories[$Class->ID] = $Class;
			endif;
		endforeach;
	}
}