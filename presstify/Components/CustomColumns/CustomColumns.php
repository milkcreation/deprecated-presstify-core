<?php
namespace tiFy\Components\CustomColumns;

use \tiFy\Environment\Component;

/** @Autoload */
class CustomColumns extends Component
{
	/* = ARGUMENTS = */
	/** == ACTIONS == **/
	// Liste des Actions à déclencher
	protected $CallActions				= array(
		'init'
	);
	
	/** == CONFIGURATION == **/
	public static $Factories			= array();
	
	/* = ACTIONS = */
	/** == Initialisation globale == **/
	final public function init()
	{
		foreach( (array) self::getConfig() as $classname => $args ) :
			$class = "\\tiFy\Components\CustomColumns\\Column\\". $classname;
			if( ! \class_exists( $class ) )
				continue;
			new $class( $args );		
		endforeach;
	}
}