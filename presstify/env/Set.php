<?php
namespace tiFy\Environment;

use tiFy\tiFy;

abstract class Set extends \tiFy\Environment\App
{
	/* = CONTROLEURS = */
	/** == Récupération d'une classe de surcharge == **/
	public function getOverride( $ClassName )
	{
		return tiFy::getOverride( $ClassName );
	}
}