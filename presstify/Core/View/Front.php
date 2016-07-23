<?php
namespace tiFy\Core\View;

use tiFy\Environment\App;

final class Front extends App
{
	/* = ARGUMENTS = */
	// ACTIONS
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'init'	
	); 
	// CONFIGURATION
	/// Liste des vues prédéfinies			
	private		$PredefinedView 	= array( 'AjaxListTable' );
	/// Liste des vues actives
	private		$ActiveViewNames	= array();
	/// Classe de rappel des vues actives
	private		$ActiveViewClass	= array();
	/// Attributs des vues actives 
	private		$ViewAttrs			= null;	
	/// Vue principale de l'écran courant
	private 	$ScreenView			= null;
	/// Vue courante
	private 	$CurrentView		= null;

	/* = CONSTRUCTEUR = */
	public function __construct( $id, $args = array() )
	{		
		parent::__construct();
		
		// Définition de l'identifiant de l'entité associée
		$this->ID = $id;		
		
		// Traitement des vues actives	
		foreach( (array) $args as $name => $attrs ) :
			array_push( $this->ActiveViewNames, $name );
			
			$this->ViewAttrs[$name] = new \stdClass;
			
			// Définition de la classe de rappel
			if( isset( $attrs['cb'] ) ) :
				$this->ViewAttrs[$name]->cb 	= $attrs['cb'];
			elseif( in_array( $name, $this->PredefinedView ) ) :
				$this->ViewAttrs[$name]->cb 	= "\\tiFy\\Core\\View\\Front\\{$name}\\{$name}";	
			else :
				$this->ViewAttrs[$name]->cb 	= null;
			endif;
		endforeach;		
	}
	
	/* = ACTIONS = */
	/** == Initialisation globale == **/
	final public function init()
	{
		// Instanciation des contrôleurs
		foreach( $this->ActiveViewNames as $view ) :
			// Bypass
			if( ! isset( $this->ViewAttrs[$view]->cb ) || ! class_exists( $this->ViewAttrs[$view]->cb ) )
				continue;
			
			// Instanciation de la classe
			$this->ActiveViewClass[$view] 		= new $this->ViewAttrs[$view]->cb( \tiFy\Core\View\View::GetClass( $this->ID ) );
			//$this->ViewAttrs[$view]->base_url 	= \esc_attr( $this->ViewAttrs[$view]->menu_page_url );
								
			// Déclenchement de l'action dans les classes de rappel d'environnement
			if( method_exists( $this->ActiveViewClass[$view], '_init' ) ) :
				call_user_func( array( $this->ActiveViewClass[$view], '_init' ) );
			endif;
			if( method_exists( $this->ActiveViewClass[$view], 'init' ) ) :
				call_user_func( array( $this->ActiveViewClass[$view], 'init' ) );
			endif;
		endforeach;
	}
			
	/** == Mise en file des scripts de l'interface d'administration (privée) == **/
	final public function enqueue_scripts()
	{			
		switch( $this->CurrentView ) :
			case 'AjaxListTable' :	
				wp_enqueue_style( 'tiFy_View_Front_AjaxListTable', $this->Url .'/Front/AjaxListTable/AjaxListTable.css', array( 'datatables' ), '160506' );						
				wp_enqueue_script( 'tiFy_View_Front_AjaxListTable', $this->Url .'/Front/AjaxListTable/AjaxListTable.js', array( 'datatables' ), '160506', true );
				break;
		endswitch;		
			
		// Déclenchement de l'action dans la classe de rappel d'environnement	
		if( isset( $this->ActiveViewClass[$this->CurrentView] ) && method_exists( $this->ActiveViewClass[$this->CurrentView], '_enqueue_scripts' ) ) :
			call_user_func( array( $this->ActiveViewClass[$this->CurrentView], '_enqueue_scripts' ) );
		endif;
		if( isset( $this->ActiveViewClass[$this->CurrentView] ) && method_exists( $this->ActiveViewClass[$this->CurrentView], 'enqueue_scripts' ) ) :
			call_user_func( array( $this->ActiveViewClass[$this->CurrentView], 'enqueue_scripts' ) );
		endif;
	}
	
	/* = CONTRÔLEURS = */
	/** == Récupération d'une vue == **/
	public function getView( $view )
	{
		foreach( (array) $this->ActiveViewNames as $ActiveViewName ) :
			if( $view !== $ActiveViewName )
				continue;
			return $this->ActiveViewClass[$view];
		endforeach;
	}
	
	/** == Définition de la vue courante == **/
	public function setCurrentView( $view )
	{
		foreach( (array) $this->ActiveViewNames as $ActiveViewName ) :
			if( $view !== $ActiveViewName )
				continue;
			$this->CurrentView = $view; break;
		endforeach;
		
		return $this->CurrentView;
	}
	
	/** == Rédéfinition de la vue courante par défaut == **/
	public function resetCurrentView()
	{
		return $this->setCurrentView( $this->ScreenView );
	}
	
	/** == Récupération de la vue courante == **/
	public function getCurrentView()
	{
		return $this->CurrentView;
	}
	
	/** == Vérification d'existance d'une vue == **/
	public function has( $view = null )
	{
		if( ! $view )
			$view = $this->CurrentView;
		
		return in_array( $view, $this->ActiveViewNames );
	}
	
	/** == Récupération d'attributs == **/
	public function getAttrs( $attr = null, $view = null )
	{
		if( ! $view )
			$view = $this->CurrentView;
		
		if( ! $this->has( $view ) )
			return;
		
		if( ! $attr ) :	
			return $this->ViewAttrs[$view];
		elseif( isset( $this->ViewAttrs[$view]->{$attr} ) ) :
			return $this->ViewAttrs[$view]->{$attr};
		endif;
	}
	
	/** == Définition d'attribut == **/
	public function setAttr( $attr, $value = '', $view = null )
	{
		if( ! $view )
			$view = $this->CurrentView;
		
		if( ! $this->has( $view ) )
			return;
		
		if( ! isset( $this->ViewAttrs[$view]->{$attr} ) )
			return;
		
		return $this->ViewAttrs[$view]->{$attr} = $value;
	}
	
	/** == == **/
	public function getActive()
	{
		return $this->ActiveViewNames;
	}
				
	/* = AFFICHAGE = */
	/** == Page de l'interface utilisateur == **/
	final public function Render( $view = null )
	{
		if( ! $view )
			$view = $this->CurrentView;

		if( isset( $this->ActiveViewClass[$view] ) && method_exists( $this->ActiveViewClass[$view], 'Render' ) )
			return call_user_func( array( $this->ActiveViewClass[$view], 'Render' ) ); 		
	}
}