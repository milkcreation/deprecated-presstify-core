<?php
namespace tiFy\Plugins\Wistify\Admin;

use tiFy\Plugins\Wistify\Wistify;

class Admin{
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
	public function __construct( Wistify $master ){
		// Définition de la classe de référence
		$this->master = $master;
		
		// Définition des chemins
		$this->dir = $this->master->Dir .'/Admin';
		$this->uri = $this->master->Url .'/Admin';	
								
		// Actions et Filtres Wordpress
		add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );
		add_action( 'admin_menu', array( $this, 'wp_admin_menu' ) );		
		add_action( 'admin_notices', array( $this, 'wp_admin_notices' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Chargement de Wordpress == **/
	public function wp_loaded(){
		// Instanciation des contrôleurs			
		/// Campagnes	
		$this->campaign = new \tiFy\Entity\AdminView\AdminView( 
			'wistify_campaign', 
			array(
				'list_table'	=> array(
					'parent_slug'	=> $this->master->menu_slug['parent'],
					'menu_slug'		=> $this->master->menu_slug['campaign'],
					'callback'		=> new CampaignListTable( $this->master )
				),
				'edit_form'			=> array(
					'parent_slug'	=> $this->master->menu_slug['parent'],
					'menu_slug'		=> $this->master->menu_slug['campaign'],
					'callback'		=> new CampaignEditForm( $this->master )
				)
			) 
		);	
		
		/// Tableau de bord
		$this->dashboard = new Dashboard( $this->master );

		/// Listes de diffusion
		$this->list = new \tiFy\Entity\AdminView\AdminView( 
			'wistify_list', 
			array(
				'list_table'	=> array(
					'parent_slug'	=> $this->master->menu_slug['parent'],
					'menu_slug'		=> $this->master->menu_slug['list'],
					'callback'		=> new MailingListListTable( $this->master )
				),
				'edit_form'			=> array(
					'parent_slug'	=> $this->master->menu_slug['parent'],
					'menu_slug'		=> $this->master->menu_slug['list'],
					'callback'		=> new MailingListEditForm( $this->master )
				)
			) 
		);		
	
		/// Options
		$this->options = new Options( $this->master );
				
		/// Rapport d'envoi des messages acheminés
		$this->report = new \tiFy\Entity\AdminView\AdminView( 
			'wistify_report', 
			array(
				'list_table'	=> array(
					'parent_slug'	=> $this->master->menu_slug['parent'],
					'menu_slug'		=> $this->master->menu_slug['report'],
					'callback'		=> new ReportListTable( $this->master )
				)
			) 
		);	
	
		/// Maintenance
		$this->maintenance = new Maintenance( $this->master );

		/// Abonnés
		$this->subscriber = new \tiFy\Entity\AdminView\AdminView(
			'wistify_subscriber', 
			array(
				'list_table'	=> array(
					'parent_slug'	=> $this->master->menu_slug['parent'],
					'menu_slug'		=> $this->master->menu_slug['subscriber'],
					'callback'		=> new SubscriberListTable( $this->master )
				),
				'edit_form'			=> array(
					'parent_slug'	=> $this->master->menu_slug['parent'],
					'menu_slug'		=> $this->master->menu_slug['subscriber'],
					'callback'		=> new SubscriberEditForm( $this->master )
				),
				'import'			=> array(
					'parent_slug'	=> $this->master->menu_slug['parent'],
					'menu_slug'		=> $this->master->menu_slug['subscriber'],
					'callback'		=> new SubscriberImport( $this->master )
				)
			) 
		);
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
			array( $this->subscriber, 'render' ) 
		);
		
		// Listes de diffusion
		$this->master->hookname['list'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Listes de diffusion', 'tify' ), 
			__( 'Listes de diffusion', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['list'], 
			array( $this->list, 'render' ) 
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
			array( $this->report, 'render' ) 
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