<?php
namespace tiFy\Core\Forms\Buttons\Submit;

use tiFy\Core\Forms\Buttons\Factory;

class Submit extends Factory
{
	/* = ARGUMENTS = */
	// Configuration
	/// Identifiant
	public $ID 			= 'submit';
	
	/// Attributs
	public $Attrs		= array();
	
	
	/* = CONSTRUCTEUR = */		
	public function __construct() 
	{		
		$this->Attrs =  array(
			'label' 			=> __( 'Envoyer', 'tify' ), // Intitulé du bouton
			'before' 			=> '', // Code HTML insérer avant le bouton
			'after' 			=> '', // Code HTML insérer après le bouton
			'container_id'		=> '',
			'container_class'	=> '',
			'class'				=> '',
			'order'				=> 2			
		);
    }
    
    /* = CONTROLEURS = */
	/** == Traitement des attributs de configuration == **/
	public function parseAttrs( $attrs = array() )
	{
		if( is_string( $attrs ) )
			$attrs = array( 'label' => $attrs );

		return wp_parse_args( $attrs, $this->Attrs );
	}    
    
    /** == Affichage == **/
	public function display()
	{						
		$class = ! empty( $this->Attrs['class'] ) ? "tiFyForm-ButtonHandler tiFyForm-ButtonHandler--submit ". $this->Attrs['class'] : "tiFyForm-ButtonHandler tiFyForm-ButtonHandler--submit";
		
		$output  = "";
		$output .= "<div class=\"tiFyForm-Button tiFyForm-Button--". $this->getID() ."\">\n";
		$output .= "\t<input type=\"hidden\" name=\"submit-". $this->form()->getUID() ."\" value=\"submit\"/>\n";
		$output .= "\t<button type=\"submit\" id=\"submit-". $this->form()->getUID() ."\" class=\"$class\" ". $this->getTabIndex() ." >\n";
		$output .= $this->Attrs['label'];
		$output .= "\t</button>\n";
		$output .= "</div>\n";

		return $output;
	}    
}