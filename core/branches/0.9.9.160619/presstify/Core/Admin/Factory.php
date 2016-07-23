<?php
namespace tiFy\Core\Admin;

use tiFy\Environment\App;

final class Factory extends App
{
	/* = ARGUMENTS = */
	// DECLENCHEURS
	/// Liste des actions à déclencher
	protected $CallActions				= array(
		'init',
		'admin_init',
		'current_screen'	
	); 
	
	// PARAMETRES GENERAUX
	/// Identifiant
	private		$FactoryID				= null;
						
	// Liste des vues prédéfinies			
	private		$PredefinedView 		= array( 
		'AjaxExport', 
		'AjaxListTable', 
		'EditForm', 
		'EditUser', 
		'Import', 
		'ListTable', 
		'ListUser',
		// Variation	
		'TabooxEditUser',
		'TabooxForm'
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
				$this->ModelAttrs[$name]->cb 	= "\\tiFy\\Core\\Admin\\Model\\{$name}\\{$name}";	
			else :
				$this->ModelAttrs[$name]->cb 	= null;
			endif;
						
			// Traitement des arguments de menu
			$defaults = array( 
				'page_title' 	=> $this->FactoryID, 
				'menu_title' 	=> '', 
				'capability'	=> 'manage_options', 
				'icon_url' 		=> null, 
				'position' 		=> 99, 
				'function' 		=> array( $this, 'Render' ), 
				'hide_menu' 	=> false
			);			
			$args = wp_parse_args( $attrs, $defaults );
			
			foreach( $args as $k => $v ) :
				$this->ModelAttrs[$name]->{$k} = $v;
			endforeach;
				
			$this->ModelAttrs[$name]->menu_slug 	= 	! empty( $attrs['menu_slug'] ) 		? $attrs['menu_slug'] 		: $this->FactoryID .'_'. $name;
			$this->ModelAttrs[$name]->parent_slug 	= 	! empty( $attrs['parent_slug'] ) 	? $attrs['parent_slug'] 	: null;
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
			$this->ModelClasses[$name] = new $this->ModelAttrs[$name]->cb( $this );
					
			// Déclenchement de l'action dans les classes de rappel d'environnement
			if( method_exists( $this->ModelClasses[$name], '_init' ) ) :
				call_user_func( array( $this->ModelClasses[$name], '_init' ) );
			endif;
			if( method_exists( $this->ModelClasses[$name], 'init' ) ) :
				call_user_func( array( $this->ModelClasses[$name], 'init' ) );
			endif;
		endforeach;
	}
	
	/** == Initialisation de l'interface d'administration (privée) == **/
	final public function admin_init()
	{
		// Instanciation des contrôleurs
		foreach( $this->ModelNames as $name ) :			
			// Bypass
			if( ! isset( $this->ModelAttrs[$name]->cb ) || ! class_exists( $this->ModelAttrs[$name]->cb ) )
				continue;
			
			// Définition des attributs de la vue	
			$this->ModelAttrs[$name]->hookname 		= \get_plugin_page_hookname( $this->ModelAttrs[$name]->menu_slug, $this->ModelAttrs[$name]->parent_slug );
			$this->ModelAttrs[$name]->menu_page_url = \menu_page_url( $this->ModelAttrs[$name]->menu_slug, false );
			$this->ModelAttrs[$name]->base_url 		= \esc_attr( $this->ModelAttrs[$name]->menu_page_url );
			
			// Déclenchement de l'action dans les classes de rappel d'environnement
			if( method_exists( $this->ModelClasses[$name], '_admin_init' ) ) :
				call_user_func( array( $this->ModelClasses[$name], '_admin_init' ) );
			endif;
			if( method_exists( $this->ModelClasses[$name], 'admin_init' ) ) :
				call_user_func( array( $this->ModelClasses[$name], 'admin_init' ) );
			endif;
		endforeach;
	}
	
	/** == Chargement de l'écran courant (privée) == **/
	final public function current_screen( $current_screen )
	{
		// Définition de la vue de l'écran courant
		foreach( $this->ModelNames as $name ) :
			if( ! isset( $this->ModelAttrs[$name]->hookname ) || ( $this->ModelAttrs[$name]->hookname !== $current_screen->id ) )
				continue;
			
			$this->CurrentModelName 	= $name;	
			$this->CurrentModelClass 	= $this->ModelClasses[$name];
			break;
		endforeach;
		
		// Bypass		
		if( ! $this->hasModel() )
			return;

		// Mise en file des scripts de l'ecran courant
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Déclenchement de l'action dans la classe de rappel d'environnement			
		if( method_exists( $this->CurrentModelClass, '_current_screen' ) ) :
			call_user_func( array( $this->CurrentModelClass, '_current_screen' ), $current_screen );
		endif;
		if( method_exists( $this->CurrentModelClass, 'current_screen' ) ) :
			call_user_func( array( $this->CurrentModelClass, 'current_screen' ), $current_screen );
		endif;
	}
	
	/** == Mise en file des scripts de l'interface d'administration (privée) == **/
	final public function admin_enqueue_scripts()
	{			
		// Déclaration des scripts
		/// AjaxListTable
		wp_register_style( 'tiFy_Core_Admin_AjaxListTable', $this->Url .'/Model/AjaxListTable/AjaxListTable.css', array( 'datatables' ), '160506' );						
		wp_register_script( 'tiFy_Core_Admin_AjaxListTable', $this->Url .'/Model/AjaxListTable/AjaxListTable.js', array( 'datatables' ), '160506', true );
		/// EditForm
		wp_register_style( 'tiFy_Core_Admin_EditForm', $this->Url .'/Model/EditForm/EditForm.css', array(), 151211 );
		/// ListTable
		wp_register_style( 'tiFy_Core_Admin_ListTable', $this->Url .'/Model/ListTable/ListTable.css', array(), 160617 );
		/// ListUser
		wp_register_style( 'tiFy_Core_Admin_ListUser', $this->Url .'/Model/ListUser/ListUser.css', array( 'tiFy_Core_Admin_ListTable' ), 160609 );
				
		switch( $this->CurrentModelName ) :
			case 'AjaxListTable' :	
				wp_enqueue_style( 'tiFy_Core_Admin_AjaxListTable' );						
				wp_enqueue_script( 'tiFy_Core_Admin_AjaxListTable' );
				break;
			case 'EditForm' :
				wp_enqueue_style( 'tiFy_Core_Admin_EditForm' );
				break;
			case 'ListTable' :
				wp_enqueue_style( 'tiFy_Core_Admin_ListTable' );
				break;
			case 'ListUser' :
				wp_enqueue_style( 'tiFy_Core_Admin_ListUser' );
				break;
		endswitch;
			
		// Déclenchement de l'action dans la classe de rappel d'environnement	
		if( method_exists( $this->CurrentModelClass, '_admin_enqueue_scripts' ) ) :
			call_user_func( array( $this->CurrentModelClass, '_admin_enqueue_scripts' ) );
		endif;
		if( method_exists( $this->CurrentModelClass, 'admin_enqueue_scripts' ) ) :
			call_user_func( array( $this->CurrentModelClass, 'admin_enqueue_scripts' ) );
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
	
	/** == Récupération des intitulées == **/
	public function getLabel( $label = '' )
	{
		if( ! is_null( $this->LabelClass ) )
			return $this->LabelClass->Get( $label );
		
		if( ! $this->LabelClass = \tiFy\Core\Labels\Labels::Get( $this->FactoryID ) )
			$this->LabelClass = \tiFy\Core\Labels\Labels::Register( $this->FactoryID );
		
		return $this->LabelClass->Get( $label );
	}

	/** == Récupération de la base de données == **/
	public function getDb()
	{
		if( ! is_null( $this->DbClass ) )
			return $this->DbClass;
		
		if( ! $this->DbClass = \tiFy\Core\Db\Db::Get( $this->FactoryID ) ) :
			if( ( in_array( 'ListUser', $this->ModelNames ) || in_array( 'EditUser', $this->ModelNames ) ) ) :
				$this->DbClass = \tiFy\Core\Db\Db::Get( 'users' );
			else :
				$this->DbClass = \tiFy\Core\Db\Db::Get( 'posts' );
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