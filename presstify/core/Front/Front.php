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
	private static $Factories		= array();
	
	public function __construct()
	{
		parent::__construct();
		
		foreach( (array) self::getConfig() as $id => $args ) :
			self::Register( $id, $args );
		endforeach;	
	}
	
	/* = DECLENCHEUR = */
	final public function after_setup_tify()
	{		
		do_action( 'tify_front_register' );
	}
		
	/* = CONTRÔLEURS = */
	/* = Déclaration d'une entité = */	
	public static function Register( $id, $args )
	{		
		return self::$Factories[$id] = new Factory( $id, $args );
	}
	
	/* = Récupération d'une classe = */
	public static function Get( $id )
	{
		if( isset( self::$Factories[$id] ) )
			return self::$Factories[$id];
	}
}