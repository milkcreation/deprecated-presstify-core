<?php
/**
 * @Overridable 
 */
namespace tiFy\Core\Forms\FieldTypes\Hidden;

use tiFy\Core\Forms\FieldTypes\Factory;

class Hidden extends Factory
{
	/* = ARGUMENTS = */
	// Identifiant
	public $ID 			= 'hidden';
	
	// Support
	public $Supports 	= array(
		'integrity', 
		'request'
	);	
			
	/* = CONTROLEURS = */
	/** == Affichage == **/
	public function display()
	{
		$output = "";
		
		// Affichage de l'intitulé
		if( $this->isSupport( 'label' ) ) :	
			$output .= $this->displayLabel();
		endif;
		
		// Affichage du champ de saisie
		$output .= "<input type=\"hidden\"";
		/// ID HTML
		$output .= " id=\"". $this->getInputID() ."\"";
		/// Classe HTML
		$output .= " class=\"". join( ' ', $this->getInputClasses() ) ."\"";
		/// Name		
		$output .= " name=\"". esc_attr( $this->field()->getDisplayName() ) ."\"";		
		/// Value
		$output .= " value=\"". esc_attr( $this->field()->getValue() ) ."\"";
		/// Fermeture
		$output .= "/>";
			
		return $output;
	}
}