<?php
namespace tiFy\Core\Forms\Buttons;

abstract class Factory
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
	
	/** == Liste des classes du bouton d'action == **/
	final public function getHandlerClasses()
	{
	    if( is_string( $this->Attrs['class'] ) ) :
	        $classes = array_map( 'trim', explode( ',', $this->Attrs['class'] ) );
	    else :
	       $classes = (array) $this->Attrs['class'];
	    endif;
	        
    	$classes[] = "tiFyForm-ButtonHandler";
    	$classes[] = "tiFyForm-ButtonHandler--". $this->getID();
    	
    	return $this->form()->factory()->buttonClasses( $this, $classes );
	}
	
	/** == Attributs tabindex de navigation au clavier == **/
	final public function getTabIndex()
	{
		return "tabindex=\"{$this->form()->increasedTabIndex()}\"";
	}
	
    /** == == **/
	final public function _display()
	{
		$output  = "";
		
		// Ouverture
		/// ID HTML
		$openId = $this->Attrs['container_id'];
		/// Class HTML
		$openClass = "";
		if( $this->Attrs['container_class'] )
		  $openClass .= $this->Attrs['container_class'] .' ';
		$openClass .= "tiFyForm-Button tiFyForm-Button--". $this->getID();
		
		$output .= $this->form()->factory()->buttonOpen( $this, $openId, $openClass );
		
		$output .= $this->display();
		
		// Fermeture
		$output .= $this->form()->factory()->buttonClose( $this );
		
		return $output;
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
	abstract protected function display();
}