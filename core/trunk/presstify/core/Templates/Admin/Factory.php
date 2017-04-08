<?php
namespace tiFy\Core\Templates\Admin;

class Factory extends \tiFy\Core\Templates\Factory
{
	/* = ARGUMENTS = */
	// DECLENCHEURS
	/// Liste des actions à déclencher
	protected $CallActions					= array(
		'init',
		'admin_init',
		'current_screen'	
	); 
	
	// Contexte d'execution
	protected static $Context				= 'admin';
	
	// Liste des modèles prédéfinis
	protected static $Models				= array(
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
	
	/* = DECLENCHEURS = */
	/** == Initialisation globale == **/
	final public function init()
	{
		// Bypass
		if( ! $this->getAttr( 'cb' ) && ! $this->getAttr( 'model' ) )
			return;
			
		// Instanciation de la classe
		if( ! $this->getAttr( 'cb' ) ) : 
			$model = $this->getAttr( 'model' );		
			$className = "\\tiFy\\Core\\Templates\\". ucfirst( $this->getContext() ) ."\\Model\\{$model}\\{$model}";
		else :
			$className = self::getOverride( $this->getAttr( 'cb' ) );
		endif;

		if( ! class_exists( $className ) )
			return;
					
		$this->TemplateCb = new $className( $this->getAttr( 'args', null ) );

		// Création des methodes dynamiques
		$factory = $this;
		$this->TemplateCb->template = function() use( $factory ){ return $factory; };
		$this->TemplateCb->db = function() use( $factory ){ return $factory->db(); };
		$this->TemplateCb->label = function( $label = '' ) use( $factory ){ if( func_num_args() ) return $factory->getLabel( func_get_arg(0) ); };		
		$this->TemplateCb->getConfig = function( $attr, $default = '' ) use( $factory ){ if( func_num_args() ) return call_user_func_array( array( $factory, 'getAttr' ), func_get_args() ); };	
		
		// Identifiants de menu
		$menu_slug = $this->getID(); $parent_slug = null;
		if( $admin_menu = $this->getAttr( 'admin_menu' ) ) :
			if( ! empty( $admin_menu['menu_slug'] ) )
				$menu_slug = $admin_menu['menu_slug'];
			if( ! empty( $admin_menu['parent_slug'] )  )
			$parent_slug = $admin_menu['parent_slug'];	
		endif;
		$this->setAttr( '_menu_slug', $menu_slug );
		$this->setAttr( '_parent_slug', $parent_slug );
		
		// Déclenchement de l'action dans la classes du template
		if( method_exists( $this->TemplateCb, '_init' ) ) :
			call_user_func( array( $this->TemplateCb, '_init' ) );
		endif;
		if( method_exists( $this->TemplateCb, 'init' ) ) :
			call_user_func( array( $this->TemplateCb, 'init' ) );
		endif;
	}
	
	/** == Initialisation de l'interface d'administration (privée) == **/
	final public function admin_init()
	{
		// Bypass
		if( ! $this->TemplateCb )
			return;
					
		// Définition des attributs privés de la vue	
		$this->setAttr( '_hookname', \get_plugin_page_hookname( $this->getAttr( '_menu_slug' ), $this->getAttr( '_parent_slug' ) ) );
		$this->setAttr( '_menu_page_url', \menu_page_url( $this->getAttr( '_menu_slug' ), false ) );
		
		if( ! $this->getAttr( 'base_url' ) )
			$this->setAttr( 'base_url', \esc_attr( $this->getAttr( '_menu_page_url' ) ) );
			
		// Déclenchement de l'action dans les classes de rappel d'environnement
		if( method_exists( $this->TemplateCb, '_admin_init' ) ) :
			call_user_func( array( $this->TemplateCb, '_admin_init' ) );
		endif;
		if( method_exists( $this->TemplateCb, 'admin_init' ) ) :
			call_user_func( array( $this->TemplateCb, 'admin_init' ) );
		endif;
	}
	
	/** == Chargement de l'écran courant (privée) == **/
	final public function current_screen( $current_screen )
	{
		// Bypass
		if( ! $this->TemplateCb )
			return;
		if( $current_screen->id !== $this->getAttr( '_hookname', '' ) )
			return;
			
		// Mise en file des scripts de l'ecran courant
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'admin_print_footer_scripts', array( $this, 'admin_print_footer_scripts' ) );
		
		// Déclenchement de l'action dans la classe de rappel d'environnement			
		if( method_exists( $this->TemplateCb, '_current_screen' ) ) :
			call_user_func( array( $this->TemplateCb, '_current_screen' ), $current_screen );
		endif;
		if( method_exists( $this->TemplateCb, 'current_screen' ) ) :
			call_user_func( array( $this->TemplateCb, 'current_screen' ), $current_screen );
		endif;
	}
	
	/** == Mise en file des scripts de l'interface d'administration (privée) == **/
	final public function admin_enqueue_scripts()
	{				
		// Déclaration des scripts
		/// AjaxListTable
		wp_register_style( 'tiFyCoreAdminAjaxListTable', self::getUrl() .'/Model/AjaxListTable/AjaxListTable.css', array( 'datatables' ), '160506' );						
		wp_register_script( 'tiFyCoreAdminAjaxListTable', self::getUrl() .'/Model/AjaxListTable/AjaxListTable.js', array( 'datatables' ), '160506', true );
		
		/// EditForm
		wp_register_style( 'tiFyCoreAdminEditForm', self::getUrl() .'/Model/EditForm/EditForm.css', array(), 151211 );
		
		/// Import
		wp_register_style( 'tiFyCoreAdminImport', self::getUrl() .'/Model/Import/Import.css', array( 'tify_control-progress' ), 150607 );
		wp_register_script( 'tiFyCoreAdminImport', self::getUrl() .'/Model/Import/Import.js', array( 'jquery', 'tify_control-progress', 'tify-fixed_submitdiv' ), 150607 );	
		
		/// ListTable
		wp_register_style( 'tiFyCoreAdminListTable', self::getUrl().'/Model/ListTable/ListTable.css', array(), 160617 );
		
		/// ListUser
		wp_register_style( 'tiFyCoreAdminListUser', self::getUrl() .'/Model/ListUser/ListUser.css', array( 'tiFyCoreAdminListTable' ), 160609 );
		
		switch( $this->ModelName ) :
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
		if( method_exists( $this->TemplateCb, '_admin_enqueue_scripts' ) ) :
			call_user_func( array( $this->TemplateCb, '_admin_enqueue_scripts' ) );
		endif;
		if( method_exists( $this->TemplateCb, 'admin_enqueue_scripts' ) ) :
			call_user_func( array( $this->TemplateCb, 'admin_enqueue_scripts' ) );
		endif;
	}
	
	/** == Ecriture des scripts du pried de page de l'interface d'administration == **/
	final public function admin_print_footer_scripts()
	{
	   // Déclenchement de l'action dans la classe de rappel d'environnement	
		if( method_exists( $this->TemplateCb, '_admin_print_footer_scripts' ) ) :
			call_user_func( array( $this->TemplateCb, '_admin_print_footer_scripts' ) );
		endif;
		if( method_exists( $this->TemplateCb, 'admin_print_footer_scripts' ) ) :
			call_user_func( array( $this->TemplateCb, 'admin_print_footer_scripts' ) );
		endif;  
	}
}