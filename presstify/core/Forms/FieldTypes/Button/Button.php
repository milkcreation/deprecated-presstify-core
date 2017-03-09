<?php
/**
 * @Overridable 
 */
namespace tiFy\Core\Forms\FieldTypes\Button;

class Button extends \tiFy\Core\Forms\FieldTypes\Factory
{
	/* = ARGUMENTS = */
	// Identifiant
	public $ID = 'button';
	
	// Support
	public $Supports = array(
		'wrapper'
	);	
		
	/* = CONTROLEURS = */
	/** == Affichage == **/
	public function display()
	{				
		if( ! $className = \tiFy\Core\Forms\Buttons::getClass( $this->field()->getAttr( 'value' ) ) )
			return;
		
		$button = new $className;
		$button->init( $this->form(), $this->field()->getOptions() );
		
		return $button->display();
	}
}