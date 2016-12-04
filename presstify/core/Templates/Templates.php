<?php
namespace tiFy\Core\Templates;

use \tiFy\Core\Templates\Front\Factory;

final class Templates extends \tiFy\Environment\Core
{
	/* ARGUMENTS */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'init'	
	);
	
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'init'				=> 9
	);
	
	// Liste des gabarits déclarés
	private static $Registered 			= array();
	
	// Liste des fonctions de rappel des gabarits déclarées
	private static $Factory				= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		// Instanciation des contrôleurs
		new Admin\Admin;
		new Front\Front;
	}	
	
	/* = DECLENCHEURS = */
	/** == Initialisation globale == **/
	public function init()
	{		
		/*foreach( (array) self::getConfig() as $templates ) :
			self::Register( $templa );
		endforeach;*/
		
		do_action( 'tify_templates_register' );
	}
		
	/* = CONTRÔLEURS = */
	/** == Déclaration d'une entité == **/	
	public static function register( $id, $attrs = array(), $context )
	{		
		$context = strtolower( $context );
		
		self::$Registered[$context][$id] = $attrs;
	
		switch( $context ) :
			case 'admin' :						
				return self::$Factory['admin'][$id] = new \tiFy\Core\Templates\Admin\Factory( $id, $attrs );
				break;
			case 'front' :
				return self::$Factory['front'][$id] = new \tiFy\Core\Templates\Front\Factory( $id, $attrs );
				break;
		endswitch;
	}
		
	/** == == **/
	public static function listAdmin()
	{
		if( isset( self::$Factory['admin'] ) )
			return self::$Factory['admin'];
	}
	
	/** == == **/
	public static function listFront()
	{
		if( isset( self::$Factory['front'] ) )
			return self::$Factory['front'];
	}
	
	/** == == **/
	public static function getAdmin( $id )
	{
		if( isset( self::$Factory['admin'][$id] ) )
			return self::$Factory['admin'][$id];
	}
	
	/** == == **/
	public static function getFront( $id )
	{
		if( isset( self::$Factory['front'][$id] ) )
			return self::$Factory['front'][$id];
	}
}