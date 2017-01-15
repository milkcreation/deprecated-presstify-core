<?php
namespace tiFy\Environment;

use tiFy\tiFy;

abstract class Set extends \tiFy\Environment\App
{
	/* = CONTROLEURS = */
	/** == == **/
	public function getOverride( $ClassName )
	{
		return tiFy::getOverride( $ClassName );
	}
}