<?php
namespace tiFy\Core\View;

use tiFy\Core\View\Admin;
use tiFy\Core\View\Labels;

class Factory
{
	/* = ARGUMENTS = */
	// Identifiant de la vue
	private		$ID			= null;
	
	// Attributs de la vue
	private		$Attrs 		= array();
	
	// Classe de rappel de l'interface d'administration
	public		$AdminClass	= null;
			
	// Classe de rappel de l'interface visiteur
	private		$FrontClass = null;
	
	// Classe de rappel des intitulés
	private		$LabelClass = null;
	
	// Classe de rappel des données en base
	private		$DbClass 	= null;
		
	/* = CONSTRUCTEUR = */
	public function __construct( $id, $args = array() )
	{
		// Définition de l'identifiant de l'entité
		$this->ID = $id;	

		// Traitement des arguments de personnalisation
		$defaults = array(
			'Admin'		=> null,
			'Front'		=> null,
			'Labels'	=> $this->ID,	
			'Db'		=> $this->ID	
		);

		$this->Attrs = wp_parse_args( $args, $defaults );		
				
		// Définition de la classe de rappel de l'interface d'administration
		if( ! empty( $this->Attrs['Admin'] ) )
			$this->AdminClass = new Admin( $this->ID, $this->Attrs['Admin'] );		
	}
	
	/* = CONTROLEUR = */
	/** == Récupération de l'identifiant == **/
	public function getID()
	{
		return $this->ID;
	}
	
	/** == Récupération des attributs de l'interface d'administration == **/
	public function getAdminViewAttrs( $attr = null, $view = null )
	{
		if( $this->AdminClass )
			return $this->AdminClass->getAttrs( $attr, $view );
	}
	
	/** == Récupération des attributs de l'interface d'administration == **/
	public function getAdminViewActive()
	{
		if( $this->AdminClass )
			return $this->AdminClass->getActive();
	}
	
	/** == Récupération des intitulées == **/
	public function getLabel( $label = '' )
	{
		if( ! is_null( $this->LabelClass ) )
			return $this->LabelClass->Get( $label );
		
		if( ! $this->LabelClass = \tiFy\Core\Labels\Labels::Get( $this->Attrs['Labels'] ) )
			$this->LabelClass = \tiFy\Core\Labels\Labels::Get( 'posts' );
		
		return $this->LabelClass->Get( $label );
	}
	
	/** == Récuoération de la base de données == **/
	public function getDb()
	{
		if( ! is_null( $this->DbClass ) )
			return $this->DbClass;
		
		if( ! $this->DbClass = \tiFy\Core\Db\Db::Get( $this->Attrs['Db'] ) )
			$this->DbClass = \tiFy\Core\Db\Db::Get( 'posts' );
		
		return 	$this->DbClass;
	}
}