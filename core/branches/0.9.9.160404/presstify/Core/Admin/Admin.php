<?php
namespace tiFy\Core\Admin;

use tiFy\Environment\App;

class Admin extends App
{	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{		
		// Chargement des contrôleurs
		require_once $this->Dirname. '/plugins.php';
		//new tiFy_AdminPlugins( $master );
		
		// Actions et Filtres Wordpress
		add_action( 'admin_menu', array( $this, 'wp_admin_menu' ), 9 );
		add_filter( 'admin_footer_text', array( $this, 'wp_admin_footer_text' ) );
		add_action( 'admin_bar_menu', array( $this, 'wp_admin_bar_menu' ), 11 );	
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Menu d'administration == **/
	final public function wp_admin_menu()
	{
	  	add_menu_page( __( 'PresstiFy', 'tify' ) , __( 'PresstiFy', 'tify' ), 'manage_tify', 'tify', null, null, 66 );
	}
	
	/** == Personnalisation du pied de page de l'interface d'administration == **/
	final public function wp_admin_footer_text( $text = '' )
	{
		$admin_footer_text = $this->Params['config']['admin_footer_text'];
		if( ! empty( $admin_footer_text ) )
			$text = $admin_footer_text;
		
		return $text;
	}
	
	/** == Personnalisation du logo PressTiFy de la barre d'administration == **/
	final public function wp_admin_bar_menu( $wp_admin_bar )
	{
		if( ! $admin_bar_menu_logo = $this->Params['config'][ 'admin_bar_menu_logo'] )
			return;
	
		$wp_admin_bar->remove_menu( 'wp-logo' );

		foreach( (array) $admin_bar_menu_logo as $node )
			if( ! empty( $node['group'] ) )
				$wp_admin_bar->add_group( $node );
			else
				$wp_admin_bar->add_menu( $node );	
	}
}