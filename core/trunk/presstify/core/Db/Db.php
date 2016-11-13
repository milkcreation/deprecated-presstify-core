<?php
namespace tiFy\Core\Db;

use tiFy\Environment\Core;

class Db extends Core
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'init'
	);
	
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'init'				=> 9
	);
		
	private static $Factories	= array();
	
	public static $Query 		= null;
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();		
				
		foreach( (array) self::getConfig() as $id => $args ) :
			self::register( $id, $args );
		endforeach;		
	}
	
	/* = DECLENCHEUR = */
	/** == == **/
	function init()
	{
		do_action( 'tify_db_register' );
	}
	
	/* = CONTRÔLEURS = */
	/** == Déclaration == **/
	public static function register( $id, $args = array() )
	{
		if( isset( $args['cb'] ) ) :
			self::$Factories[$id] = new $args['cb']( $id, $args );
		else :
			self::$Factories[$id] = new Factory( $id, $args );
		endif;
		
		if( self::$Factories[$id] instanceof Factory )
			return self::$Factories[$id];
	}
	
	/** == Vérification == **/
	public static function has( $id )
	{
		return isset( self::$Factories[$id] );
	}
	
	/** == Récupération == **/
	public static function get( $id )
	{
		if( isset( self::$Factories[$id] ) )
			return self::$Factories[$id];
	}
}