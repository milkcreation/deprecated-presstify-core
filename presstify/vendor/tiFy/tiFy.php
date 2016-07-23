<?php
namespace tiFy;

class tiFy
{
	/* = ARGUMENTS = */
	public 	$Entity;
	
	/* = CONSTRUCTEUR = */
	public function __construct( $main )
	{
		$this->Entity = new Entity\Bootstrap( $main );
	}
	
	/* = CONTROLEUR */
	/** == == **/
	public function getEntity( $entity_id ){
		if( isset( $this->Entity->{$entity_id} ) )
			return $this->Entity->{$entity_id};
	}
}