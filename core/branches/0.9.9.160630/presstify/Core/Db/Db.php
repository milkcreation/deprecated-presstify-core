<?php
namespace tiFy\Core\Db;

use tiFy\Environment\Core;

class Db extends Core
{
	/* = ARGUMENTS = */
	private static $Factories	= array();
	
	public static $Query 		= null;
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();		
		
		foreach( (array) self::getConfig() as $id => $args ) :
			self::Register( $id, $args );
		endforeach;
		
		do_action( 'tify_db_register' );
	}
	
	/* = CONTRÔLEURS = */
	/** == Déclaration == **/
	public static function Register( $id, $args = array() )
	{
		if( isset( $args['cb'] ) ) :
			self::$Factories[$id] = new $args['cb']( $id, $args );
		else :
			self::$Factories[$id] = new Factory( $id, $args );
		endif;		
	}
	
	/** == Récupération == **/
	public static function Get( $id )
	{
		if( isset( self::$Factories[$id] ) )
			return self::$Factories[$id];
	}
}