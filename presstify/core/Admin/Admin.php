<?php
namespace tiFy\Core\Admin;

use tiFy\Environment\Core;

class Admin extends Core
{
	/* ARGUMENTS */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'after_setup_tify',	
		'admin_menu'	
	);
	
	// Liste des vues déclarées
	private static $Factories		= array();
	
	public function __construct()
	{
		parent::__construct();
		
		foreach( (array) self::getConfig() as $id => $args ) :
			self::Register( $id, $args );
		endforeach;	
	}
	
	/* = DECLENCHEUR = */
	final public function after_setup_tify()
	{		
		do_action( 'tify_admin_register' );
	}
	
	/** == Menu d'administration == **/
	final public function admin_menu()
	{
		$menus = array(); $submenus = array();		
	
		foreach( (array) self::$Factories as $id => $Class ) :
			foreach( (array) $Class->getModelNames() as $name ) :				
				if( ! $attrs = (array) $Class->getModelAttrs( null, $name ) )
					continue;

				extract( $attrs );
				if( ! $menu_title ) :
					switch( $name ) :
						default :
							$menu_title = $Class->getLabel( 'menu_name' );
							break;
						case 'EditForm' :
						case 'EditUser' :
						case 'TabooxEditUser' :
							$menu_title = $Class->getLabel( 'add_new' );
							break;
						case 'Import' :
							$menu_title = $Class->getLabel( 'import_items' );
							break;
						case 'ListTable' :
						case 'ListUser' :
							$menu_title = $Class->getLabel( 'all_items' );
							break;
						case 'Menu' :
							$menu_title = $Class->getLabel( 'menu_name' );
							break;
					endswitch;				
				endif;
				if( ! $parent_slug ) :
					$menus[$menu_slug] = array(
						'page_title'	=> 	$page_title,
						'menu_title'	=> 	$menu_title,
						'capability'	=> 	$capability,
						'menu_slug'		=> 	$menu_slug,
						'function'		=> 	$function,
						'icon_url'		=>  $icon_url,
						'position'		=> 	$position
					);
				else :
					if( $hide_menu )
						continue;
					$submenus[$parent_slug][] = array(
						'parent_slug'	=> 	$parent_slug,	
						'page_title'	=> 	$page_title,
						'menu_title'	=> 	$menu_title,
						'capability'	=> 	$capability,
						'menu_slug'		=> 	$menu_slug,
						'function'		=> 	$function,
						'position'		=> 	$position	
					);
				endif;				
			endforeach;
		endforeach;
			
		// Déclaration des menus
		foreach( (array) $menus as $menu_slug => $menu ) :			
			add_menu_page( $menu['page_title'], $menu['menu_title'], $menu['capability'], $menu_slug, $menu['function'], $menu['icon_url'], $menu['position'] );
		endforeach;
		
		// Déclaration des sous-menus
		foreach( (array) $submenus as $parent_slug =>  $_submenus ) :
			foreach( $_submenus as $k => $v ) 
				$order[$k] = $v['position'];
			@array_multisort( $order, $_submenus );
			
			foreach( $_submenus as $position => $submenu ) :
				add_submenu_page( $parent_slug, $submenu['page_title'], $submenu['menu_title'], $submenu['capability'], $submenu['menu_slug'], $submenu['function'] );
			endforeach;
		endforeach;
	}
	
	/* = CONTRÔLEURS = */
	/* = Déclaration d'une entité = */	
	public static function Register( $id, $args )
	{		
		return self::$Factories[$id] = new Factory( $id, $args );
	}
	
	/* = Récupération d'une classe = */
	public static function Get( $id )
	{
		if( isset( self::$Factories[$id] ) )
			return self::$Factories[$id];
	}
}