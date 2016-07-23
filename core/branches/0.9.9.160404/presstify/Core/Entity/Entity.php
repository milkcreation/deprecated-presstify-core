<?php
namespace tiFy\Core\Entity;

use tiFy\Environment\Core;

class Entity extends Core
{
	/* ARGUMENTS */
	// Liste des entités déclarées
	public static $Entities		= array();
	
	// Liste des attributs de tables de base de données déclarées 
	public static $TableAttrs	= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{		
		parent::__construct();
		
		add_action( 'after_setup_tify', array( $this, 'after_setup_tify' ), 11 );
	}
	
	/* = Déclaration et configuration des entités = */
	public function after_setup_tify()
	{
		foreach( (array) $this->Params['schema'] as $id => $args ) :
			self::register( $id, $args );
		endforeach;	
		
		do_action( 'tify_entity_register' );
	}
	
	/* = Déclaration d'une entité = */	
	public static function register( $id, $args )
	{
		self::$Entities[$id] = new \tiFy\Core\Entity\Factory( $id, $args );
	}
	
	/* = = */
	public static function get( $id )
	{
		if( isset( self::$Entities[$id] ) )
			return self::$Entities[$id];
	}
}