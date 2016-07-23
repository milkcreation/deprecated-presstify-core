<?php
namespace tiFy\Environment\Traits;

trait Setter
{	
	/** == Récupération des données accessibles == **/
	public function __set( $name, $value ) 
	{
		if ( in_array( $name, $this->SetAttrs ) )
			return $this->{$name} = $value;
		return false;
	}
}