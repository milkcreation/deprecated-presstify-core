<?php
namespace tiFy\Components\DevTools;

use tiFy\Environment\Component;

/** @Autoload */
class DevTools extends Component
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		foreach( (array) self::getConfig() as $addon => $args ) :
			$className = "\\tiFy\\Components\\DevTools\\". $addon;
		
			$args = wp_parse_args( $args, self::getDefaultConfig( $addon ) );
			if( $args['active'] )
				new $className( $args );
		endforeach;
	}
}