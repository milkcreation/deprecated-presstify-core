<?php
/*
Plugin Name: Events
Plugin URI: http://presstify.com/plugins/events
Description: Gestion d'événements
Version: 1.150610
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

global $tify_events;
$tify_events = new tiFy_Events;

class tiFy_Events{
	/* = ARGUMENTS = */
	public 	// Chemins
			$dir,
			$uri,
			
			// Configuration
			$post_types 	= array(),			
			$by_day_limit	= -1,
			
			// Références
			$admin,
			$db,
			$query,
			$template;
			
	public	$timezone_string;
					
	/* = CONSTRUCTEUR = */
	function __construct(){
		// Définition des chemins	
		$this->dir 	= dirname(__FILE__);
		$this->uri	= plugin_dir_url(__FILE__);
		
		// Configuration		
		$this->timezone_string = get_option( 'timezone_string' );
				
		// Contrôleurs
		/// Interface d'administration
		require_once $this->dir .'/admin/admin.php';			
		$this->admin = new tiFy_EventsAdmin( $this );
		
		/// Base de données
		require_once( $this->dir .'/inc/db.php' );
		$this->db = new tiFy_EventsDb( $this );
		
		/// Requêtes
		require_once( $this->dir .'/inc/query.php' );
		$this->query = new tiFy_EventsQuery( $this );
		
		/// Templates
		require_once( $this->dir .'/inc/general-template.php' );
		$this->template = new tiFy_EventsTemplate( $this );
			
		// Actions et filtres Wordpress
		add_action( 'after_setup_tify', array( $this, 'after_setup_tify' ) );
	}
	
	/* = CONFIGURATION = */
	/** == Définition des types de post == **/
	function set_post_types(){
		$defaults = array(
			'taboox_auto'	=> true,				// Déclaration automatique de la boîte de sasie
			'form'			=> 'default',			// Type de saisie default | range
			'by_day_limit'	=> $this->by_day_limit,	// Limite de jour consecutifs pour l'affichage en jour séparés : -1 (illimité) | int
			'admin_preview'	=> true	
		);
		$post_types = apply_filters( 'tify_events_post_types', array() );
		
		foreach( $post_types as $k => $v )
			if( is_string( $v ) )
				$this->post_types[$v] = $defaults;
			elseif( is_array( $v ) )
				$this->post_types[$k] = wp_parse_args( $v, $defaults );
	}
	
	/* = CONTROLEURS = */
	/** == Récupération des types de posts == **/
	function get_post_types(){
		// Bypass
		if( ! is_array( $this->post_types ) )
			return array();
		
		return array_keys( $this->post_types );		
	}
	
	/** == Vérifie si le type de post est valide  == **/
	function is_post_type( $post_type ){
		return in_array( $post_type, $this->get_post_types() );
	}
	
	/** == Récupération d'une option de type de post  == **/
	function get_post_type_option( $post_type, $option ){
		// Bypass
		if( ! $this->is_post_type( $post_type ) )
			return;
		
		if( isset( $this->post_types[$post_type][$option] ) )
			return $this->post_types[$post_type][$option];
	}
	
	/** == Partitionnement d'un créneau de date == **/
	function split_date_range( $start_datetime, $end_datetime, $by_day_limit = -1 ){
		$range = array(); $k = 0;
		
		$s = new DateTime( $start_datetime ); $e = new DateTime( $end_datetime );
			
		if( $s->format( 'Ymd' ) === $e->format( 'Ymd' ) ) :
			$range[$k]['start_date'] 	= $s->format( 'Y-m-d' );
			$range[$k]['end_date'] 		= false;
			
			if( $s->format( 'Hi' ) === $e->format( 'Hi' ) ) :
				$range[$k]['start_time'] 	= $s->format( 'H:i:s' ); 
				$range[$k]['end_time'] 		= false;
			else :
				$range[$k]['start_time'] 	= $s->format( 'H:i:s' );
				$range[$k]['end_time'] 		= $e->format( 'H:i:s' );
			endif;	
		elseif( $s->format( 'Ymd' ) > $e->format( 'Ymd' ) ) :
			$range[$k]['start_date'] 	= $s->format( 'Y-m-d' );
			$range[$k]['end_date'] 		= false;		
			
			if( $s->format( 'Hi' ) === $e->format( 'Hi' ) ) :
				$range[$k]['start_time'] 	= $s->format( 'H:i:s' ); 
				$range[$k]['end_time'] 		= false;
			else :
				$range[$k]['start_time'] 	= $s->format( 'H:i:s' );
				$range[$k]['end_time'] 		= $e->format( 'H:i:s' );
			endif;
		else :
			$sdiff = new DateTime( $s->format( 'Y-m-d' ) );
			$ediff = new DateTime( $e->format( 'Y-m-d' ) );
			$diff = $sdiff->diff( $ediff );
			if( $by_day_limit == -1 )
				$by_day_limit = $diff->days;
			
			if( $diff->days && $diff->days <= $by_day_limit ) :			
				foreach( range( 0, $diff->days, 1 ) as $n ) :
					if( $n )
						$s->add( new DateInterval( 'P1D' ) );
					$range[$n]['start_date'] 	= $s->format( 'Y-m-d' );
					$range[$n]['end_date'] 		= false;
					
					if( $s->format( 'Hi' ) == $e->format( 'Hi' ) ) :
						$range[$n]['start_time'] 	= $s->format( 'H:i:s' ); 
						$range[$n]['end_time'] 		= false;
					else :
						$range[$n]['start_time'] 	= $s->format( 'H:i:s' );
						$range[$n]['end_time'] 		= $e->format( 'H:i:s' );
					endif;
				endforeach;
			else :
				$range[$k]['start_date'] = $s->format( 'Y-m-d' );
				$range[$k]['end_date'] = $e->format( 'Y-m-d' );
				
				if( $s->format( 'Hi' ) == $e->format( 'Hi' ) ) :
					$range[$k]['start_time'] = $s->format( 'H:i:s' ); 
					$range[$k]['end_time'] = false;
				else :
					$range[$k]['start_time'] = $s->format( 'H:i:s' );
					$range[$k]['end_time'] = $e->format( 'H:i:s' );
				endif;
			endif;
		endif;
		
		return $range;
	}

	/** == Affichage d'une plage de date partitionnée == **/
	function split_date_range_display( $split_date_range, $event_id = null, $post_id = null, $echo = true ){		
		$output = "";
	
		if( ! $output = apply_filters( 'tify_events_date_display', '', $split_date_range, $event_id, $post_id ) ) :
			if( ! $split_date_range['end_date'] )
				$output .= sprintf( __( 'le %s', 'tify' ), mysql2date( 'l d M', $split_date_range['start_date'] ) );
			elseif( (int) substr( $date['end_date'], 0, 4 ) > (int) substr( $split_date_range['start_date'], 0, 4 ) )
				$output .= sprintf( __( 'du %s au %s', 'tify' ), mysql2date( 'l d M Y', $split_date_range['start_date'] ), mysql2date( 'l d M Y', $split_date_range['end_date'] ) );
			else
				$output .= sprintf( __( 'du %s au %s', 'tify' ), mysql2date( 'l d M', $split_date_range['start_date'] ), mysql2date( 'l d M', $split_date_range['end_date'] ) );
			
			if( ! $split_date_range['end_time'] ) :
				$output .= sprintf( __( ' à %s', 'tify' ), preg_replace( '/^(\d{2}):(\d{2}):(\d{2})$/', '$1h$2', $split_date_range['start_time'] ) );
			else :
				$output .= sprintf( __( ' de %s à %s', 'tify' ), preg_replace( '/^(\d{2}):(\d{2}):(\d{2})$/', '$1h$2', $split_date_range['start_time'] ), preg_replace( '/^(\d{2}):(\d{2}):(\d{2})$/', '$1h$2', $split_date_range['end_time'] ) );
			endif;
		endif;
		
		if( $echo )
			echo $output;
		
		return $output;
	}
	
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation du thème == **/
	public function after_setup_tify(){
		$this->set_post_types();
	}
}