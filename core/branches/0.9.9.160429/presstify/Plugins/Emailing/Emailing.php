<?php
/*
Plugin Name: Emailing
Plugin URI: http://presstify.com/plugins/premiums/Emailing
Description: Gestionnaire de campagne d'emailing
Version: 1.160423
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

namespace tiFy\Plugins\Emailing; 

use tiFy\Environment\Plugin;
 
class Emailing extends Plugin
{
	/* = ARGUMENTS = */
	public 	// Configuration
			$version = 1.151226,		
			
			// Paramètres
			$installed,		// Numéro de version installée
			$menu_slug 	= array(),
			$hookname 	= array(),		
			
			// Contrôleurs			
			/// Bases de données
			$db,
			
			/// Références
			$admin,
			$ajax_actions,
			$forms,		
			$options,
			$queue,			
			$tasks,
			$templates,					
			$Mandrill;			
			
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
					
		// Contrôleurs
		/// Interface d'administration		
		new Admin;	
		
		/// Elément de Gabarits
		new GeneralTemplate;
		
		require_once( $this->Dirname .'/Helpers.php' );
		/// Actions ajax
		//$this->ajax_actions = new Core\AjaxActions( $this );	
		
		/// Formulaires
		//$this->forms = new Core\Forms( $this );
		
		/// Gestion des options
		//$this->options = new Core\Options( $this );
		
		//// Instanciation de l'API Mandrill
		/*if( $api_key = $this->get_mandrill_api_key() ) :
			tify_require_lib( 'mandrill' );		
			$this->Mandrill = new \tiFy_Mandrill( $api_key );
		endif;
				
		/// Tâches
		//$this->tasks = new Core\Tasks( $this );
		
		/// Gabarits
		//$this->templates = new Core\Templates( $this );				
		
		// Mise à jour
		//new Core\Upgrade( $this );
		
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );	
		add_action( 'admin_init', array( $this, 'wp_admin_init' ), 1 );*/
	}

	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	public function wp_init(){
		// Définition des contrôleurs de base de données
		$this->db 				= new \stdClass;	
		$this->db->campaign 	= new Db\Campaign( 'wistify_campaign' );
		$this->db->list_rel		= new Db\MailingList_Relationships( 'wistify_list_relationships' );
		$this->db->subscriber 	= new Db\Subscriber( 'wistify_subscriber' );
		$this->db->list			= new Db\MailingList( 'wistify_list' );	
		$this->db->queue		= new Db\MailQueue( 'wistify_queue' );
		$this->db->report		= new Db\Report( 'wistify_report' );
	}
	
	/** == Initialisation de l'interface d'administration == **/
	public function wp_admin_init(){
		// Définition des hooknames des pages d'administration
		$parent_page = $this->menu_slug['parent'];
		$plugin_pages = $this->menu_slug;		
		unset( $plugin_pages[$parent_page] );
		
		foreach( $plugin_pages as $key => $plugin_page )
			$this->hookname[$key] = get_plugin_page_hook( $plugin_page, $parent_page );	
	}

	/* = CONTROLEUR = */
	/** == Récupération de la clé d'api Mandrill == **/
	public function get_mandrill_api_key( $test = false ){
		$api_key = ( ! $test ) ? $this->Config['mandrill_api_key'] : $this->Config['mandrill_api_key_test'];			
			
		return apply_filters( 'wistify_mandrill_api_key', $api_key );
	}
}