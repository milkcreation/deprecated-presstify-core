<?php
namespace tiFy\Entity;

use tiFy\Environment\App;

class Bootstrap extends App
{
	// Actions à déclencher	
	protected 	$CallActions		= array( 'after_setup_theme' );
		
	/* = DECLENCHEMENT DES ACTIONS = */
	/** == Après le chargement du thème == **/
	protected function after_setup_theme()
	{	
		// Bypass	
		if( ! isset( $this->Params['schema'] ) )
			return;	

		foreach( $this->Params['schema'] as $entity_id => $opts )
			$this->{$entity_id} = new Entity( $entity_id, $opts );
	}
}