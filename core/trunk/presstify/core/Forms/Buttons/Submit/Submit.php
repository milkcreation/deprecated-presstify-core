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

		parent::__construct();
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
	public function display( $form, $attrs )
	{						
		$class = ! empty( $attrs['class'] ) ? "tiFyForm-ButtonHandler tiFyForm-ButtonHandler--submit". $attrs['class'] : "tiFyForm-ButtonHandler tiFyForm-ButtonHandler--submit";
		
		$output  = "";
		$output .= "<div class=\"tiFyForm-Button tiFyForm-Button--". $this->getID() ."\">\n";
		$output .= "\t<input type=\"hidden\" name=\"submit-". $form->getUID() ."\" value=\"submit\"/>\n";
		$output .= "\t<button type=\"submit\" id=\"submit-". $form->getUID() ."\" class=\"$class\" >\n";
		$output .= $attrs['label'];
		$output .= "\t</button>\n";
		$output .= "</div>\n";

		return $output;
	}    
}