<?php
namespace tiFy\Core\Front;

use tiFy\Environment\App;
use tiFy\Core\Db\Db;
use tiFy\Core\Labels\Labels;

final class Factory extends App
{
	/* = ARGUMENTS = */
	// DECLENCHEURS
	/// Liste des actions à déclencher
	protected $CallActions				= array(
		'init',
		'template_redirect',
		'wp_enqueue_scripts'		
	); 
	
	// PARAMETRES GENERAUX
	/// Identifiant
	private		$FactoryID				= null;
						
	// Liste des vues prédéfinies			
	private		$PredefinedView 		= array( 
		'AjaxListTable'
	);
	
	/// Classe de rappel des intitulés
	private		$LabelClass 				= null;
	
	/// Classe de rappel des données en base
	private		$DbClass 					= null;	
	
	// PARAMETRES DES MODELES ACTIFS
	/// Liste de nom des modèles actifs
	private		$ModelNames					= array();
	
	/// Attributs des modèles actifs 
	private		$ModelAttrs					= null;	
	
	/// Classe de rappel des modèles actifs
	private		$ModelClasses				= array();	
		
	// VUE COURANTE
	/// Nom du modèle principale de l'écran courant
	private 	$CurrentModelName			= null;
	
	/// Classe de rappel du modèle principale de l'écran courant
	private 	$CurrentModelClass			= null;
		
	/* = CONSTRUCTEUR = */
	public function __construct( $id, $args = array() )
	{
		parent::__construct();
		
		// Définition de l'identifiant de l'entité
		$this->FactoryID = $id;
		
		// Traitement des vues actives	
		foreach( (array) $args as $name => $attrs ) :
			array_push( $this->ModelNames, $name );
			
			$this->ModelAttrs[$name] = new \stdClass;
			
			// Définition de la classe de rappel
			if( isset( $attrs['cb'] ) ) :
				$this->ModelAttrs[$name]->cb 	= $attrs['cb'];
			elseif( in_array( $name, $this->PredefinedView ) ) :
				$this->ModelAttrs[$name]->cb 	= "\\tiFy\\Core\\Front\\Model\\{$name}\\{$name}";	
			else :
				$this->ModelAttrs[$name]->cb 	= null;
			endif;
		
			// Traitement des arguments de menu
			$defaults = array( 
				'route'			=> $this->FactoryID .'/'. $name,
				'function' 		=> array( $this, 'Render' )
			);			
			$args = wp_parse_args( $attrs, $defaults );
			
			foreach( $args as $k => $v ) :
				$this->ModelAttrs[$name]->{$k} = $v;
			endforeach;
		endforeach;
		
	}
	
	/* = DECLENCHEURS = */
	/** == Initialisation globale == **/
	final public function init()
	{		
		// Instanciation des contrôleurs
		foreach( $this->ModelNames as $name ) :
			// Bypass
			if( ! isset( $this->ModelAttrs[$name]->cb ) || ! class_exists( $this->ModelAttrs[$name]->cb ) )
				continue;

			// Instanciation de la classe
			$this->ModelClasses[$name] 			= new $this->ModelAttrs[$name]->cb;
			$this->ModelClasses[$name]->View 	= $this;
			$this->ModelClasses[$name]->Name	= $name;
			
			// Déclenchement de l'action dans les classes de rappel d'environnement
			if( method_exists( $this->ModelClasses[$name], '_init' ) ) :
				call_user_func( array( $this->ModelClasses[$name], '_init' ) );
			endif;
			if( method_exists( $this->ModelClasses[$name], 'init' ) ) :
				call_user_func( array( $this->ModelClasses[$name], 'init' ) );
			endif;
		endforeach;
	}
	
	/** == == **/
	public function template_redirect()
	{
		$matches = preg_split( '/\?.*/', $_SERVER['REQUEST_URI'], 2 );
		$route = current( $matches );

		/// Chargement du template
		foreach( $this->ModelAttrs as $name => $attrs ) :
			if( preg_match( '/^\/'. preg_quote( $attrs->route, '/' ) .'\/?$/', $route ) ) :
				$this->CurrentModelName 	= $name;	
				$this->CurrentModelClass 	= $this->ModelClasses[$name];
				break;
			endif;
		endforeach;
	
		// Bypass		
		if( ! $this->hasModel() )
			return;
		
		// Déclenchement de l'action dans la classe de rappel d'environnement			
		if( method_exists( $this->CurrentModelClass, '_current_screen' ) ) :
			call_user_func( array( $this->CurrentModelClass, '_current_screen' ) );
		endif;
		if( method_exists( $this->CurrentModelClass, 'current_screen' ) ) :
			call_user_func( array( $this->CurrentModelClass, 'current_screen' ) );
		endif;	
			
		$this->Render();	
			
		exit;
	}
	
	/** == Mise en file des scripts de l'interface d'administration (privée) == **/
	final public function wp_enqueue_scripts()
	{			
		// Déclaration des scripts
		/// AjaxListTable
		wp_register_style( 'tiFyCoreAdminAjaxListTable', $this->Url .'/Model/AjaxListTable/AjaxListTable.css', array( 'datatables' ), '160506' );						
		wp_register_script( 'tiFyCoreAdminAjaxListTable', $this->Url .'/Model/AjaxListTable/AjaxListTable.js', array( 'datatables' ), '160506', true );
		
		/// EditForm
		wp_register_style( 'tiFyCoreAdminEditForm', $this->Url .'/Model/EditForm/EditForm.css', array(), 151211 );
		
		/// Import
		wp_register_style( 'tiFyCoreAdminImport', $this->Url .'/Model/Import/Import.css', array( 'tify_control-progress' ), 150607 );
		wp_register_script( 'tiFyCoreAdminImport', $this->Url .'/Model/Import/Import.js', array( 'jquery', 'tify_control-progress', 'tify-fixed_submitdiv' ), 150607 );	
		
		/// ListTable
		wp_register_style( 'tiFyCoreAdminListTable', $this->Url .'/Model/ListTable/ListTable.css', array(), 160617 );
		
		/// ListUser
		wp_register_style( 'tiFyCoreAdminListUser', $this->Url .'/Model/ListUser/ListUser.css', array( 'tiFyCoreAdminListTable' ), 160609 );
				
		switch( $this->CurrentModelName ) :
			case 'AjaxListTable' :	
				wp_enqueue_style( 'tiFyCoreAdminAjaxListTable' );						
				wp_enqueue_script( 'tiFyCoreAdminAjaxListTable' );
				break;
			case 'EditForm' :
				wp_enqueue_style( 'tiFyCoreAdminEditForm' );
				break;
			case 'Import' :
				wp_enqueue_style( 'tiFyCoreAdminImport' );
				wp_enqueue_script( 'tiFyCoreAdminImport' );	
				break;
			case 'ListTable' :
				wp_enqueue_style( 'tiFyCoreAdminListTable' );
				break;
			case 'ListUser' :
				wp_enqueue_style( 'tiFyCoreAdminListUser' );
				break;
		endswitch;
			
		// Déclenchement de l'action dans la classe de rappel d'environnement	
		if( method_exists( $this->CurrentModelClass, '_wp_enqueue_scripts' ) ) :
			call_user_func( array( $this->CurrentModelClass, '_wp_enqueue_scripts' ) );
		endif;
		if( method_exists( $this->CurrentModelClass, 'wp_enqueue_scripts' ) ) :
			call_user_func( array( $this->CurrentModelClass, 'wp_enqueue_scripts' ) );
		endif;
	}
	
	/* = CONTRÔLEURS = */
	/** == Récupération de l'identifiant == **/
	public function getID()
	{
		return $this->FactoryID;
	}
	
	/** == Récupération des modèles déclarés == **/
	public function getModelNames()
	{
		return $this->ModelNames;
	}
	
	/** == Vérifie l'existance d'un modèle == **/
	public function hasModel( $name = null )
	{
		if( ! $name )
			$name = $this->CurrentModelName;
		
		if( ! empty( $name ) )	
			return in_array( $name, $this->ModelNames );
	}
	
	/** == Récupération d'une classe de modèle == **/
	public function getModelClass( $name = null )
	{
		if( ! $name )
			$name = $this->CurrentModelName;
		
		if( $this->hasModel( $name ) )
			return $this->ModelClasses[$name];
	}
	
	/** == Récupération des attributs de configuration d'un modèle == **/
	public function getModelAttrs( $attr = null, $name = null )
	{	
		if( ! $name )
			$name = $this->CurrentModelName;

		if( ! $this->hasModel( $name ) )
			return;
		
		if( ! $attr ) :	
			return $this->ModelAttrs[$name];
		elseif( isset( $this->ModelAttrs[$name]->{$attr} ) ) :
			return $this->ModelAttrs[$name]->{$attr};
		endif;
	}
	
	/** == Définition d'un attribut de configuration de modèle == **/
	public function setModelAttrs( $attr, $value = '', $name = null )
	{	
		if( ! $name )
			$name = $this->CurrentModelName;
		
		if( ! $this->hasModel( $name ) )
			return;
		
		$this->ModelAttrs[$name]->{$attr} = $value;
	}
	
	/** == Récupération des intitulées == **/
	public function getLabel( $label = '' )
	{
		if( ! is_null( $this->LabelClass ) )
			return $this->LabelClass->Get( $label );
		
		if( ! $this->LabelClass = Labels::Get( $this->FactoryID ) )
			$this->LabelClass = Labels::Register( $this->FactoryID );
		
		return $this->LabelClass->Get( $label );
	}

	/** == Récupération de la base de données == **/
	public function getDb()
	{
		if( ! is_null( $this->DbClass ) )
			return $this->DbClass;
		
		if( ! $this->DbClass = Db::Get( $this->FactoryID ) ) :
			if( ( in_array( 'ListUser', $this->ModelNames ) || in_array( 'EditUser', $this->ModelNames ) || in_array( 'AjaxListTable', $this->ModelNames ) ) ) :
				$this->DbClass = Db::Get( 'users' );
			else :
				$this->DbClass = Db::Get( 'posts' );
			endif;
		endif;

		return 	$this->DbClass;
	}
	
	/* = AFFICHAGE = */
	/** == Page de l'interface d'administration == **/
	final public function Render( $name = null )
	{
		if( method_exists( $this->CurrentModelClass, 'Render' ) )
			return call_user_func( array( $this->CurrentModelClass, 'Render' ) ); 		
	}
}