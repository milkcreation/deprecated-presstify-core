<?php
namespace tiFy\App\Traits;

trait Getter
{	
	// Liste des arguments pouvant être récupérés
	protected $GetAttrs	= array();
	
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
