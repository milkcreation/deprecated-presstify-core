<?php
class tiFy_Wistify_Admin{
	/* = ARGUMENTS = */
	public 	// Configuration
			$dir,
			$uri,
					
			// Contrôleurs
			$campaign,
			$dashboard,
			$list,
			$maintenance,
			$options,
			$report,
			$subscriber;
			
	private	// Référence
			$master;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Wistify_Master $master ){
		// Définition de la classe de référence
		$this->master = $master;
		
		// Définition des chemins
		$this->dir = $this->master->dir .'/admin';
		$this->uri = $this->master->uri .'admin';
								
		// Actions et Filtres Wordpress
		add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );
		add_action( 'admin_menu', array( $this, 'wp_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'wp_admin_init' ) );		
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_admin_enqueue_scripts' ) );
		add_action( 'admin_print_styles', array( $this, 'wp_admin_print_styles' ) );
		add_action( 'admin_notices', array( $this, 'wp_admin_notices' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Chargement de Wordpress == **/
	public function wp_loaded(){
		// Chargement du gestionnaire de vue
		tify_require( 'admin_view' );
		tify_require( 'admin_view2' );
		// Instanciation des contrôleurs
		/// Actions Ajax
		require_once $this->dir .'/ajax-actions.php';
		new tiFy_Wistify_AjaxActions( $this->master );		
		/// Campagnes
		//require_once $this->dir .'/campaign.php';
		//$this->campaign = new tiFy_Wistify_AdminCampaign( $this->master );
		require_once( $this->dir .'/inc/campaign-table.php' );
		require_once( $this->dir .'/inc/campaign-edit.php' );
		$query = new tiFy_Query( $this->master->db_campaign );
		$this->campaign = new tiFy_AdminView( 
							$query, 
							array(
								'parent_slug'	=> $this->master->menu_slug['parent'],
								'menu_slug'		=> $this->master->menu_slug['campaign'],
								'list_table'	=> new tiFy_Wistify_AdminListCampaign( $this->master, $query ),
								'edit_form'		=> new tiFy_Wistify_AdminEditCampaign( $this->master, $query )
							) 
						);		
		
		/// Tableau de bord
		require_once $this->dir .'/dashboard.php';
		$this->dashboard = new tiFy_Wistify_AdminDashboard( $this->master );	
		/// Listes de diffusion
		require_once $this->dir .'/list.php';
		$this->list = new tiFy_Wistify_AdminList( $this->master );
		/// Options
		require_once $this->dir .'/options.php';
		$this->options = new tiFy_Wistify_AdminOptions( $this->master );		
		/// Rapport d'envoi des messages acheminés
		require_once $this->dir .'/report.php';
		$this->report = new tiFy_Wistify_AdminReport( $this->master );		
		/// Maintenance
		require_once $this->dir .'/maintenance.php';
		$this->maintenance = new tiFy_Wistify_Maintenance( $this->master );
		/// Abonnés
		require_once $this->dir .'/subscriber.php';
		$this->subscriber = new tiFy_Wistify_AdminSubscriber( $this->master );	
	}
	
	/** == Menu d'administration == **/
	public function wp_admin_menu(){
		$this->master->hookname['parent'] = add_menu_page( 
			'tify_wistify', 
			__( 'Newsletters', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['parent'], 
			array( $this->dashboard, 'admin_render' ) 
		);
		
		// Tableau de bord
		$this->master->hookname['dashboard'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Tableau de bord', 'tify' ), 
			__( 'Tableau de bord', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['parent'], 
			array( $this->dashboard, 'admin_render' )
		 );
		 
		// Campagnes
		$this->master->hookname['campaign'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Campagnes', 'tify' ),
			__( 'Campagnes', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['campaign'], 
			array( $this->campaign, 'render' ) 
		);
		
		// Abonnés
		$this->master->hookname['subscriber'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Abonnés', 'tify' ), 
			__( 'Abonnés', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['subscriber'], 
			array( $this->subscriber, 'admin_render' ) 
		);
		
		// Listes de diffusion
		$this->master->hookname['list'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Listes de diffusion', 'tify' ), 
			__( 'Listes de diffusion', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['list'], 
			array( $this->list, 'admin_render' ) 
		);
		
		// Options
		$this->master->hookname['options'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Options', 'tify' ), 
			__( 'Options', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['options'], 
			array( $this->options, 'admin_render' ) 
		);
		
		// Rapports d'acheminement
		$this->master->hookname['report'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Rapport d\'envoi', 'tify' ),
			__( 'Rapport d\'envoi', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['report'], 
			array( $this->report, 'admin_render' ) 
		);
		
		// Maintenance
		$this->master->hookname['maintenance'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Maintenance des newsletters', 'tify' ),
			__( 'Maintenance', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['maintenance'], 
			array( $this->maintenance, 'admin_render' ) 
		);
	}

	/** == Initialisation de l'interface d'administration == **/
	public function wp_admin_init(){
		foreach( $this->master->menu_slug as $key => $menu_slug )
			$this->master->hookname[$key] = get_plugin_page_hookname( $menu_slug, $this->master->menu_slug['parent'] );
	}

	/** == Mise en file des scripts == **/
	public function wp_admin_enqueue_scripts(){
		wp_enqueue_style( 'font-wistify' );
	}
	
	/** == Scripts personnalisée de l'entête" == **/
	public function wp_admin_print_styles(){
	?><style type="text/css">#adminmenu #toplevel_page_tify_wistify .menu-icon-generic div.wp-menu-image:before{content: "\e000";}#toplevel_page_tify_wistify div.wp-menu-image:before{font:400 20px/1 wistify !important;}</style><?php	
	}
	
	/** == Messages d'alerte == **/
	public function wp_admin_notices() {
		// Bypass
		if( empty( get_current_screen()->parent_base ) || ( get_current_screen()->parent_base !== 'tify_wistify' ) )
			return;
		
		$show_notice = false;
		if( ! $this->master->get_mandrill_api_key() ) :
			$show_notice = __( 'La clé d\'API Mandrill doit être renseignée.', 'tify' );
		else :
			try {
				$result = $this->master->Mandrill->users->ping( );		
			} catch( Mandrill_Error $e ) {
				$show_notice = __( 'La clé d\'API Mandrill fournie n\'est pas valide.', 'tify' );
			}
		endif;
		if( ! $show_notice )
			return;
    ?><div class="error"><p><?php _e( $show_notice, 'tify' ); ?></p></div><?php
	}
}