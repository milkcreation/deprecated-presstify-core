<?php
namespace tiFy\Set\ContactToggle;

use tiFy\Core\Control\Control;

class ContactToggle extends \tiFy\Set\Factory
{
	
	/* = DECLENCHEURS = */
	/** == == **/
	
	/* = CONTROLEURS = */
	/** == Initialisation == **/
	final protected function _init()
	{
		Control::register( self::getOverride( 'tiFy\Set\ContactToggle\Core\Control\ContactToggle' ) );
	}
}