<?php
class tiFy_Contest_Admin{
	/* = ARGUMENTS = */
	public 	// Configuration
			$dir,
			$uri,
			$menu_slug = array(),
			
			// Paramètres
			$hookname = array(),
			
			// Contrôleurs
			$dashboard,
			$participant,
			$participation,
			$poll;
			
	private	// Référence
			$master;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Contest_Master $master ){
		// Bypass
		if( ! is_admin() )
			return;
		
		// Définition de la classe de référence
		$this->master = $master;
		
		// Définition des chemins
		$this->dir = $this->master->dir .'/admin';
		$this->uri = $this->master->uri .'admin';
		
		// Configuration
		$this->menu_slug = array(
			'parent'		=> 'tify_contest',
			'participant'	=> 'tify_contest_participant',
			'participation'	=> 'tify_contest_participation',
			'poll'			=> 'tify_contest_poll'
		);
						
		// Actions et Filtres Wordpress
		add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );
		add_action( 'admin_init', array( $this, 'wp_admin_init' ) );
		add_action( 'admin_menu', array( $this, 'wp_admin_menu' ) );
		add_action( 'admin_print_styles', array( $this, 'wp_admin_print_styles' ) );;
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == == **/
	function wp_loaded(){
		// Chargement du gestionnaire de vue
		tify_require_lib( 'admin_view' );
		// Instanciation des contrôleurs
		/// Actions Ajax
		require_once $this->dir .'/ajax-actions.php';
		new tiFy_Contest_AjaxActions( $this->master );		
		/// Tableau de bord
		require_once $this->dir .'/dashboard.php';
		$this->dashboard = new tiFy_Contest_AdminDashboard( $this->master );
		/// Participants
		require_once $this->dir .'/participant.php';
		$this->participant = new tiFy_Contest_AdminParticipant( $this->master );	
		/// Participations
		require_once $this->dir .'/participation.php';
		$this->participation = new tiFy_Contest_AdminParticipation( $this->master );
		/// Votes
		require_once $this->dir .'/poll.php';
		$this->poll = new tiFy_Contest_AdminPoll( $this->master );
	}
	
	/** == == **/
	function wp_admin_init(){
		foreach( $this->menu_slug as $key => $menu_slug )
			$this->hookname[$key] = get_plugin_page_hookname( $menu_slug, $this->menu_slug['parent'] );
	}	
	
	/** == Menu d'administration == **/
	function wp_admin_menu(){
		$this->hookname['parent'] = add_menu_page( 
			'tify_contest', 
			__( 'Jeux Concours', 'tify' ), 
			'manage_options', 
			$this->menu_slug['parent'], 
			array( $this->dashboard, 'admin_render' ) 
		);
		// Tableau de bord
		$this->hookname['dashboard'] = add_submenu_page( 
			$this->menu_slug['parent'], 
			__( 'Tableau de bord', 'tify' ), 
			__( 'Tableau de bord', 'tify' ), 
			'manage_options', 
			$this->menu_slug['parent'], 
			array( $this->dashboard, 'admin_render' )
		 );
		// Participants
		$this->hookname['participant'] = add_submenu_page( 
			$this->menu_slug['parent'], 
			__( 'Participants', 'tify' ),
			__( 'Participants', 'tify' ), 
			'manage_options', 
			$this->menu_slug['participant'], 
			array( $this->participant, 'admin_render' ) 
		);
		// Participations
		$this->hookname['participation'] = add_submenu_page( 
			$this->menu_slug['parent'], 
			__( 'Participations', 'tify' ), 
			__( 'Participations', 'tify' ), 
			'manage_options', 
			$this->menu_slug['participation'], 
			array( $this->participation, 'admin_render' ) 
		);
		// Participations
		$this->hookname['poll'] = add_submenu_page( 
			$this->menu_slug['parent'], 
			__( 'Votes', 'tify' ), 
			__( 'Votes', 'tify' ), 
			'manage_options', 
			$this->menu_slug['poll'], 
			array( $this->poll, 'admin_render' ) 
		);
	}
	
	/** == Scripts personnalisées de l'entête == **/
	function wp_admin_print_styles(){
	?><style type="text/css">#adminmenu #toplevel_page_tify_wistify .menu-icon-generic div.wp-menu-image:before{content: "\e000";}#toplevel_page_tify_wistify div.wp-menu-image:before{font:400 20px/1 wistify !important;}</style><?php	
	}
}