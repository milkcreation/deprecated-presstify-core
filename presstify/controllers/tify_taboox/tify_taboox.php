<?php
global $tify_taboox;
$tify_taboox = new tiFy_Taboox_Master;

class tiFy_Taboox_Master{
	/* = ARGUMENTS = */
	public	// Chemins					
			$dir,
			$uri,
						
			// Contrôleurs
			$factory,
						
			// Paramètres
			$screens 	= array(),
			$boxes		= array(),
			$nodes		= array(),
			$forms		= array(); 
	
	/* = CONSTRUCTEUR = */
	function __construct( ){
		// Définition des chemins
		$this->dir = dirname( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );
		
		require_once $this->dir .'/inc/factory.php';
		require_once $this->dir .'/inc/form.php';
		require_once $this->dir .'/inc/helpers.php';
		
		// Option
		$this->factory = new tiFy_Taboox_Factory( $this );
		
		// Interfaces de saisie prédéfinies
		require_once $this->dir .'/taboox/color_palette/color_palette.php';
		require_once $this->dir .'/taboox/custom_background/custom_background.php';
		require_once $this->dir .'/taboox/custom_fields/custom_fields.php';
		require_once $this->dir .'/taboox/custom_header/custom_header.php';
		require_once $this->dir .'/taboox/date_range/date_range.php';
		require_once $this->dir .'/taboox/date_single/date_single.php';
		require_once $this->dir .'/taboox/dynamic_tab/dynamic_tab.php';
		require_once $this->dir .'/taboox/fileshare/fileshare.php';
		require_once $this->dir .'/taboox/google_map/google_map.php';	
		require_once $this->dir .'/taboox/image_gallery/image_gallery.php';
		require_once $this->dir .'/taboox/links/links.php';
		require_once $this->dir .'/taboox/password_protect/password_protect.php';
		require_once $this->dir .'/taboox/related_posts/related_posts.php';
		require_once $this->dir .'/taboox/taxonomy_select/taxonomy_select.php';
		require_once $this->dir .'/taboox/threesixty_view/threesixty_view.php';
		require_once $this->dir .'/taboox/video_gallery/video_gallery.php';
			
		// Actions et Filtres Wordpress
		add_action( 'admin_init', array( $this, 'wp_admin_init' ), 9 );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation de l'interface d'administration == **/	
	public function wp_admin_init(){
		do_action( 'tify_taboox_register_box' );
		do_action( 'tify_taboox_register_node' );
		do_action( 'tify_taboox_register_form' );
		
		foreach( array_keys( $this->screens ) as $screen )
			if( isset( $this->nodes[$screen] ) )
				foreach( $this->nodes[$screen] as $node )			
					$this->init_call_form( $node, $screen );
					
		// Déclaration des scripts 	
		wp_register_style( 'tify_taboox', $this->uri .'/css/tify_taboox.css', array(), '150216' );
		wp_register_script( 'tify_taboox', $this->uri .'/js/tify_taboox.js', array( 'bootstrap-togglable-tabs' ), '151019', true );	
	}
		
	/* = CONTROLEURS = */
	/** == BOÎTES A ONGLET - CONTENEUR (box) == **/
	/*** === Déclaration === ***/
	function register_box( $hookname = null, $env = 'post', $args = array() ){
		if( ! $hookname )
			return;
		
		if( is_string( $hookname ) )
			$hookname = array( $hookname );
		
		foreach( $hookname as $_hookname ) :
			if( ! in_array( $_hookname, array_keys( $this->screens ) ) )
				$this->screens[$_hookname] = convert_to_screen( $_hookname );

			$this->boxes[$_hookname] = 	wp_parse_args( 
											$args, 
											array( 
												'title' 	=> '', 
												'page' 		=> '' 
											) 
										);
										
			$this->boxes[$_hookname]['env']	= $env;			
		endforeach;
	}
	
	/** == NOEUDS - SECTION DE BOÎTE A ONGLET (nodes) == **/
	/*** === Déclaration === ***/
	function register_node( $hookname, $args = array()  ){
		$defaults = array(
			'id' 			=> false,
			'title' 		=> '',
			'cb' 			=> __return_null(),
			'parent'		=> 0,
			'args' 			=> array(),
			'capability'	=> 'manage_options',
			'order'			=> 99
		);
		$args = wp_parse_args( $args, $defaults );
		
		if( is_string( $hookname ) )
			$hookname = array( $hookname );
		
		foreach( (array) $hookname as $_hookname )
			$this->nodes[$_hookname][$args['id']] = $args;
		
		return $args['id'];
	}
		
	/** == FORMULAIRE - ZONE DE SAISIE (forms) == **/	
	/*** === Déclaration === ***/
	public function register_form( $class, $args = array() ) {		
		$this->forms[$class] = $args;
	}
	
	/*** === Initialisation === ***/
	function init_call_form( $node, $screen ){
		// Bypass
		if( ! $node['cb'] || ! isset( $this->forms[$node['cb']] ) || ! class_exists( $node['cb'] ) ) 
			return;
		
		$this->_{$node['cb']} 			= new $node['cb']( $this->forms[$node['cb']] );
		$this->_{$node['cb']}->screen 	= isset( $this->screens[ $screen ] ) ? $this->screens[ $screen ] : convert_to_screen( $screen );		
		$this->_{$node['cb']}->page 	= $this->boxes[$screen]['page'];
		$this->_{$node['cb']}->env		= $this->boxes[$screen]['env'];
		$this->_{$node['cb']}->args 	= $node['args'];

		return $this->_{$node['cb']};
	}
}