<?php
class tiFy_WebAgencyCRM_Admin{
	/* = ARGUMENTS = */
	public 	// Configuration
			$dir,
			$uri,
						
			// Contrôleurs
			$dashboard,
			$customer,
			$gandi,
			$partner,
			$project,
			$task,
			$team;
			
	private	// Référence
			$master;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_WebAgencyCRM_Master $master ){
		// Définition de la classe de référence
		$this->master = $master;
		
		// Définition des chemins
		$this->dir = $this->master->dir .'/admin';
		$this->uri = $this->master->uri .'admin';
						
		// Actions et Filtres Wordpress
		add_action( 'admin_menu', array( $this, 'wp_admin_menu' ) );
		
		// Chargement du gestionnaire de vue
		tify_require( 'admin_view2' );
		
		// Instanciation des contrôleurs	
		/// Tableau de bord
		require_once $this->dir .'/dashboard.php';
		$this->dashboard = new tiFy_WebAgencyCRM_AdminDashboard( $this->master );
		
		/// Clients
		require_once( $this->dir .'/inc/customer-table.php' );
		require_once( $this->dir .'/inc/customer-edit.php' );
		$query = new tiFy_Query( $this->master->db->customer );
		$this->customer = new tiFy_AdminView( 
							$query, 
							array(
								'parent_slug'	=> $this->master->menu_slug['parent'],
								'menu_slug'		=> $this->master->menu_slug['customer'],
								'list_table'	=> new tiFy_WebAgencyCRM_AdminListCustomer( $this->master, $query ),
								'edit_form'		=> new tiFy_WebAgencyCRM_AdminEditCustomer( $this->master, $query )
							) 
						);
		
		/// Equipe
		require_once( $this->dir .'/inc/team-table.php' );
		require_once( $this->dir .'/inc/team-edit.php' );
		$query = new tiFy_Query( $this->master->db->team );
		$this->team = new tiFy_AdminView( 
							$query, 
							array(
								'parent_slug'	=> $this->master->menu_slug['parent'],
								'menu_slug'		=> $this->master->menu_slug['team'],
								'list_table'	=> new tiFy_WebAgencyCRM_AdminListTeam( $this->master, $query ),
								'edit_form'		=> new tiFy_WebAgencyCRM_AdminEditTeam( $this->master, $query )
							) 
						);
				
		/// Partenaires
		require_once( $this->dir .'/inc/partner-table.php' );
		require_once( $this->dir .'/inc/partner-edit.php' );
		$query = new tiFy_Query( $this->master->db->partner );
		$this->partner = new tiFy_AdminView( 
							$query, 
							array(
								'parent_slug'	=> $this->master->menu_slug['parent'],
								'menu_slug'		=> $this->master->menu_slug['partner'],
								'list_table'	=> new tiFy_WebAgencyCRM_AdminListPartner( $this->master, $query ),
								'edit_form'		=> new tiFy_WebAgencyCRM_AdminEditPartner( $this->master, $query )
							) 
						);

		/// Projets
		require_once( $this->dir .'/inc/project-table.php' );
		require_once( $this->dir .'/inc/project-edit.php' );
		$query = new tiFy_Query( $this->master->db->project );
		$this->project = new tiFy_AdminView( 
							$query, 
							array(
								'parent_slug'	=> $this->master->menu_slug['parent'],
								'menu_slug'		=> $this->master->menu_slug['project'],
								'list_table'	=> new tiFy_WebAgencyCRM_AdminListProject( $this->master, $query ),
								'edit_form'		=> new tiFy_WebAgencyCRM_AdminEditProject( $this->master, $query )
							) 
						);
		
		/// Tâches
		require_once( $this->dir .'/inc/task-table.php' );
		require_once( $this->dir .'/inc/task-edit.php' );
		$query = new tiFy_Query( $this->master->db->task );
		$this->task = new tiFy_AdminView( 
							$query, 
							array(
								'parent_slug'	=> $this->master->menu_slug['parent'],
								'menu_slug'		=> $this->master->menu_slug['task'],
								'list_table'	=> new tiFy_WebAgencyCRM_AdminListTask( $this->master, $query ),
								'edit_form'		=> new tiFy_WebAgencyCRM_AdminEditTask( $this->master, $query )
							) 
						);
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Menu d'administration == **/
	public function wp_admin_menu(){		
		// Entrée de Menu parente
		$this->master->hookname['parent'] = add_menu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Espace Clients', 'tify' ), 
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
		
		// Partenaires
		$this->master->hookname['partner'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Partenaires', 'tify' ),
			__( 'Partenaires', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['partner'], 
			array( $this->partner, 'render' ) 
		);
				
		// Clients
		$this->master->hookname['customer'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Clients', 'tify' ),
			__( 'Clients', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['customer'], 
			array( $this->customer, 'render' ) 
		);
		
		// Équipe
		$this->master->hookname['team'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Équipe', 'tify' ),
			__( 'Équipe', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['team'], 
			array( $this->team, 'render' ) 
		);	
		
		// Projets
		$this->master->hookname['project'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Projets', 'tify' ),
			__( 'Projets', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['project'], 
			array( $this->project, 'render' ) 
		);
		
		// Tâches
		$this->master->hookname['task'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Tâches', 'tify' ),
			__( 'Tâches', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['task'], 
			array( $this->task, 'render' ) 
		);
	}	
}