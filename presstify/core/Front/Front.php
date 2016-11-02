<?php
namespace tiFy\Core\Front;

use tiFy\Environment\Core;

class Front extends Core
{
	/* ARGUMENTS */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'after_setup_tify',
	);
	
	// Liste des vues déclarées
	private static $Factories			= array();
	
	//
	private static $Route				= null;
		
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		// Déclaration des templates
		foreach( (array) self::getConfig() as $id => $args ) :
			self::Register( $id, $args );
		endforeach;	
		
		// Définition de la route courante
		$matches = preg_split( '/\?.*/', $_SERVER['REQUEST_URI'], 2 );
		self::$Route = current( $matches );
	}
	
	/* = DECLENCHEUR = */
	final public function after_setup_tify()
	{		
		do_action( 'tify_front_register' );
	}
		
	/* = CONTRÔLEURS = */
	/** == Déclaration d'une entité == **/	
	public static function Register( $id, $args )
	{		
		return self::$Factories[$id] = new Factory( $id, $args );
	}
	
	/** == Récupération d'une classe == **/
	public static function Get( $id )
	{
		if( isset( self::$Factories[$id] ) )
			return self::$Factories[$id];
	}
	
	/** == == **/
	public static function getRoute()
	{
		return self::$Route;
	}
}