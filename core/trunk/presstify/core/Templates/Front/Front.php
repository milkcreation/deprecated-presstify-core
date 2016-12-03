<?php
namespace tiFy\Core\Templates\Front;

class Front extends \tiFy\Environment\Core
{
	/* ARGUMENTS */	
	// 
	private static $Route				= null;
		
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		// Définition de la route courante
		$matches = preg_split( '/\?.*/', $_SERVER['REQUEST_URI'], 2 );
		self::$Route = current( $matches );
	}
		
	/* = CONTRÔLEURS = */	
	/** == == **/
	public static function getRoute()
	{
		return self::$Route;
	}
}