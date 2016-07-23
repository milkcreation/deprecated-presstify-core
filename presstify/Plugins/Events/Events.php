<?php
/*
Plugin Name: Events
Plugin URI: http://presstify.com/plugins/events
Description: Gestion d'événements
Version: 1.150610
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

namespace tiFy\Plugins\Events; 

use tiFy\Environment\Plugin;

class Events extends Plugin
{
	/* = ARGUMENTS = */	
	// Configuration 
	private static	$PostTypes 		= array();
	
	// Contrôleurs
	private static $GeneralTemplate;
	private static $Entity;
	private static $Query;
					
	/* = CONSTRUCTEUR = */
	public function __construct(){	
		parent::__construct();
		
		// Instanciation des contrôleurs
		new GeneralTemplate;
		new Query;
		
		require_once( $this->Dirname . '/Helpers.php' );
				
		// Définition des types de post
		/// Arguments par défaut
		$defaults = array(
			// Déclaration automatique de la boîte de saisie
			'admin'			=> true,
			// Limite de jours consecutifs pour l'affichage en jours séparés : -1 (illimité) | int
			'split'			=> 0,	
		);
		
		foreach( (array) self::getConfig() as $post_type => $args ) :		
			self::$PostTypes[$post_type] = wp_parse_args( $args, $defaults );
		endforeach;
		
		add_action( 'tify_taboox_register_node', array( $this, 'tify_taboox_register_node' ) );		
	}
	
	/* = ACTIONS = */
	/** == == **/
	final public function tify_taboox_register_node()
	{
		foreach( (array) self::$PostTypes as $post_type => $args ) :
			if( ! $args['admin'] )
				continue;
			
			tify_taboox_register_node( 
				$post_type, 
				array( 
					'id' 		=> 'tify_events',
					'title' 	=> __( 'Dates', 'tify' ), 
					'cb' 		=> "\\tiFy\\Plugins\\Events\\Taboox\\Post\\DateEditor\\Admin\\DateEditor" 
				) 
			);
		endforeach;
	}
		
	/* = CONTROLEURS = */
	/** == Récupération des types de posts == **/
	public static function GetPostTypes()
	{
		// Bypass
		if( ! is_array( self::$PostTypes ) )
			return array();
		
		return array_keys( self::$PostTypes );		
	}
	
	/** == Vérifie si le type de post est valide  == **/
	public static function IsPostType( $post_type )
	{
		return in_array( $post_type, self::GetPostTypes() );
	}
	
	/** == Récupération d'un attribut de type de post  == **/
	public static function GetPostTypeAttr( $post_type, $attr = null )
	{
		// Bypass
		if( ! self::IsPostType( $post_type ) )
			return;
		
		if( ! $attr ) :
			return self::$PostTypes[$post_type];
		elseif( isset( self::$PostTypes[$post_type][$attr] ) ) :
			return self::$PostTypes[$post_type][$attr];
		endif;
	}
}