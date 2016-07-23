<?php
namespace tiFy\Plugins\Emailing;

use tiFy\Environment\App;

class Admin extends App
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'wp_loaded',
		'admin_menu',
		'admin_notices'
	);
				
	/* = ACTIONS = */
	/** == Chargement de Wordpress == **/
	public function wp_loaded()
	{
		return;
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
		$this->campaign->setDb( $this->master->db->campaign );	
		
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
		$this->list->setDb( $this->master->db->list );		
	
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
		$this->report->setDb( $this->master->db->report );	
	
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
		$this->subscriber->setDb( $this->master->db->subscriber );
	}
	
	/** == Menu d'administration == **/
	final public function admin_menu()
	{
		return;
		$this->master->hookname['parent'] = add_menu_page( 
			'tify_wistify', 
			__( 'Newsletters', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['parent'], 
			array( $this->dashboard, 'admin_render' ),
			'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJDYWxxdWVfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSI2My41NTZweCIgaGVpZ2h0PSI3NC4wNzRweCIgdmlld0JveD0iMTguNjkxIC0xLjU5MyA2My41NTYgNzQuMDc0IiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDE4LjY5MSAtMS41OTMgNjMuNTU2IDc0LjA3NCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgZmlsbD0iIzAwMCI+PGc+PHBhdGggZD0iTTc5LjkzMiw0MS43bC0yLjU1MS00LjQ0NWMtMC41NzYsNC4wMzMtMi4yMjMsNi45OTYtNC4xOTcsNi45OTZWMjQuNDk4YzAuODIyLDAsMS41NjMsMC40OTQsMi4xNCwxLjMxN3YtNC4zNjJjMC0xMi43NTctMTAuMjg4LTIzLjA0NS0yMy4wNDYtMjMuMDQ1aC0zLjc4NmMtMTIuNzU3LDAtMjMuMDQ1LDEwLjI4OC0yMy4wNDUsMjMuMDQ1djQuNDQ0YzAuNjU5LTAuODIzLDEuMzk5LTEuMzE3LDIuMTQtMS4zMTd2MTkuNzUzYy0xLjk3NSwwLTMuNjIyLTIuOTYzLTQuMTk4LTYuOTk2bC0yLjU1MSw0LjQ0NGMtNC42MDksOC4zOTUtMS41NjQsMTUuNTU2LDYuOTE0LDE3LjM2N2MzLjYyMiw3LjksMTEuNjA1LDEzLjMzMywyMC44MjMsMTMuMzMzaDMuNzg1YzkuMjE5LDAsMTcuMjAyLTUuNDMzLDIwLjkwNi0xMy4zMzNDODEuNzQyLDU3LjMzOCw4NC43ODgsNTAuMTc3LDc5LjkzMiw0MS43eiBNNTEuMTI1LDY3LjM3OWgtMS4zMTZjLTQuNjkxLDAtOC40NzgtMy43ODYtOC40NzgtOC40Nzh2LTQuNzc0YzIuOTYzLTAuNjU4LDYuMDA4LTIuNTUxLDkuMDU0LTIuNTUxYzMuMDQ1LDAsNi4wOSwxLjg5Myw5LjEzNiwyLjU1MXY0Ljc3NEM1OS42MDQsNjMuNTkzLDU1LjgxNiw2Ny4zNzksNTEuMTI1LDY3LjM3OXogTTY4LjY1NiwzOS44MDdjMCw1LjUxNC0zLjQ1NywxMC4xMjQtOC4zOTUsMTEuOTM0Yy0wLjQxMi0wLjY1OC0xLjIzNC0xLjMxNi0xLjk3Ni0xLjQ4MWMtNS4xODYtMC45MDUtMTAuNDUzLTAuOTA1LTE1LjYzOCwwYy0wLjc0MSwwLjE2NS0xLjU2MywwLjc0MS0xLjk3NSwxLjM5OWMtNC44NTYtMS44MTEtOC4yMy02LjQxOS04LjIzLTExLjkzNFYyNy4wNDljMC00LjAzMyw0LjYwOS03LjQwNywxMC4yMDYtNy40MDdjMy4yMSwwLDYuMDA4LDEuMDcsNy45MDEsMi43MTZjMS44OTQtMS42NDYsNC42OTEtMi43MTYsNy45MDItMi43MTZjNS41OTYsMCwxMC4yMDUsMy4yOTIsMTAuMjA1LDcuNDA3VjM5LjgwN3oiLz48cGF0aCBkPSJNNDguNzM5LDM4LjczN2MtMS42NDYtMS4wNy0zLjIxLTAuODIzLTMuNDU3LDAuNjU4Yy0wLjMyOSwxLjM5OSwxLjA3LDIuMzA1LDIuOTYzLDEuOTc1QzUwLjEzOCw0MC45NTksNTAuMzg1LDM5LjgwNyw0OC43MzksMzguNzM3eiIvPjxwYXRoIGQ9Ik01Mi4zNTksMzguNzM3Yy0xLjY0NiwxLjA3LTEuMzk4LDIuMjIyLDAuNDk0LDIuNjMzYzEuODk0LDAuMzMsMy4yMTEtMC40OTQsMi45NjMtMS45NzVDNTUuNDg4LDM3Ljk5Niw1My45MjQsMzcuNjY3LDUyLjM1OSwzOC43Mzd6Ii8+PHBhdGggZD0iTTQxLjMzMSwyNy45NTVjLTIuNTUxLDAtNC42MDksMi4wNTgtNC42MDksNC42MDloOS4xMzZDNDUuODU4LDMwLjAxMiw0My44LDI3Ljk1NSw0MS4zMzEsMjcuOTU1eiIvPjxwYXRoIGQ9Ik01OS42MDQsMjcuOTU1Yy0yLjU1MywwLTQuNjA5LDIuMDU4LTQuNjA5LDQuNjA5aDkuMTM2QzY0LjIxMiwzMC4wMTIsNjIuMTU0LDI3Ljk1NSw1OS42MDQsMjcuOTU1eiIvPjwvZz48L3N2Zz4='
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
	public function admin_notices() 
	{
		return;
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