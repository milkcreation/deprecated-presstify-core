<?php
namespace tiFy\Components;

use tiFy\Environment\App;

class Autoload extends App
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'after_setup_tify'
	);
	
	/* = CONSTRUCTEUR = */
	final public function after_setup_tify()
	{		
		if( ! isset( $this->Params['components'] ) )
			return;
		foreach( (array) array_keys( $this->Params['components'] ) as $component ) :
			if( class_exists( "\\tiFy\\Components\\{$component}\\{$component}" ) ) :
				$ClassName	= "\\tiFy\\Components\\{$component}\\{$component}";
			elseif( class_exists( $component ) ) :
				$ClassName	= $component;
			else :
				continue;
			endif;

			$reflection = new \ReflectionAnnotatedClass( $ClassName );
			if( $reflection->hasAnnotation( 'Autoload' ) ) :
				new $ClassName;
			endif;
		endforeach;
	}
}