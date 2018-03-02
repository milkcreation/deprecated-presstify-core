<?php
/**
 * @Overridable 
 */
namespace tiFy\Core\Forms\FieldTypes\Password;

use tiFy\Core\Forms\FieldTypes\Factory;

class Password extends Factory
{
	/* = ARGUMENTS = */
	// Identifiant
	public $ID 			= 'password';
	
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
		$output .= "<input type=\"password\"";
		/// ID HTML
		$output .= " id=\"". $this->getInputID() ."\"";
		/// Classe
		$output .= " class=\"". join( ' ', $this->getInputClasses() ) ."\"";
		/// Name		
		$output .= " name=\"". esc_attr( $this->field()->getDisplayName() ) ."\"";
		/// Placeholder
		$output .= " placeholder=\"". esc_attr( $this->getInputPlaceholder() ) ."\"";
		/// Attributs
        $output .= $this->getInputHtmlAttrs();
		/// Value
		$output .= " value=\"". esc_attr( $this->field()->getValue() ) ."\"";
		/// TabIndex
		$output .= " ". $this->getTabIndex();
		/// Fermeture
		$output .= "/>";
			
		return $output;
	}
}