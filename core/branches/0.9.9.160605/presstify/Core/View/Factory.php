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
		
		// Instanciation de la classe de rappel de l'interface d'administration
		if( ! empty( $this->Attrs['Admin'] ) )
			$this->AdminClass = new Admin( $this->ID, $this->Attrs['Admin'] );
		// Instancaitio  de la classe de rappel de l'interface d'administration
		if( ! empty( $this->Attrs['Front'] ) )
			$this->FrontClass = new Front( $this->ID, $this->Attrs['Front'] );
	}
	
	/* = CONTROLEUR = */
	/** == Récupération de l'identifiant == **/
	public function getID()
	{
		return $this->ID;
	}
	
	/* = ADMIN = */
	/** == Définition d'attribut de l'interface d'administration == **/
	public function setAdminViewAttr( $attr = null, $value = '', $view = null )
	{
		if( $this->AdminClass )
			return $this->AdminClass->setAttr( $attr, $value, $view );
	}
	
	/** == Récupération des attributs de l'interface d'administration == **/
	public function getAdminViewAttrs( $attr = null, $view = null )
	{
		if( $this->AdminClass )
			return $this->AdminClass->getAttrs( $attr, $view );
	}
	
	/** == Récupération de la vue active == **/
	public function getAdminViewActive()
	{
		if( $this->AdminClass )
			return $this->AdminClass->getActive();
	}
	
	/** == Récupération de la vue active de l'interface d'administration == **/
	public function getAdminRender( $view = null )
	{
		if( $this->AdminClass )
			return $this->AdminClass->Render( $view );
	}
	
	/** == Récupération d'une vue pour l'interface d'administration == **/
	public function getAdminView( $view )
	{
		if( $this->AdminClass )
			return $this->AdminClass->getView( $view );
	}
	
	/** == Définition de la vue courante pour l'interface d'administration == **/
	public function setAdminView( $view )
	{
		if( $this->AdminClass )
			return $this->AdminClass->setCurrentView( $view );
	}
	
	/** == Réinitialisation de la vue de la page de rendu active pour l'interface d'administration == **/
	public function resetAdminView()
	{
		if( $this->AdminClass )
			return $this->AdminClass->resetCurrentView();
	}
	
	/* = FRONT = */
	/** == Définition d'attribut de l'interface d'administration == **/
	public function setFrontViewAttr( $attr = null, $value = '', $view = null )
	{
		if( $this->FrontClass )
			return $this->FrontClass->setAttr( $attr, $value, $view );
	}
	
	/** == Récupération des attributs de l'interface d'administration == **/
	public function getFrontViewAttrs( $attr = null, $view = null )
	{
		if( $this->FrontClass )
			return $this->FrontClass->getAttrs( $attr, $view );
	}
	
	/** == Récupération de la vue active == **/
	public function getFrontViewActive()
	{
		if( $this->FrontClass )
			return $this->FrontClass->getActive();
	}
	
	/** == Récupération de la page de rendue active de l'interface utilisateur == **/
	public function getFrontRender( $view = null )
	{
		if( $this->FrontClass )
			return $this->FrontClass->Render( $view );
	}
	
	/** == Récupération d'une vue pour l'interface utilisateur == **/
	public function getFrontView( $view )
	{
		if( $this->FrontClass )
			return $this->FrontClass->getView( $view );
	}
	
	/** == Définition de la vue courante pour l'interface utilisateur == **/
	public function setFrontView( $view )
	{
		if( $this->FrontClass )
			return $this->FrontClass->setCurrentView( $view );
	}
	
	/** == Réinitialisation de la vue de la page de rendu active pour l'interface utilisateur == **/
	public function resetFrontView()
	{
		if( $this->FrontClass )
			return $this->FrontClass->resetCurrentView();
	}
	
	/* = LABEL = */
	/** == Récupération des intitulées == **/
	public function getLabel( $label = '' )
	{
		if( ! is_null( $this->LabelClass ) )
			return $this->LabelClass->Get( $label );
		
		if( ! $this->LabelClass = \tiFy\Core\Labels\Labels::Get( $this->Attrs['Labels'] ) )
			$this->LabelClass = \tiFy\Core\Labels\Labels::Register( $this->Attrs['Labels'] );
		
		return $this->LabelClass->Get( $label );
	}
	
	/* = DB = */
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