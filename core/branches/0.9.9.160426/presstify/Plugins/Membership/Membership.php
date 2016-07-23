<?php
/*
Plugin Name: Membership
Plugin URI: http://presstify.com/theme-manager/addons/premium/membership
Description: Gestion d'espace pro
Version: 1.20150227
Author: Milkcreation
Author URI: http://milkcreation.fr
*/
namespace tiFy\Plugins\Membership;

use tiFy\Environment\Plugin;

class Membership extends Plugin
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'admin_init',
		'tify_entity_register',
		'tify_options_register_node',
		'tify_form_register'
	);
	
	// Liste des rôles
	public static $Roles;
		
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		require_once $this->Dirname .'/Helpers.php';
		
		parent::__construct();

		// Définition des rôles
		self::$Roles = self::getConfig( 'roles' );
		
		// Définition des arguments du formulaire d'inscription
		if( $form = self::getConfig( 'form' ) ) :
			$form['ID'] = 'tify_membership_subscribe_form';
			$form['add-ons']['user'] = array( 
				'roles' 			=> (array) self::$Roles, 
				'edit_hookname'		=> 'acces-pro_page_tify_membership-edit'
			);
			self::setConfig( 'form', $form );
		endif;
		
		// Chargement des contrôleurs
		new Capabilities;
		new GeneralTemplate;		
	}

	/* = DECLENCHEMENT DES ACTIONS = */
	/** == Initialisation de l'interface d'administration == **/
	final public function admin_init()
	{		
		// Création des roles et des habilitations
		foreach( ( array ) self::$Roles as $role => $args ) :			
			// Création du rôle
			if( ! $_role =  get_role( $role ) )
				$_role = add_role( $role, $args['name'] );

			// Création des habilitations
			foreach( (array)  $args['capabilities'] as $cap => $grant ) :
				if( ! isset( $_role->capabilities[$cap] ) ||  ( $_role->capabilities[$cap] != $grant ) ) :
					$_role->add_cap( $cap, $grant );
				endif;
			endforeach;
		endforeach;
	}
		
	/* = = */
	final public function tify_entity_register()
	{
		tify_entity_register( 
			'tify_membership', 
			array(					
				'AdminView'	=> array(
					'Menu'	=> array(
						'icon_url'	=> 'dashicons-businessman',
						'position'	=> 71	
					),
					'ListTable'	=> array(
						'parent_slug'	=> 'tify_membership',
						'menu_slug'		=> 'tify_membership',
						'cb'			=> "\\tiFy\\Plugins\\Membership\\Admin\\ListUsers"
					),
					'EditForm'			=> array(
						'parent_slug'	=> 'tify_membership',
						'menu_slug'		=> 'tify_membership-edit',
						'cb'			=> "\\tiFy\\Plugins\\Membership\\Admin\\EditUser"
					)
				),	
				'Db' 		=> array( 
					'id' => 'users' 
				),
				'Labels'	=> array(
					'name'			=> __( 'Accès Pro.', 'tify' ),
					'menu_name'		=> __( 'Accès Pro.', 'tify' ),	
					'all_items'		=> __( 'Membres', 'tify' ),
					'add_item' 		=> __( 'Ajout d\'un membre', 'tify' ) 
				)
			) 
		);
	}	
	
	/* = = */
	final public function tify_options_register_node()
	{
		\tify_options_register_node(
			array(
				'id' 		=> 'tify_membership_page',
				'title' 	=> __( 'Accès Pro.', 'tify' ),
				'cb'		=> "\\tiFy\\Plugins\\Membership\\Taboox\\Option\\HookPage\\Admin\\HookPage"
			)
		);
	}

	/** == Déclaration du formulaire d'inscription == **/
	final public function tify_form_register()
	{
		if( empty( self::getConfig( 'form' ) ) ) 
			return;
		
		tify_form_register( self::getConfig( 'form' ) );
	}	
}