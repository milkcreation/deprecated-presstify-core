<?php
namespace tiFy\Core\Labels;

use tiFy\Environment\Core;

class Labels extends Core
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $tFyAppActions				= array(
		'init',
	);
	
	// Ordres de priorité d'exécution des actions
	protected $tFyAppActionsPriority	= array(
		'init'				=> 9
	);
	
	public static $Factories	= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();		

		foreach( (array) self::tFyAppConfig() as $id => $args ) :
			self::Register( $id, $args );
		endforeach;		
	}
	
	/* = DECLENCHEURS = */
	/** == Initialisation globale == **/
	final public function init()
	{		
		do_action( 'tify_labels_register' );
	}
	
	/* = CONTRÔLEURS = */
	/** == Déclaration == **/
	public static function Register( $id, $args = array() )
	{
		return self::$Factories[$id] = new Factory( $args );		
	}
	
	/** == Récupération == **/
	public static function Get( $id )
	{
		if( isset( self::$Factories[$id] ) )
			return self::$Factories[$id];
	}
}