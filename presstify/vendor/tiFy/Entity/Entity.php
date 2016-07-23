<?php
namespace tiFy\Entity;

use tiFy\Entity\Db\Db;

class Entity
{
	/* = ARGUMENTS = */
	// Identifiant
	protected	$ID;
	
	// Intitulés
	protected	$Labels;
	
	// Table de base de données
	// Nom de la classe de table de base de données
	protected	$DbClassName;
	
	// Chemin d'accès à la classe de table de base de données
	protected	$DbClassPath;
	
	// Mandat d'accès à la table de base de données
	protected	$DbClassProxy;
	
	// Attributs de la table de base de données
	protected $DbAttrs;
	
	// Liste des attributs accessibles
	protected	$AccessAttrs		= array( 'DbClassName', 'DbClassPath', 'DbClassProxy', 'DbAttrs' );
	
	/* = CONSTRUCTEUR = */
	public function __construct( $entity_id, $args = array() )
	{
		$this->ID = $entity_id;	
			
		$defaults = array( 
			'labels' 	=> array(),
			'Db'		=> array()
		);
		$args = wp_parse_args( $args, $defaults );
		
		// Définition des intitulés de l'entité
		$this->Labels 	= new Labels\Labels( $args['labels'] );
		
		// Définition de la base de données
		$this->DbClassName 	= implode( '_', array_map( 'ucfirst',  preg_split( '/_/', $this->ID ) ) );
		$this->DbClassPath  = 'tiFy\\Entity\\Db\\Table';
		$this->DbClassProxy = '\\'. $this->DbClassPath .'\\'. $this->DbClassName;
		$this->DbAttrs		= $args['Db'];

		eval( "namespace {$this->DbClassPath}; class {$this->DbClassName} extends \\tiFy\\Entity\\Db\\Db{};" );
		//new $this->DbClassProxy( $this->ID );
	}
	
	/* = RECUPERATION DE DONNÉES = */
	/** == Récupération des données accessible == **/
	public function __get( $name ) 
	{
		if ( in_array( $name, $this->AccessAttrs ) ) {
			return $this->{$name};
		}
	}

	/* = CONTROLEUR = */
	/** == Récupération des intitulées == **/
	public function getLabel( $label = '' )
	{
		return $this->Labels->get( $label );
	}
}