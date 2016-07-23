<?php
namespace tiFy\Components;

use tiFy\Environment\App;
use tiFy\tiFy;

class Autoload extends App
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'after_setup_tify'
	);
	
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'after_setup_tify' => 11	
	);
	
	// Liste des composants déclarés
	private static $Registered = array();
	
	
	/* = CONSTRUCTEUR = */
	final public function after_setup_tify()
	{		
		// Déclaration
		if( isset( tiFy::$Params['components'] ) ) :
			foreach( (array) array_keys( tiFy::$Params['components'] ) as $component ) :
				self::Register( $component );
			endforeach;
		endif;
		
		do_action( 'tify_component_register' );
		
		// Instanciation
		foreach( (array) self::$Registered as $ClassName ) :
			new $ClassName;
		endforeach;
	}
	
	/* = CONTRÔLEUR = */
	public static function Register( $component )
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