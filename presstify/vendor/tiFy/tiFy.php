<?php
namespace tiFy;

use tiFy\Environment\App;

class tiFy extends App
{
	/* = ARGUMENTS = */
	//
	protected $Entity;
	
	// Classe de rappel du gestionnaire de boîtes à onglets
	protected $Taboox;
	
	// Classe de rappel du gestionnaire d'options
	protected $Options;
	
	// Classe de rappel du gestionnaire de controleurs
	protected $Control;
	
	//
	protected $GetAttrs = array( 'Taboox', 'Options', 'Control' );
	
	/* = CONSTRUCTEUR = */
	public function __construct( $master )
	{
		parent::__construct();
		
		// Instanciation des fonctions d'aides au développement
		new Libraries\Autoload;
		
		// Instanciation des Entités	
		$this->Entity 	= new Entity\Bootstrap;
		
		// Instanciation de l'interface de gestion des boîtes à onglets	
		$this->Taboox 	= new Taboox\Bootstrap;
		
		// Instanciation de l'interface de gestion des options	
		$this->Options 	= new Options\Bootstrap;
		
		// Instanciation de l'interface de gestion des controleurs	
		$this->Control 	= new Control\Bootstrap;
		
		// Instanciation des fonctions d'aides au développement
		new Helpers\Helpers;
		
		
	}
	
	/* = CONTROLEUR */
	/** == == **/
	public function getEntity( $entity_id )
	{
		if( isset( $this->Entity->{$entity_id} ) )
			return $this->Entity->{$entity_id};
	}
}