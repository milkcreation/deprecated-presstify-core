<?php
namespace tiFy\Components;

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
		/* = Après l'instanciation des plugins et des sets = */	
		'after_setup_tify' => 1	
	);
	
	// Liste des composants déclarés
	private static $Registered = array();
	
	
	/* = DECLENCHEURS = */
	/** == Initialisation == **/
	final public function after_setup_tify()
	{		
		// Déclaration
		if( isset( tiFy::$Params['components'] ) ) :
			foreach( (array) array_keys( tiFy::$Params['components'] ) as $component ) :
				self::register( $component );
			endforeach;
		endif;
		
		do_action( 'tify_component_register' );
		
		// Instanciation
		foreach( (array) self::$Registered as $ClassName ) :
			new $ClassName;
		endforeach;
	}
	
	/* = CONTRÔLEUR = */
	public static function register( $component )
	{
		if( class_exists( "\\tiFy\\Components\\{$component}\\{$component}" ) ) :
			$ClassName	= "\\tiFy\\Components\\{$component}\\{$component}";
		elseif( class_exists( $component ) ) :
			$ClassName	= $component;
		else :
			return;
		endif;
		
		if( ! in_array( $ClassName, self::$Registered ) )
			array_push( self::$Registered, $ClassName );
	}
}