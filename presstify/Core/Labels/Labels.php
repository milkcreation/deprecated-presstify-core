<?php
namespace tiFy\Core\Labels;

use tiFy\Environment\Core;

class Labels extends Core
{
	/* = ARGUMENTS = */
	private static $Factories	= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();		
		
		foreach( (array) self::getConfig() as $id => $args ) :
			self::Register( $id, $args );
		endforeach;
		
		do_action( 'tify_labels_register' );
	}
	
	/* = CONTRÔLEURS = */
	/** == Déclaration == **/
	public static function Register( $id, $args = array() )
	{
		self::$Factories[$id] = new Factory( $args );		
	}
	
	/** == Récupération == **/
	public static function Get( $id )
	{
		if( isset( self::$Factories[$id] ) )
			return self::$Factories[$id];
	}
}