<?php
namespace tiFy\Core\Entity;

use tiFy\Core\Entity\AdminView\AdminView;
use tiFy\Core\Entity\Labels\Labels;

class Factory
{
	/* = ARGUMENTS = */
	// Identifiant de l'entité
	protected	$ID;
		
	// Interface d'administration liée à l'entité 
	protected	$AdminView;
	
	// Table de base de données liée à l'entité
	// Identifiant de la base de données
	protected 	$Db;
	
	// Intitulés liés à l'entité
	protected	$Labels;
		
	/* = CONSTRUCTEUR = */
	public function __construct( $entity_id, $args = array() )
	{
		// Définition de l'identifiant de l'entité
		$this->ID = $entity_id;	
	
		// Traitement des arguments de personnalisation
		$defaults = array(
			'AdminView'	=> array(),	
			'Labels' 	=> array(),
			'Db'		=> array()
		);
		$args = wp_parse_args( $args, $defaults );		
		
		if( ! empty( $args['AdminView'] ) )
			$this->AdminView = new AdminView( $this->ID, $args['AdminView'] );
		
		// Définition des arguments de base de données
		$DbID 			= ( isset( $args['Db']['id'] ) ) ? $args['Db']['id'] : $this->ID;			
		$DbClassName 	= implode( '_', array_map( 'ucfirst',  preg_split( '/_/', $DbID ) ) );
		$DbClassPath  	= 'tiFy\\Core\\Entity\\Db\\Table';
		$DbClassProxy 	= "\\{$DbClassPath}\\{$DbClassName}";
		/// Instanciation de la classe de base de données pour les tables non déclarées
		if( ! isset( \tiFy\Core\Entity\Entity::$TableAttrs[ $DbID ] ) ) :
			$DbAttrs = \tiFy\Core\Entity\Entity::$TableAttrs[ $DbID ] = $args['Db'];
			eval( "namespace {$DbClassPath}; class {$DbClassName} extends \\tiFy\\Core\\Entity\\Db\\Db{}" );
		else :
			$DbAttrs = \tiFy\Core\Entity\Entity::$TableAttrs[ $DbID ];
		endif;
			
		$this->Db = new $DbClassProxy( $DbID, $DbAttrs );
		
		// Définition des intitulés liés à l'entité
		$this->Labels = new Labels( $args['Labels'] );
	}
	
	/* = CONTROLEUR = */
	/** == Récupération de l'identifiant == **/
	public function getID()
	{
		return $this->ID;
	}
	
	/** == Récupération des attributs de l'interface d'administration == **/
	public function getAdminView( $attr = null, $view = null )
	{
		return $this->AdminView->get( $attr, $view );
	}
	
	/** == Récupération des intitulées == **/
	public function getLabel( $label = '' )
	{
		return $this->Labels->get( $label );
	}
	
	/** == Récupération de l'objet base de données == **/
	public function getDb()
	{
		return $this->Db;
	}	
}