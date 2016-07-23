<?php
/*
Addon Name: Wistify
Addon URI: http://presstify.com/addons/premium/wistify
Description: Gestion d'envoi de campagne d'emailing via Mandrill
Version: 1509271926
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

/**
 * @see http://templates.mailchimp.com/development/
 * @see http://blog.simple-mail.fr/2014/11/07/guide-eviter-etre-considere-spam/
 */
 
global $wistify;
$wistify = new tiFy_Wistify_Master; 
 
class tiFy_Wistify_Master{
	/* = ARGUMENTS = */
	public 	// Configuration
			/// Chemins		
			$dir, $uri,
			
			/// Version
			$version = 1509271926,			
			
			// Paramètres
			$installed,		// Numéro de version installée
			$menu_slug 	= array(),
			$hookname 	= array(),		
			
			// Contrôleurs			
			/// Bases de données
			$db_campaign,
			$db_list,
			$db_list_rel,
			$db_queue,
			$db_report,
			$db_subscriber,
			
			/// Références
			$admin,
			$forms,		
			$options,
			$queue,			
			$tasks,
			$templates,					
			$Mandrill;			
			
	/* = CONSTRUCTEUR = */
	public function __construct(){
		// Définition des chemins
		$this->dir = dirname( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );
		
		// Configuration
		$this->menu_slug = array(
			'parent'		=> 'tify_wistify',
			'campaign'		=> 'tify_wistify_campaign',
			'subscriber'	=> 'tify_wistify_subscriber',
			'list'			=> 'tify_wistify_list',
			'options'		=> 'tify_wistify_options',
			'report'		=> 'tify_wistify_report',
			'maintenance'	=> 'tify_wistify_maintenance'
		);		
			
		// Contrôleurs
		/// Base de données
		require_once $this->dir .'/inc/db.php';
		$this->db_campaign 		= new tiFy_Wistify_DbCampaign( $this );
		$this->db_list_rel		= new tiFy_Wistify_DbListRelationships( $this );
		$this->db_subscriber 	= new tiFy_Wistify_DbSubscriber( $this );
		$this->db_list			= new tiFy_Wistify_DbList( $this );		
		$this->db_queue			= new tiFy_Wistify_DbQueue( $this );
		$this->db_report		= new tiFy_Wistify_DbReport( $this );	
		
		/// Interface d'administration
		require_once $this->dir .'/admin/admin.php';			
		$this->admin = new tiFy_Wistify_Admin( $this );	
		
		/// Formulaires
		require_once $this->dir .'/inc/forms.php';
		$this->forms = new tiFy_Wistify_Forms_Main( $this );
		
		/// Gestion des options
		require_once $this->dir .'/inc/options.php';
		$this->options = new tiFy_Wistify_Options_Main( $this );
		//// Instanciation de l'API Mandrill
		if( $api_key = $this->get_mandrill_api_key() ) :
			tify_require_lib( 'mandrill' );		
			$this->Mandrill = new tiFy_Mandrill( $api_key );
		endif;
				
		/// Tâches
		require_once $this->dir .'/inc/tasks.php';
		$this->tasks = new tiFy_Wistify_Tasks( $this );
		
		/// Gabarits
		require_once $this->dir .'/inc/general-template.php';
		$this->templates = new tiFy_Wistify_Templates( $this );				
		
		// Mise à jour
		require_once $this->dir .'/inc/upgrade.php';
		new tiFy_Wistify_Upgrade( $this );
		
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );	
		add_action( 'admin_init', array( $this, 'wp_admin_init' ), 1 );
	}

	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	public function wp_init(){
		// Déclaration de la police de caractère
		wp_register_style( 'font-wistify', $this->uri .'/assets/fonts/wistify/styles.css', array(), 150405 );
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
		$global_options = get_option( 'wistify_global_options' );
		
		$api_key = ( ! $test ) ? $this->options->get( 'wistify_global_options', 'api_key' ) : $this->options->get( 'wistify_global_options', 'api_test_key' );			
			
		return apply_filters( 'wistify_mandrill_api_key', $api_key );
	}
}