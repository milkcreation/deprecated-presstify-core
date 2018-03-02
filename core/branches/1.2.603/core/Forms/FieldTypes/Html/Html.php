<?php
/**
 * @Overridable 
 */
namespace tiFy\Core\Forms\FieldTypes\Html;

use tiFy\Core\Forms\FieldTypes\Factory;

class Html extends Factory
{
	/* = ARGUMENTS = */
	// Identifiant
	public $ID 	       = 'html';
	
	// Support
	public $Supports   = array( 'wrapper' );
	
	
	/* = CONTRÔLEUR = */
	/** == Affichage == **/
	public function display()
	{
		return $this->field()->getAttr( 'value' ); 
	}
}