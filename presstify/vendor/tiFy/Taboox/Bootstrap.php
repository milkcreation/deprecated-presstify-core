<?php
namespace tiFy\Taboox;

use tiFy\Environment\App;

class Bootstrap extends App
{
	/* = ARGUMENTS = */
	// Boîtes à onglets déclarées
	protected $Boxes				= array();
	
	// Sections de boîte à onglets déclarées		
	protected $Nodes				= array();
	
	// Interface d'administration déclarées
	protected $AdminForm			= array();
	
	// Classes de rappel de l'interfaces d'administration
	protected $AdminFormClass		= array();
	
	// Classes de rappel de l'interface visiteur
	protected $Helpers				= array();
	
	// Liste des pages d'accroche		
	protected $Hooknames			= array();
		
	// ID de l'écran courant d'affichage de l'interface d'administration
	protected $CurrentScreenID;
	
	// Classe de rappel l'écran courant
	protected $Screen;	
		
	// Actions à déclencher
	protected $CallActions			= array(
		'after_setup_theme',
		'wp_loaded', 
		'admin_init',
		'current_screen',
		'admin_enqueue_scripts',
		'add_meta_boxes',
		'wp_ajax_tify_taboox_current_tab'  
	);
	
	// Ordre de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array( 'admin_init' => 1 );
				
	/* = DECLENCHEMENT DES ACTIONS = */
	/** == Après le chargement du thème == **/	
	protected function after_setup_theme()
	{
		// Traitement des paramètres
		/// Déclaration des boîtes à onglets 
		if( isset( $this->Params['taboox']['boxes'] ) )
			foreach( $this->Params['taboox']['boxes'] as $boxes )
				$this->registerBox( $boxes['hookname'], $boxes['env'] /** @todo $args **/ );
		
		/// Déclaration des sections de boîtes à onglets	
		if( isset( $this->Params['taboox']['nodes'] ) ) :
			foreach( $this->Params['taboox']['nodes'] as $node_id => $attrs ) :
				$hookname = $attrs['hookname']; unset( $attrs['hookname'] );
				$args = array();
				$args['id'] = $node_id;
				$args +=  $attrs;
				$this->registerNode( $hookname, $args );
			endforeach;
		endif;	
	}
	
	/** == Au chargement complet == **/
	protected function wp_loaded()
	{
		// Déclaration des boîtes à onglets
		do_action( 'tify_taboox_register_box' );
		
		// Création automatique des boîtes à onglets pour les pages d'édition de post contenant des sections
		foreach( get_post_types() as $post_type ) 
			if( isset( $this->Nodes[$post_type] ) && ! isset( $this->Boxes[$post_type] ) )
				$this->registerBox( $post_type );		
		
		// Déclaration des sections de boîtes à onglets
		do_action( 'tify_taboox_register_node' );
		
		// Déclaration des formulaires de saisie
		do_action( 'tify_taboox_register_form' );
		
		// Déclaration des helpers
		do_action( 'tify_taboox_register_helpers' );
		
		// Initialisation de Helpers
		foreach( (array) $this->Helpers as $helpers )
			$this->initHelpers( $helpers );
	}
	
	/** == Initialisation de l'interface d'administration == **/	
	protected function admin_init()
	{		
		// Initialisation des sections de boîtes à onglets			
		foreach( $this->Hooknames as $hookname )
			if( isset( $this->Nodes[$hookname] ) )
				foreach( $this->Nodes[$hookname] as $node )			
					$this->initAdminFormClass( $node, $hookname );
				
		// Déclenchement de l'action "Initialisation de l'interface d'administration" dans l'ensemble classes de rappel de formulaire
		foreach( (array) $this->AdminFormClass as $Screen => $Classes )
			foreach( (array) $Classes as $ID => $Class )
				if( is_callable( array( $Class, 'admin_init' ) ) )
					call_user_func( array( $Class, 'admin_init' ) );			
	}
	
	/** == Chargement de l'écran courant == **/
	protected function current_screen( $current_screen )
	{
		// Bypass
		if( ! in_array( $current_screen->id, array_keys( $this->Hooknames ) ) )
			return;
		
		if( ! isset( $this->Boxes[$current_screen->id] ) || ! isset( $this->Nodes[$current_screen->id] ) )
			return;
	
		// Initialisation de la classe de l'écran courant 			
		$this->Screen 			= new Screen( $current_screen );
		$this->Screen->Box 		= $this->Boxes[$this->Screen->ID];
		$this->Screen->Nodes 	= $this->Nodes[$this->Screen->ID];
		$this->Screen->Forms 	= $this->AdminFormClass[$this->Screen->ID];

		// Création de la section 
		if( $this->Boxes[$this->Screen->ID]['env'] === 'option' )
			add_settings_section( $this->Screen->ID, null, array( $this->Screen, 'box_render' ), $this->Boxes[$this->Screen->ID]['page'] );
		
		// Déclenchement de l'action "Chargement de l'écran courant" dans les classes de rappel de formulaire
		foreach( (array) $this->AdminFormClass[$this->Screen->ID] as $ID => $Class )
			if( is_callable( array( $Class, 'current_screen' ) ) )
				call_user_func( array( $Class, 'current_screen' ), $current_screen );
		
	}
	
	/** == Mise en file des scripts de l'interface d'administration == **/
	public function admin_enqueue_scripts()
	{			
		// Bypass
		if( empty( $this->Screen ) )
			return;
		
		// Chargement des scripts
		wp_enqueue_style( 'tify_taboox_admin', $this->Url .'/Admin/Admin.css', array(), '150216' );
		wp_enqueue_script( 'tify_taboox_admin', $this->Url .'/Admin/Admin.js', array( 'bootstrap-togglable-tabs' ), '151019', true );
		
		// Déclenchement de l'action "Mise en file des scripts de l'interface d'administration" dans les classes de rappel de formulaire
		foreach( (array) $this->AdminFormClass[$this->Screen->ID] as $ID => $Class )
			if( is_callable( array( $Class, 'admin_enqueue_scripts' ) ) )
				call_user_func( array( $Class, 'admin_enqueue_scripts' ) );
	}
	
	/** == == **/
	public function add_meta_boxes( $post_type )
	{
		// Bypass
		if( is_null( $this->Screen ) )
			return;
		if( $this->Screen->ID !==  $post_type )
			return;				
		if( ! isset( $this->Nodes[$post_type] ) )
			return;

		if( $post_type == 'page' ) 
			add_action( 'edit_page_form', array( $this->Screen, 'box_render' ) );
		else 
			add_action( 'edit_form_advanced', array( $this->Screen, 'box_render' ) );					
	}
	
	/** == Action Ajax de sauvegarde de l'onglet courant == **/
	public function wp_ajax_tify_taboox_current_tab()
	{
		// Bypass	
		if( empty( $_POST['current'] ) )
			wp_die(0);
		
		list( $screen_id, $node_id ) = explode( ':', $_POST['current'] );
		
		update_user_meta( get_current_user_id(), 'tify_taboox_'. $screen_id, ! empty( $node_id ) ? $node_id : 0 );
		
		wp_send_json_success( $node_id );
	} 
			
	/* = CONTROLEURS = */
	/** == BOÎTES A ONGLETS - CONTENEUR == **/
	/*** === Déclaration === ***/
	public function registerBox( $hookname = null, $env = 'post', $args = array() )
	{
		// Bypass	
		if( ! $hookname )
			return;

		if( is_string( $hookname ) )
			$hookname = array( $hookname );
		
		foreach( $hookname as $_hookname ) :
			if( ! in_array( $_hookname, $this->Hooknames ) )
				array_push( $this->Hooknames, $_hookname );
			
			$this->Boxes[$_hookname] = 	wp_parse_args( 
											$args, 
											array( 
												'title' 	=> '', 
												'page' 		=> '' 
											) 
										);
										
			$this->Boxes[$_hookname]['env']	= $env;			
		endforeach;
	}
	
	/** == NOEUDS - SECTION DE BOÎTE A ONGLETS == **/
	/*** === Déclaration === ***/
	public function registerNode( $hookname, $args = array() )
	{
		$defaults = array(
			'id' 			=> false,
			'title' 		=> '',
			'cb' 			=> \__return_null(),
			'parent'		=> 0,
			'args' 			=> array(),
			'cap'			=> 'manage_options',
			'order'			=> 99,
			'helpers'		=> \__return_null()
		);
		$args = wp_parse_args( $args, $defaults );
		
		if( is_string( $hookname ) )
			$hookname = array( $hookname );
		
		foreach( (array) $hookname as $_hookname )		
			$this->Nodes[$_hookname][$args['id']] = $args;
		
		if( $args['helpers'] )
			$this->registerHelpers( $args['helpers'] );
			
		return $args['id'];
	}
		
	/** == FORMULAIRE - ZONE DE SAISIE D'UNE SECTION DE BOÎTE A ONGLETS == **/	
	/*** === Déclaration === ***/
	public function registerAdminForm( $class, $args = array() ) 
	{		
		$this->AdminForm[$class] = $args;
	}
	
	/*** === Initialisation === ***/
	private function initAdminFormClass( $node, $hookname )
	{
		// Bypass
		if( ! $node['cb'] || ! class_exists( $node['cb'] ) ) 
			return;
		
		$AdminFormClassArgs 			= isset( $this->AdminForm[$node['cb']] ) ? $this->AdminForm[$node['cb']] : null;		
		$AdminFormClass 				= new $node['cb']( $AdminFormClassArgs );
		$AdminFormClass->ScreenID		= $hookname;
		$AdminFormClass->page 			= $this->Boxes[$hookname]['page'];
		$AdminFormClass->env			= $this->Boxes[$hookname]['env'];
		$AdminFormClass->args 			= $node['args'];
		
		return $this->AdminFormClass[$hookname][$node['id']] = $AdminFormClass;
	}
	
	/** == HELPERS == **/
	/*** === Déclaration === ***/
	public function registerHelpers( $helpers )
	{
		if( ! in_array( $helpers, $this->Helpers ) )
			array_push( $this->Helpers, $helpers );
	}
	
	/*** === Initialisation === ***/
	private function initHelpers( $helpers )
	{	
		new $helpers;
	}
}