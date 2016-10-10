<?php
namespace tiFy\Core\Forms\FieldTypes\Input;

use tiFy\Core\Forms\FieldTypes\Factory;

class Input extends Factory
{
	/* = ARGUMENTS = */
	// Identifiant
	public $ID 			= 'input' ;
	
	// Support
	public $Supports 	= array(
		'integrity',
		'label', 
		'placeholder', 
		'request',
		'wrapper'
	);	
	
	// Attributs HTML
	// @see http://www.w3schools.com/html/html_form_attributes.asp
	public $HtmlAttrs	= array(
		'readonly', 
		'disabled',
		'autocomplete',
		'onpaste',
		/* @todo */	
		/* 'size', 'maxlength', 'autofocus', 'height', 'width', 'list', 'min', 'max', 'multiple', 'pattern', 'placeholder', 'required', 'step' */			
	);
		
	/* = CONTROLEURS = */
	/** == Affichage == **/
	public function display()
	{
		$output = "";
				
		// Affichage du champ de saisie
		$output .= "<input type=\"text\"";
		/// ID HTML
		$output .= " id=\"". $this->getInputID() ."\"";
		/// Classe HTML
		$output .= " class=\"". join( ' ', $this->getInputClasses() ) ."\"";
		/// Name		
		$output .= " name=\"". esc_attr( $this->field()->getDisplayName() ) ."\"";
		/// Placeholder
		$output .= " placeholder=\"". esc_attr( $this->getInputPlaceholder() ) ."\"";
		/// Attributs
		foreach( (array) $this->getInputHtmlAttrs() as $k => $v ) :
			$output .= " {$k}=\"{$v}\"";
		endforeach;
		/// Value
		$output .= " value=\"". esc_attr( $this->field()->getValue() ) ."\"";
		/// TabIndex
		$output .= " ". $this->getTabIndex();
		/// Fermeture
		$output .= "/>";
			
		return $output;
	}
}