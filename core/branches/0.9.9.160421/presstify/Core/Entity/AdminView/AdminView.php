<?php
namespace tiFy\Core\Entity\AdminView;

final class AdminView
{
	/* = ARGUMENTS = */
	// CONFIGURATION DE L'ENTITE ASSOCIÉE
	protected 	$EntityID;
	protected 	$Entity;
			
	// CONFIGURATION DES VUES
	/// Liste des vues autorisées			
	private		$AllowedView 		= array( 'ListTable', 'EditForm', 'Import' );
	/// Vues actives
	private		$ActiveView			= array();	
	/// Attribut du menu d'administration
	private		$Menu				= null;
	/// Attributs des vues actives 
	private		$View				= null;	
	/// Vue courante
	private 	$CurrentView		= null;
	
	// CLASSES D'APPEL DES VUES
	protected	$ListTable;
	protected	$EditForm;
	protected	$Import;

	/* = CONSTRUCTEUR = */
	public function __construct( $id, $args = array() )
	{		
		// Définition de l'identifiant de l'entité associée
		$this->EntityID = $id;		
				
		// Traitement des argument de menu
		if( ! empty( $args['Menu'] ) ) :
			$this->Menu					= array();
			$this->Menu['menu_slug']	= isset( $args['Menu']['menu_slug'] ) ? $args['Menu']['menu_slug'] : $this->EntityID;
			$this->Menu['icon_url']		= isset( $args['Menu']['icon_url'] ) ? $args['Menu']['icon_url'] : null;
			$this->Menu['position']		= isset( $args['Menu']['position'] ) ? $args['Menu']['position'] : null;	
		endif;
		
		// Traitement des vues actives	
		foreach( (array) $this->AllowedView as $view ) :
			if( ! isset( $args[$view] ) )
				continue;
			
			array_push( $this->ActiveView, $view );
			$this->View[$view] = new \stdClass;
			
			if( isset( $args[$view]['parent_slug'] ) ) :
				$this->View[$view]->parent_slug = $args[$view]['parent_slug'];
			endif;
			
			if( isset( $args[$view]['menu_slug'] ) ) :
				$this->View[$view]->menu_slug = $args[$view]['menu_slug'];
			endif;
			
			$this->View[$view]->cb = ( isset( $args[$view]['cb'] ) ) ? $args[$view]['cb'] : "\\tiFy\\Core\\Entity\\AdminView\\{$view}";	
		endforeach;

		// Actions et Filtres Wordpress		
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'current_screen', array( $this, 'current_screen' ) );
	}
	
	/* = CONTRÔLEURS = */
	/** == == **/
	public function has( $view = null )
	{
		if( ! $view )
			$view = $this->CurrentView;
		
		return in_array( $view, $this->AllowedView ) && in_array( $view, $this->ActiveView );
	}
	
	/** == == **/
	public function get( $attr = null, $view = null )
	{
		if( ! $view )
			$view = $this->CurrentView;
		
		if( ! $this->has( $view ) )
			return;
		
		if( ! $attr ) :	
			return $this->View[$view];
		elseif( isset( $this->View[$view]->{$attr} ) ) :
			return $this->View[$view]->{$attr};
		endif;
	}
		
	/* = ACTIONS ET FILTRES WORDPRESS (privée) = */
	final public function admin_menu()
	{
		if( ! $this->Menu )
			return;		
		
		$this->Entity 	= \tiFy\Core\Entity\Entity::get( $this->EntityID );
		
		
		add_menu_page( $this->Entity->getLabel( 'name' ), $this->Entity->getLabel( 'menu_name' ), 'manage_options', $this->Menu['menu_slug'], null, $this->Menu['icon_url'], $this->Menu['position'] );
		
		foreach( $this->ActiveView as $view ) :
			switch( $view ) :
				default :
				break;
				case 'ListTable' :
						$page_title = $this->Entity->getLabel( 'all_items' );
						$menu_title = $this->Entity->getLabel( 'all_items' );
					break;
				case 'EditForm' :
						$page_title = $this->Entity->getLabel( 'add_item' );
						$menu_title = $this->Entity->getLabel( 'add_item' );
					break;
			endswitch;
			
			add_submenu_page( $this->View[$view]->parent_slug, $page_title, $menu_title, 'manage_options', $this->View[$view]->menu_slug, array( $this, 'Render' ) );
		endforeach;
	}	
	
	/** == Initialisation de l'interface d'administration (privée) == **/
	final public function admin_init()
	{
		// Instanciation des contrôleurs
		foreach( (array) $this->AllowedView as $view ) :
			// Bypass
			if( ! isset( $this->View[$view]->cb ) || ! class_exists( $this->View[$view]->cb ) )
				continue;
			
			// Instanciation de la classe
			$this->{$view} = new $this->View[$view]->cb;
			
			// Définition des attributs de la vue	
			$this->View[$view]->hookname 		= \get_plugin_page_hookname( $this->View[$view]->menu_slug, $this->View[$view]->parent_slug );
			$this->View[$view]->screen			= \convert_to_screen( $this->View[$view]->hookname );
			$this->View[$view]->menu_page_url 	= \menu_page_url( $this->View[$view]->menu_slug, false );
			$this->View[$view]->base_url 		= \esc_attr( $this->View[$view]->menu_page_url );
			
			// Passage des arguments dans la vue
			$this->{$view}->Entity				= $this->Entity;
			
			// Déclenchement de l'action dans les classes de rappel d'environnement
			if( method_exists( $this->{$view}, '_admin_init' ) ) :
				call_user_func( array( $this->{$view}, '_admin_init' ) );
			endif;
		endforeach;
	}
	
	/** == Chargement de l'écran courant (privée) == **/
	final public function current_screen( $current_screen )
	{
		// Définition de la vue courante
		$this->_currentView();

		if( ! isset( $this->View[$this->CurrentView]->screen ) || ( $current_screen->id !== $this->View[$this->CurrentView]->screen->id ) )
			return;
		
		// Mise en file des scripts de l'ecran courant
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Déclenchement de l'action dans la classe de rappel d'environnement			
		if( method_exists( $this->{$this->CurrentView}, '_current_screen' ) ) :
			call_user_func( array( $this->{$this->CurrentView}, '_current_screen' ), $current_screen );
		endif;
	}
	
	/** == Mise en file des scripts de l'interface d'administration (privée) == **/
	final public function admin_enqueue_scripts()
	{			
		// Déclenchement de l'action dans la classe de rappel d'environnement	
		if( method_exists( $this->{$this->CurrentView}, '_admin_enqueue_scripts' ) ) :
			call_user_func( array( $this->{$this->CurrentView}, '_admin_enqueue_scripts' ) );
		endif;
	}
	
	/* = PARAMETRAGE = */		
	/** == Récupération de la vue courante == **/
	private function _currentView()
	{		
		if( $this->CurrentView ) :
			return $this->CurrentView;
		endif;
		
		if( $current_screen = get_current_screen() ) :
			foreach( (array) $this->ActiveView as $view ) :
				if( $this->View[$view]->hookname === $current_screen->id ) :
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
		return $this->{$this->CurrentView}->Render(); 		
	}
}