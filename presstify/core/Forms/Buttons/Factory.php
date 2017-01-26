<?php
namespace tiFy\Core\Forms\Buttons;

class Factory
{
	/* = ARGUMENTS = */
	// Configuration
	// Identifiant du bouton
	public $ID;
	
	/// Attributs de configuration
	public $Attrs		= array();
	
	// Paramètres
	/// Formulaire de référence
	private $Form			= null;
		
	/* = PARAMETRAGE = */
	/** == Initialisation de l'addon pour un formulaire == **/
	final public function init( $form, $attrs )
	{
		// Définition du formulaire de référence
		$this->Form = $form;
		
		// Définition des attributs
		$this->Attrs = $this->parseAttrs( $attrs );
	}	
	
	/** == Récupération de l'identifiant == **/
	final public function getID()
	{
		return $this->ID;
	}
	
	/** == Attributs tabindex de navigation au clavier == **/
	final public function getTabIndex()
	{
		return "tabindex=\"{$this->form()->increasedTabIndex()}\"";
	}
	
	/** == Récupération de l'objet formulaire de référence == **/
	final public function form()
	{
		return $this->Form;
	}
	
	/* = CONTROLEURS = */
	public function parseAttrs()
	{
		return wp_parse_args( $attrs, $this->Attrs );
	}
	
	/** == Affichage == **/
	public function display(){}
}