<?php
namespace tiFy\Environment\Traits;

trait Getter
{	
	/** = RÉCUPERATION DE DONNÉES = **/
	public function __get( $name ) 
	{
		if ( in_array( $name, $this->GetAttrs ) )
			return $this->{$name};
		return false;
	}
	
	/** = VÉRIFICATION D'EXISTANCE DE DONNÉES = **/
	public function __isset( $name ) 
	{
		if ( in_array( $name, $this->GetAttrs ) )
			return isset( $this->{$name} );
		return false;
	}
}
