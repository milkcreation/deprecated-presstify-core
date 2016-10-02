<?php
namespace tiFy\Core\Forms\FieldTypes\Checkbox;

use tiFy\Core\Forms\FieldTypes\Factory;

class Checkbox extends Factory
{
	/* = ARGUMENTS = */
	// Identifiant
	public $ID = 'checkbox';
	
	// Support
	public $Supports = array(
		'integrity',
		'label',
		'request',
		'wrapper'
	);	
		
	/* = CONTROLEURS = */
	/** == Affichage == **/
	public function display()
	{
		$output = "";
		$output .= "<ul class=\"tiFyForm-FieldChoices\">\n";
		
		$i = 0; 
		foreach( (array) $this->field()->getAttr( 'choices' ) as $value => $label ) :
			$output .= "\t<li class=\"tiFyForm-FieldChoice tiFyForm-FieldChoice--". $this->getID() ." tiFyForm-FieldChoice--". $this->field()->getSlug() ." tiFyForm-FieldChoice--". preg_replace( '/[^a-zA-Z0-9_\-]/', '', $value ) ."\">\n";
			$output .= "\t\t<input type=\"checkbox\"";
			$output .= " id=\"". $this->getInputID() ."-". $i ."\"";
			$output .= "class=\"tiFyForm-FieldChoiceInput tiFyForm-FieldChoiceInput--checkbox\"";
			$output .= " value=\"". esc_attr( $value ) ."\"";
			$output .= " name=\"". esc_attr( $this->field()->getDisplayName() ) ."[]\"";
			$output .= "". checked( in_array( $value, (array) $this->field()->getValue() ), true, false ) ."";
			foreach( (array) $this->getInputHtmlAttrs() as $attr ) :
				$output .= " {$k}=\"{$v}\"";
			endforeach;
			$output .= "/>";
			$output .= "\t\t<label for=\"". $this->getInputID() ."-". $i ."\" class=\"tiFyForm-FieldChoiceLabel\">$label</label>";
			$output .= "\t</li>";
			$i++;
		endforeach;			
		
		$output .= "</ul>";
									
		return $output;
	}
}