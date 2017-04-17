<?php
namespace tiFy\Core\Forms\Buttons\Submit;

class Submit extends \tiFy\Core\Forms\Buttons\Factory
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
		$output  = "";		
		$output .= "\t<input type=\"hidden\" name=\"submit-". $this->form()->getUID() ."\" value=\"submit\"/>\n";
		$output .= "\t<button type=\"submit\" id=\"submit-". $this->form()->getUID() ."\" class=\"". join( ' ', $this->getHandlerClasses() )  ."\" ". $this->getTabIndex() ." >\n";
		$output .= $this->Attrs['label'];
		$output .= "\t</button>\n";

		return $output;
	}    
}