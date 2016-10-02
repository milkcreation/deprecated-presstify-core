<?php
namespace tiFy\Core\Forms\FieldTypes;

use tiFy\Core\Forms\FieldTypes;
use tiFy\Core\Forms\Form\Helpers;
use tiFy\Environment\App;

class Factory extends App
{
	/* = ARGUMENTS = */
	// Configuration
	/// Identifiant du type de champ
	public $ID				= null;
	
	/// Support
	public $Supports		= array();	
	
	/// Attributs HTML
	public $HtmlAttrs		= array();	
	
	/// Options par défaut
	public $Defaults		= array();
	
	/// Fonctions de rappel
	public $Callbacks		= array();
	
	// PARAMETRES
	/// Champ de référence
	private $Field			= null;
	
	/// Formulaire de référence
	private $Form			= null;
	
	/// Options
	private $Options		= null;
		
	/* = PARAMETRAGE = */
	/** == Initialisation du type de champ pour un champ de formulaire == **/
	final public function initField( $field )
	{
		// Définition du champ de référence
		$this->Field = $field;
		
		// Définition du formulaire de référence
		$this->Form = $field->form();

		// Définition des fonctions de court-circuitage
		foreach( (array) $this->Callbacks as $hookname => $args ) :
			if( is_callable( $args ) ) :
				$this->Form->callbacks()->setFieldType( $hookname, $this->getID(), $args );		
			elseif( isset( $args['function'] ) &&  is_callable( $args['function'] ) ) :
				$args = wp_parse_args( $args, array( 'order' => 10 ) );
				$this->Form->callbacks()->setFieldType( $hookname, $this->ID, $args['function'], $args['order'] );
			endif;
		endforeach;
	}
	
	/** == Définition des options == **/
	final public function initOptions( $options )
	{
		$this->Options = Helpers::parseArgs( $options, $this->Defaults );
	}
				
	/* = PARAMETRES = */
	/** == Récupération de l'identifiant == **/
	final public function getID()
	{
		return $this->ID;
	}
		
	/** == Vérification de support == **/
	final public function isSupport( $support )
	{
		return in_array( $support, $this->Supports );
	}
	
	/** == Récupération d'une option == **/
	final public function getOption( $option, $default = '' )
	{
		if( isset( $this->Options[$option] ) )
			return $this->Options[$option];
		
		return $default;
	}
	
	/** == Identifiant HTML de l'interface de saisie == **/
	public function getInputID()
	{
		return "tiFyForm-FieldInput--". $this->field()->formID() ."_". $this->field()->getSlug();
	}
	
	/** == Classes HTML de l'interface de saisie == **/
	public function getInputClasses()
	{
		$classes = array();
		
		if( $this->field()->getAttr( 'input_class' ) )
			$classes[] = $this->field()->getAttr( 'input_class' );
		$classes[] = "tiFyForm-FieldInput";
		$classes[] = "tiFyForm-FieldInput--". $this->getID();
		$classes[] = "tiFyForm-FieldInput--". $this->field()->getSlug();
		
		return $classes;
	}
	
	/** == Texte d'aide de l'interface de saisie == **/
	public function getInputPlaceholder()
	{
		if( ! $placeholder = $this->field()->getAttr( 'placeholder' ) )
			return;
		
		if( is_bool( $placeholder ) ) :
			$placeholder = $this->field()->getAttr( 'label' );
		endif;
			
		return (string) $placeholder;				
	}
	
	/** == Attributs HTML du champs de saisie == **/
	public function getInputHtmlAttrs()
	{
		$attrs = array();				
		
		foreach( $this->HtmlAttrs as $attr ) :		
			if( ! $value = $this->field()->getAttr( $attr ) )	
				continue;
			$attrs[$attr] = HtmlAttrs::getValue( $attr, $this->field()->getAttr( $attr ) );		
		endforeach;
		
		return $attrs;
	}
	
	/* = CONTROLEURS = */
	/** == Récupération de l'objet champ de référence == **/
	final public function field()
	{
		return $this->Field;
	}
	
	/** == Récupération de l'objet formulaire de référence == **/
	final public function form()
	{
		return $this->Form;
	}	
	
	/* = AFFICHAGE = */
	/** == == **/
	final public function _display()
	{
		$output  = "";
		// Affichage de l'intitulé
		if( $this->isSupport( 'label' ) ) :	
			$output .= $this->displayLabel();
		endif;
		
		// Pré-affichage
		$output .= $this->field()->getAttr( 'before' );
		
		$output .= $this->display();
		
		// Post-affichage
		$output .= $this->field()->getAttr( 'after' );
		
		return $output;
	}
	
	/** == Affichage == **/
	public function display(){}
	
	/** == Affichage de l'intitulé de champ == **/
	public function displayLabel()
	{
		$output = "";
		
		$class = array();
		if( $this->field()->getAttr( 'label_class' ) )
			$class[] =  $this->field()->getAttr( 'label_class' );
		$class[] = "tiFyForm-FieldLabel";
		$class[] = "tiFyForm-FieldLabel--". $this->getID();
	
		$output .= "<label for=\"". $this->getInputID() ."\" class=\"". join( ' ', $class ) ."\">\n";
		$output .= $this->field()->getLabel();					
		if( $this->field()->getRequired( 'tagged' ) ) 
			$output .= "<span class=\"tiFyForm-FieldRequiredTag\">*</span>";
		$output .= "</label>\n";
		
		return $output;
	}
}