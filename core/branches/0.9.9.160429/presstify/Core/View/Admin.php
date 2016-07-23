<?php
namespace tiFy\Core\View;

use tiFy\Environment\App;

final class Admin extends App
{
	/* = ARGUMENTS = */
	// ACTIONS
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'admin_init',
		'current_screen'	
	); 
	// CONFIGURATION
	/// Liste des vues prédéfinies			
	private		$PredefinedView 	= array( 'ListTable', 'EditForm', 'Import' );
	/// Liste des vues actives
	private		$ActiveViewNames	= array();
	/// Classe de rappel des vues actives
	private		$ActiveViewClass	= array();	
	/// Attribut du menu d'administration
	private		$Menu				= null;
	/// Attributs des vues actives 
	private		$ViewAttrs			= null;	
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
				$this->ViewAttrs[$name]->cb 	= "\\tiFy\\Core\\View\\Admin\\{$name}\\{$name}";	
			else :
				$this->ViewAttrs[$name]->cb 	= null;
			endif;
			
			// Traitement des arguments de menu
			$defaults = array( 'page_title' => $this->ID, 'menu_title' => '', 'capability'	=> 'manage_options', 'icon_url' => null, 'position' => 99, 'function' => array( $this, 'Render' ) );			
			$args = wp_parse_args( $attrs, $defaults );
			
			foreach( $args as $k => $v )
				$this->ViewAttrs[$name]->{$k} = $v;				
				
			$this->ViewAttrs[$name]->menu_slug 		= 	! empty( $attrs['menu_slug'] ) 		? $attrs['menu_slug'] 		: $this->ID .'_'. $name;
			$this->ViewAttrs[$name]->parent_slug 	= 	! empty( $attrs['parent_slug'] ) 	? $attrs['parent_slug'] 	: null;
		endforeach;		
	}
	
	/* = ACTIONS = */	
	/** == Initialisation de l'interface d'administration (privée) == **/
	final public function admin_init()
	{
		// Instanciation des contrôleurs
		foreach( $this->ActiveViewNames as $view ) :
			// Bypass
			if( ! isset( $this->ViewAttrs[$view]->cb ) || ! class_exists( $this->ViewAttrs[$view]->cb ) )
				continue;
			
			// Instanciation de la classe
			$this->ActiveViewClass[$view] = new $this->ViewAttrs[$view]->cb( \tiFy\Core\View\View::GetClass( $this->ID ) );
				
			// Définition des attributs de la vue	
			$this->ViewAttrs[$view]->hookname 		= \get_plugin_page_hookname( $this->ViewAttrs[$view]->menu_slug, $this->ViewAttrs[$view]->parent_slug );
			$this->ViewAttrs[$view]->menu_page_url 	= \menu_page_url( $this->ViewAttrs[$view]->menu_slug, false );
			$this->ViewAttrs[$view]->base_url 		= \esc_attr( $this->ViewAttrs[$view]->menu_page_url );
				
			// Déclenchement de l'action dans les classes de rappel d'environnement
			if( method_exists( $this->ActiveViewClass[$view], '_admin_init' ) ) :
				call_user_func( array( $this->ActiveViewClass[$view], '_admin_init' ) );
			endif;
		endforeach;
	}
	
	/** == Chargement de l'écran courant (privée) == **/
	final public function current_screen( $current_screen )
	{
		// Définition de la vue courante		
		if( $this->_currentView() && ( $current_screen->id !== $this->ViewAttrs[$this->CurrentView]->hookname ) )
			return;
		
		// Mise en file des scripts de l'ecran courant
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Déclenchement de l'action dans la classe de rappel d'environnement			
		if( isset( $this->ActiveViewClass[$this->CurrentView] ) && method_exists( $this->ActiveViewClass[$this->CurrentView], '_current_screen' ) ) :
			call_user_func( array( $this->ActiveViewClass[$this->CurrentView], '_current_screen' ), $current_screen );
		endif;
	}
	
	/** == Mise en file des scripts de l'interface d'administration (privée) == **/
	final public function admin_enqueue_scripts()
	{			
		// Déclenchement de l'action dans la classe de rappel d'environnement	
		if( isset( $this->ActiveViewClass[$this->CurrentView] ) && method_exists( $this->ActiveViewClass[$this->CurrentView], '_admin_enqueue_scripts' ) ) :
			call_user_func( array( $this->ActiveViewClass[$this->CurrentView], '_admin_enqueue_scripts' ) );
		endif;
	}
	
	/* = CONTRÔLEURS = */
	/** == == **/
	public function has( $view = null )
	{
		if( ! $view )
			$view = $this->CurrentView;
		
		return in_array( $view, $this->ActiveViewNames );
	}
	
	/** == == **/
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
	
	/** == == **/
	public function getActive()
	{
		return $this->ActiveViewNames;
	}
			
	/* = PARAMETRAGE = */		
	/** == Récupération de la vue courante == **/
	private function _currentView()
	{		
		if( $this->CurrentView ) :
			return $this->CurrentView;
		endif;
		
		if( $current_screen = get_current_screen() ) :
			foreach( (array) array_keys( $this->ActiveViewClass ) as $view ) :
				if( $this->ViewAttrs[$view]->hookname === $current_screen->id ) :
					$this->CurrentView = $view;
				endif;
			endforeach;
		elseif( isset( $_REQUEST['tyadmvw'] ) ) :
			$this->CurrentView = $_REQUEST['tyadmvw'];
		endif;

		if( $this->CurrentView ) :
			return $this->CurrentView;
		endif;
	}
	
	/* = AFFICHAGE = */
	/** == Point d'entrée == **/
	final public function Render()
	{
		if( isset( $this->ActiveViewClass[$this->CurrentView] ) && method_exists( $this->ActiveViewClass[$this->CurrentView], 'Render' ) )
			return call_user_func( array( $this->ActiveViewClass[$this->CurrentView], 'Render' ) ); 		
	}
}