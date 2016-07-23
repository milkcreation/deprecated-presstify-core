<?php
namespace tiFy\Core\Taboox;

use tiFy\Environment\Core;

class Taboox extends Core
{
	/* = ARGUMENTS = */
	// Boîtes à onglets déclarées
	public static	$Boxes				= array();
	
	// Sections de boîte à onglets déclarées		
	public static	$Nodes				= array();
	
	// Interface d'administration déclarées
	public static 	$AdminForm			= array();
	
	// Classes de rappel de l'interfaces d'administration
	protected 		$AdminFormClass		= array();
	
	// Classes de rappel de l'interface visiteur
	public static	$HelpersClass		= array();
	
	// Liste des pages d'accroche		
	public static	$Hooknames			= array();
		
	// ID de l'écran courant d'affichage de l'interface d'administration
	protected $CurrentScreenID;
	
	// Classe de rappel l'écran courant
	public $Screen;	
		
	// Actions à déclencher
	protected $CallActions				= array(
		'after_setup_tify',
		'wp_loaded', 
		'admin_init',
		'current_screen',
		'admin_enqueue_scripts',
		'add_meta_boxes',
		'wp_ajax_tify_taboox_current_tab'  
	);
	
	// Ordre de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array( 
		'admin_init' 		=> 1,
		'after_setup_tify'	=> 11
	);
				
	/* = DECLENCHEMENT DES ACTIONS = */
	/** == Après le chargement du thème == **/	
	protected function after_setup_tify()
	{
		// Traitement des paramètres
		/// Déclaration des boîtes à onglets 
		foreach( (array) self::getConfig() as $env => $hooknames ) :
			if( ! in_array( $env, array( 'post', 'taxonomy', 'user', 'option' ) ) )
				continue;
			
			foreach( (array) $hooknames as $hookname => $args ) :
				if( $env === 'taxonomy' )
					$hookname = 'edit-'. $hookname;
				if( ! empty( $args['box'] ) ):				
					self::registerBox( $hookname, $env, $args['box'] );
				endif;
				if( ! empty( $args['nodes'] ) ):	
					foreach( (array) $args['nodes'] as $node_id => $attrs ) :
						$attrs['id'] = $node_id;
						self::registerNode( $hookname, $attrs, $env );
					endforeach;
				endif;
			endforeach;			
		endforeach;
	}
	
	/** == Au chargement complet == **/
	protected function wp_loaded()
	{
		// Déclaration des boîtes à onglets
		do_action( 'tify_taboox_register_box' );
		
		// Création automatique des boîtes à onglets pour les pages d'édition de post contenant des sections
		foreach( get_post_types() as $post_type ) 
			if( isset( self::$Nodes[$post_type] ) && ! isset( self::$Boxes[$post_type] ) )
				self::registerBox( $post_type );		
		
		// Déclaration des sections de boîtes à onglets
		do_action( 'tify_taboox_register_node' );
		
		// Déclaration des formulaires de saisie
		do_action( 'tify_taboox_register_form' );
		
		// Déclaration des helpers
		do_action( 'tify_taboox_register_helpers' );
		
		// Initialisation de Helpers
		foreach( (array) self::$HelpersClass as $helpers )
			$this->initHelpersClass( $helpers );
	}
	
	/** == Initialisation de l'interface d'administration == **/	
	protected function admin_init()
	{		
		// Initialisation des sections de boîtes à onglets			
		foreach( self::$Hooknames as $hookname ) :
			if( isset( self::$Nodes[$hookname] ) ) :
				foreach( self::$Nodes[$hookname] as $node )	:	
					$this->initAdminFormClass( $node, $hookname );
				endforeach;
			endif;
		endforeach;

		// Déclenchement de l'action "Initialisation de l'interface d'administration" dans l'ensemble classes de rappel de formulaire
		foreach( (array) $this->AdminFormClass as $Screen => $Classes ) :
			foreach( (array) $Classes as $ID => $Class ) :
				if( is_callable( array( $Class, 'admin_init' ) ) ) :
					call_user_func( array( $Class, 'admin_init' ) );
				endif;
			endforeach;
		endforeach;
	}
	
	/** == Chargement de l'écran courant == **/
	protected function current_screen( $current_screen )
	{
		// Bypass
		if( ! in_array( $current_screen->id, array_keys( self::$Hooknames ) ) )
			return;
			
		if( ! isset( self::$Boxes[$current_screen->id] ) || ! isset( self::$Nodes[$current_screen->id] ) )
			return;

		// Initialisation de la classe de l'écran courant 			
		$this->Screen 			= new Screen( $current_screen );
		$this->Screen->Box 		= self::$Boxes[$this->Screen->ID];
		$this->Screen->Nodes 	= self::$Nodes[$this->Screen->ID];
		if( ! empty( $this->AdminFormClass[$this->Screen->ID] ) )
			$this->Screen->Forms 	= $this->AdminFormClass[$this->Screen->ID];
		
		// Création de la section de boites de saisie
		// Options
		if( self::$Boxes[$this->Screen->ID]['env'] === 'option' ) :
			add_settings_section( $this->Screen->ID, null, array( $this->Screen, 'box_render' ), self::$Boxes[$this->Screen->ID]['page'] );
		endif;
		
		// Taxonomy
		if( self::$Boxes[$this->Screen->ID]['env'] === 'taxonomy' ) :
			add_action( $current_screen->taxonomy .'_edit_form', array( $this->Screen, 'box_render' ), 10, 2 );
		endif;
		
		
		// Déclenchement de l'action "Chargement de l'écran courant" dans les classes de rappel de formulaire
		if( ! empty( $this->AdminFormClass[$this->Screen->ID] ) ) :
			foreach( (array) $this->AdminFormClass[$this->Screen->ID] as $ID => $Class ) :
				if( is_callable( array( $Class, 'current_screen' ) ) ) :
					call_user_func( array( $Class, 'current_screen' ), $current_screen );
				endif;
			endforeach;	
		endif;
	}
	
	/** == Mise en file des scripts de l'interface d'administration == **/
	public function admin_enqueue_scripts()
	{			
		// Bypass
		if( empty( $this->Screen ) )
			return;
		
		// Chargement des scripts
		wp_enqueue_style( 'tify_taboox_admin', $this->Url .'/assets/Admin.css', array(), '150216' );
		wp_enqueue_script( 'tify_taboox_admin', $this->Url .'/assets/Admin.js', array( 'bootstrap-togglable-tabs' ), '151019', true );
		
		// Déclenchement de l'action "Mise en file des scripts de l'interface d'administration" dans les classes de rappel de formulaire
		if( ! empty( $this->AdminFormClass[$this->Screen->ID] ) ) :
			foreach( (array) $this->AdminFormClass[$this->Screen->ID] as $ID => $Class ) :
				if( is_callable( array( $Class, 'admin_enqueue_scripts' ) ) ) :
					call_user_func( array( $Class, 'admin_enqueue_scripts' ) );
				endif;
			endforeach;
		endif;
	}
	
	/** == == **/
	public function add_meta_boxes( $post_type )
	{
		// Bypass
		if( is_null( $this->Screen ) )
			return;
		if( $this->Screen->ID !==  $post_type )
			return;				
		if( ! isset( self::$Nodes[$post_type] ) )
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
	public static function registerBox( $hookname = null, $env = 'post', $args = array() )
	{
		// Bypass	
		if( ! $hookname )
			return;

		if( is_string( $hookname ) )
			$hookname = array( $hookname );
		
		foreach( $hookname as $_hookname ) :
			if( ! in_array( $_hookname, self::$Hooknames ) )
				array_push( self::$Hooknames, $_hookname );
			
			self::$Boxes[$_hookname] = 	wp_parse_args( 
											$args, 
											array( 
												'title' 	=> '', 
												'page' 		=> '' 
											) 
										);
										
			self::$Boxes[$_hookname]['env']	= $env;			
		endforeach;
	}
	
	/** == NOEUDS - SECTION DE BOÎTE A ONGLETS == **/
	/*** === Déclaration === ***/
	public static function registerNode( $hookname, $args = array() )
	{
		$defaults = array(
			'id' 			=> false,
			'title' 		=> '',
			'cb' 			=> \__return_null(),
			'parent'		=> 0,
			'args' 			=> array(),
			'cap'			=> 'manage_options',
			'show'			=> true,
			'order'			=> 99,
			'helpers'		=> \__return_null()
		);
		$args = wp_parse_args( $args, $defaults );
		
		if( is_string( $hookname ) )
			$hookname = array( $hookname );
							
		foreach( (array) $hookname as $_hookname ) :
			if( ! isset( self::$Boxes[$_hookname] ) ) :
				self::registerBox( $_hookname );
			endif;
					
			self::$Nodes[$_hookname][$args['id']] = $args;
		endforeach;
		
		if( $args['helpers'] )
			self::registerHelpersClass( $args['helpers'] );
			
		return $args['id'];
	}
		
	/** == FORMULAIRE - ZONE DE SAISIE D'UNE SECTION DE BOÎTE A ONGLETS == **/	
	/*** === Déclaration === ***/
	public static function registerAdminForm( $class, $args = array() ) 
	{		
		self::$AdminForm[$class] = $args;
	}
	
	/*** === Initialisation === ***/
	private function initAdminFormClass( $node, $hookname )
	{
		// Bypass
		if( ! $node['cb'] || ! class_exists( $node['cb'] ) ) 
			return;
		
		$AdminFormClassArgs 			= isset( self::$AdminForm[$node['cb']] ) ? self::$AdminForm[$node['cb']] : null;		
		$AdminFormClass 				= new $node['cb']( $AdminFormClassArgs );
		$AdminFormClass->ScreenID		= $hookname;
		$AdminFormClass->page 			= self::$Boxes[$hookname]['page'];
		$AdminFormClass->env			= self::$Boxes[$hookname]['env'];
		$AdminFormClass->args 			= $node['args'];
		
		return $this->AdminFormClass[$hookname][$node['id']] = $AdminFormClass;
	}
	
	/** == HELPERS == **/
	/*** === Déclaration === ***/
	public static function registerHelpersClass( $helpers )
	{
		if( ! in_array( $helpers, self::$HelpersClass ) )
			array_push( self::$HelpersClass, $helpers );
	}
	
	/*** === Initialisation === ***/
	private function initHelpersClass( $helpers )
	{	
		if( is_string( $helpers ) )
			$helpers = array_map( 'trim', explode( ',', $helpers ) );
		
		foreach( $helpers as $helper )
			new $helper;
	}
}