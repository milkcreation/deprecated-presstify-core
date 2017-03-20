<?php 
namespace tiFy\Set;

use tiFy\tiFy;

class Autoload extends \tiFy\Environment\App
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'after_setup_tify'
	);
	
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'after_setup_tify' => 0	
	);
	
	// Liste des jeux de fonctionnalité déclarés
	private static $Registered = array();
	
	/* = DECLENCHEURS = */
	/** == Initialisation == **/
	public function after_setup_tify()
	{
		// Déclaration
		if( isset( tiFy::$Params['set'] ) ) :
			foreach( (array) array_keys( tiFy::$Params['set'] ) as $set ) :
				self::Register( $set );
			endforeach;
		endif;
		
		do_action( 'tify_set_register' );
		
		// Instanciation
		foreach( (array) self::$Registered as $ClassName ) :
			$ClassName = self::getOverride( $ClassName ); 
			new $ClassName;
		endforeach;
	}
	
	/* = CONTROLEURS */
	/** == Déclaration == **/
	public function register( $set )
	{		
		if( class_exists( "\\tiFy\\Set\\{$set}\\{$set}" ) ) :
			$ClassName	= "\\tiFy\\Set\\{$set}\\{$set}";
		elseif( class_exists( $set ) ) :
			$ClassName	= $set;
		endif;
				
		if( ! in_array( $ClassName, self::$Registered ) )
			array_push( self::$Registered, $ClassName );	
	}	
}