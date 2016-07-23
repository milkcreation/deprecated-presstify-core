<?php
namespace tiFy\Plugins;

use tiFy\Environment\App;

class Autoload extends App
{	
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'after_setup_tify'
	);
	
	/* = CONSTRUCTEUR = */
	public function after_setup_tify()
	{
		foreach( array_keys( $this->Params['plugins'] ) as $plugin ) :
			
			if( class_exists( $plugin ) ) :
				$ClassName	= $plugin;
			elseif( class_exists( "tiFy\\Plugins\\{$plugin}\\{$plugin}" ) ) :
				$ClassName	= "tiFy\\Plugins\\{$plugin}\\{$plugin}";
			else :
				continue;
			endif;

			new $ClassName;			
		endforeach;
	}
}