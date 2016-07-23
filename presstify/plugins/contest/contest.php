<?php
/*
Plugin Name: Contest
Plugin URI: http://presstify.com/plugins/contest
Description: Gestion de jeu concours
Version: 0.150813
Author: Milkcreation
Author URI: http://profile.milkcreation.fr/jordy
*/
global $tify_contest;
$tify_contest = New tiFy_Contest_Master;

class tiFy_Contest_Master{
	/* = ARGUMENTS = */
	public 	// Chemins
			$dir, $uri,
			
			// Configuration
			$roles,
			
			// Paramètres
			$registred_contest 	= array(),		// Paramètres des jeux concours			
			$edit_locked		= false,
			$errors,
			
			// Contrôleurs			
			/// Bases de données
			$db_participant,
			$db_participation,
			$db_poll,
			$db_ranking,
			
			/// Références
			$admin,
			$capabilities,		
			$forms,
			$participant,
			$participation,
			$social,
			$tasks,
			$template;
	
	/* = CONSTRUCTEUR = */
	function __construct(){
		// Définition des chemins	
		$this->dir 	= dirname(__FILE__);
		$this->uri	= plugin_dir_url(__FILE__);
		
		// Configuration
		$this->roles 					= array( 
			'tify_contest_participants' => array(
				'name' 					=> __( 'Participant aux jeux concours', 'tify' ),
				'capabilities'			=> array(),
				'show_admin_bar_front' 	=> false
			)
		);
		
		// Contrôleurs
		/// Base de données
		require_once $this->dir .'/inc/db.php';
		$this->db_participant	= new tiFy_Contest_ParticipantDb( $this );
		$this->db_participation = new tiFy_Contest_ParticipationDb( $this );
		$this->db_poll 			= new tiFy_Contest_PollDb( $this );
		$this->db_ranking		= new tiFy_Contest_RankingDb( $this );
		
		/// Interface d'administration
		require_once $this->dir .'/admin/admin.php';			
		$this->admin = new tiFy_Contest_Admin( $this );
		
		/// Habilitations
		require_once $this->dir .'/inc/capabilities.php';
		$this->capabilities = new tiFy_Contest_Capabilities( $this );
		
		/// Formulaires
		require_once $this->dir .'/inc/forms.php';
		$this->forms = new tiFy_Contest_Forms( $this );
		
		/// Réseaux sociaux
		require_once $this->dir .'/inc/social.php';
		$this->social = new tiFy_Contest_Social( $this );		
				
		/// Contrôleurs secondaires
		require_once $this->dir .'/inc/helpers.php';
		require_once $this->dir .'/inc/poll.php';
		require_once $this->dir .'/inc/ranking.php';
		
		// Instanciation de la Gestion des tâches
		require_once $this->dir .'/inc/tasks.php';
		$this->tasks = new tiFy_Contest_Tasks( $this );	
		
		/// Templates
		require_once $this->dir .'/inc/general-template.php';
		$this->template = new tiFy_Contest_Template( $this );	

		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ), 8 );	
		add_filter( 'query_vars', array( $this, 'wp_query_vars' ) );			
	}

	/* = PARAMETRAGE = */
	/** == Enregistrement d'un jeu concours == **/
	public function register( $contest_id, $params = array() ){
		if( $params = $this->parse_contest_params( $params ) )
			$this->registred_contest[$contest_id] = $params;
	}
	
	/** == Traitement des paramètres d'un jeu concours == **/
	private function parse_contest_params( $params = array() ){
		$defaults = array(
			'title'				=> '',
			'form'				=> array(),			// (requis) Paramètres du formulaire de participation @see tify_form
			'participations' 	=> array(),			// Attributs de participation
			'poll'				=> array(),			// Attributs de vote
			'social'			=> array(),			// Attributs des réseaux sociaux
			'winners'			=> array(),			// Attributs des gagnants
			'extras' 			=> array()			// Attributs supplémentaires		
 		);
		$params = wp_parse_args( $params, $defaults );		
		extract( $params );
		
		// Le jeux concours doit contenir des attributs de formulaires
		if( ! $form )
			return;
		
		$participations = $this->parse_participation_args( $participations );
		$poll 			= $this->parse_poll_args( $poll );
		$social			= $this->parse_social_args( $social );
				
		return  compact( array_keys( $defaults ) );		
	}
	
	/** == Traitement des attributs de participation == **/
	public function parse_participation_args( $args = array() ){
		$defaults =	array(
			'start'		=> false,			// Date de début du jeu concours
			'end'		=> false,			// Date de fin du jeu concours
			'max'		=> 	-1,				// Nombre de participation maximum
			'user'		=> array()
		);
		$args = wp_parse_args( $args, $defaults );		
		extract( $args );
		
		$user = $this->parse_user_args( $user );
		
		return  compact( array_keys( $defaults ) );
	}
	
	/** == Traitement des attributs de vote == **/
	public function parse_poll_args( $args = array() ){
		$defaults =	array(
			'start'		=> false,			// Date de début d'ouverture au vote
			'end'		=> false,			// Date de fin d'ouverture au vote
			'max'		=> 	-1,				// Nombre de vote maximum
			'user'		=> array()
		);
		$args = wp_parse_args( $args, $defaults );		
		extract( $args );
		
		$user = $this->parse_user_args( $user );
		
		return  compact( array_keys( $defaults ) );
	}
	
	/** == Traitement des attributs de vote == **/
	public function parse_social_args( $args = array() ){
		$defaults =	array(
			'fb_app_id'				=> '',		// (requis)
			'fb_app_secret'			=> '',		// (requis)
			'fb_api_version'		=> 'v2.4',	// (requis)
			'fb_client_id'			=> '',		// (optionnel) client_id du compte de service (requis pour l'execution des tâches planifiées)
			'fb_client_secret'		=> '',		// (optionnel) client_secret du compte de service (requis pour l'execution des tâches planifiées)
			'fb_page_feed'			=> '',		// (optionnel) page_id de la fan page, active les partages sur la fan page)
			'fb_user_feed'			=> false	// (optionnel) Active le partages sur la page des visiteurs
		);
		return $args = wp_parse_args( $args, $defaults );
	}
	
	/** == Traitement des attributs relatif aux utilisateurs == **/
	public function parse_user_args( $args = array() ){
		$defaults =	array(
			'roles'	=> array(),			// Rôle habilités 
			'max'	=> -1				// Nombre maximum par utilisateur
		);
		
		return wp_parse_args( $args, $defaults );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	public function wp_init(){
		// Récupération des enregistrements de jeux concours	
		do_action( 'tify_contest_register' );
		
		// Définition des paramètres
		/** @todo pour les votes etc ... **/
		foreach( $this->registred_contest as $contest_id => $attrs )
			$this->social->contest_params[$contest_id ] = $attrs['social'];
		
	}
	
	/** == Définition des arguments de requête == **/	
	public function wp_query_vars( $aVars ) {
		$aVars[] = 'tify_contest_id';
		$aVars[] = 'tify_contest_part';
		
	  	return $aVars;
	}
	
	/* = CONTROLEURS = */		
	/** == Vérifie l'existance d'un jeu concours == **/
	public function is_registred( $contest_id ){
		return isset( $this->registred_contest[$contest_id] );
	}
	
	/** == Récupération de la liste des jeux concours == **/
	public function get_list( ){
		return $this->registred_contest;				
	}
	
	/** == Récupération de la liste des jeux concours ouverts au vote == **/
	public function get_poll_opens( $args = array() ){
		$opens = array();

		foreach( $this->registred_contest as $contest_id => $contest_args )
			if( $this->capabilities->is_poll_open( $contest_id ) )
				$opens[$contest_id] = $contest_args;
		
		return $opens;			
	}
}