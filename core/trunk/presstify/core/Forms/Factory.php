<?php
namespace tiFy\Core\Forms;

class Factory
{
	/* ARGUMENTS */
	// Controleurs
	/// Formulaires
	private $Form;	
	
	/* = CONSTRUCTEUR */
	public function __construct( $id, $attrs = array() )
	{
		$this->Form = new \tiFy\Core\Forms\Form\Form( $id, $attrs );
	}
		
	/* = CONTROLEURS = */
	final public function getForm()
	{
		return $this->Form;
	}

	/* = SURCHARGE = */	
	/** == Affichage du formulaire == **/
	public function display( $echo = false )
	{
		$output = $this->getForm()->display();
		if( $echo )
			echo $output;
		
		return $output;
	}
}