<?php
namespace tiFy\Core\Forms\FieldTypes\Radio;

use tiFy\Core\Forms\FieldTypes\Factory;

class Radio extends Factory
{
	/* = ARGUMENTS = */
	// 
	public $Supports = array(
		'integrity',
		'label', 
		'placeholder', 
		'request',
		'wrapper'
	);	
		
	/* = CONTROLEURS = */
	/** == Affichage == **/
	public function display()
	{
		$output  = "";
		
		$output .= "<ul class=\"tiFyForm-FieldChoices\">\n";
		
		$i = 0; 
		foreach( (array) $this->field()->getAttr( 'choices' ) as $value => $label ) :
			$output .= "\t<li class=\"tiFyForm-FieldChoice\">\n";
			$output .= "\t\t<input type=\"radio\"";
			$output .= " id=\"". $this->getInputID() ."-". $i ."\"";
			$output .= "class=\"tiFyForm-FieldChoiceInput\"";
			$output .= " value=\"". esc_attr( $value ) ."\"";
			$output .= " name=\"". esc_attr( $this->field()->getDisplayName() ) ."\"";
			$output .= "". checked( ( $this->field()->getValue() == $value ), true, false ) ."";
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