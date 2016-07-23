<?php
/*
Plugin Name: Web Agency
Plugin URI: http://presstify.com/addons/premium/web-agency-crm
Description: Gestion de client agences Web (ressources/clients/projets/hébergement)
Version: 1510171636
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

New tiFy_WebAgencyCRM_Master;
class tiFy_WebAgencyCRM_Master{
	/* = ARGUMENTS = */
	public 	// Configuration
			/// Version
			$version = 1510171636,
			
			// Chemins
			$dir, 
			$uri,
			
			// Paramètres
			$roles,
			$menu_slug 	= array(),
			$hookname 	= array(),
					
			// Contrôleurs
			$admin,
			$ajax,
			$db,
			$forms,
			$template,
			$tasks;
								
	/* = CONSTRUCTEUR = */
	public function __construct(){
		// Définition des chemins	
		$this->dir 	= dirname(__FILE__);
		$this->uri	= plugin_dir_url(__FILE__);
		
		// Configuration
		$this->roles 		= array( 
			'tify_wacrm_customer' => array(
				'name' 					=> __( 'Client', 'tify' ),
				'capabilities'			=> array(),
				'show_admin_bar_front' 	=> false
			),
			'tify_wacrm_team' => array(
				'name' 					=> __( 'Membre de l\'équipe', 'tify' ),
				'capabilities'			=> array(),
				'show_admin_bar_front' 	=> false
			)
		);
		$this->menu_slug = array(
			'parent'		=> 'tify_wacrm',
			'dashboard'		=> 'tify_wacrm_dashboard',
			'customer'		=> 'tify_wacrm_customer',
			'partner'		=> 'tify_wacrm_partner',
			'project'		=> 'tify_wacrm_project',
			'task'			=> 'tify_wacrm_task',
			'team'			=> 'tify_wacrm_team'
		);
				
		// Contrôleurs
		/// Base de données
		require_once $this->dir .'/inc/db.php';
		$this->db = new tiFy_WebAgencyCRM_DbMain( $this );
				
		/// Interface d'administration
		require_once $this->dir .'/inc/admin.php';			
		$this->admin = new tiFy_WebAgencyCRM_Admin( $this );
		
		/// Actions Ajax
		require_once $this->dir .'/inc/ajax-actions.php';			
		$this->ajax = new tiFy_WebAgencyCRM_MainAjaxActions( $this );	
											
		/// Formulaires
		require_once $this->dir .'/inc/forms.php';
		$this->forms = new tiFy_WebAgencyCRM_MainForms( $this );
		
		/// Gabarits
		require_once $this->dir .'/inc/general-template.php';
		$this->template = new tiFy_WebAgencyCRM_MainTemplate( $this );
		
		/// Tâches
		require_once $this->dir .'/inc/tasks.php';
		$this->tasks = new tiFy_WebAgencyCRM_MainTasks( $this );
		
		/// Helpers
		require_once $this->dir .'/inc/helpers.php';
	}		
}