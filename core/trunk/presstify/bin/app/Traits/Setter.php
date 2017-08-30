<?php
namespace tiFy\App\Traits;

trait Setter
{	
	// Liste des arguments pouvant être défini
	protected $SetAttrs	= array();
	
	/** == Récupération des données accessibles == **/
	public function __set( $name, $value ) 
	{
		if ( in_array( $name, $this->SetAttrs ) )
			return $this->{$name} = $value;
		return false;
	}
}