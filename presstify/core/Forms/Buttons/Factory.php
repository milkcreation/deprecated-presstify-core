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
	
	/* = CONSTRUCTEUR = */				
	public function __construct() 
	{			
		// Définition de l'ID
		$this->_setID();		
    }
	
	/* = CONFIGURATION = */
	/** == Définition de l'ID == **/
	private function _setID()
	{
		if( $this->ID )
			return;
		$classname = get_class( $this );

		if( ! $this->ID = array_search( $classname, Buttons::get() ) )
			$this->ID = $classname;
	}
	
	/* = CONTROLEURS = */
	/** == Récupération de l'identifiant == **/
	final public function getID()
	{
		return $this->ID;
	}
	
	/** == Traitement des attributs de configuration == **/
	public function parseAttrs( $attrs = array() )
	{
		return wp_parse_args( $attrs, $this->Attrs );
	}
	
	/** == Affichage == **/
	public function display( $form, $args ){}
}