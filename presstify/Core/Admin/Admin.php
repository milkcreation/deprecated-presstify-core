<?php
namespace tiFy\Core\Admin;

use tiFy\Environment\App;

class Admin extends App
{	
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'admin_menu'
	);
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'admin_menu'	=> 9
	);
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{		
		parent::__construct();
		// Chargement des contrôleurs
		//require_once $this->Dirname. '/plugins.php';
		//new tiFy_AdminPlugins( $master );			
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Menu d'administration == **/
	final public function admin_menu()
	{
	  	add_menu_page( __( 'PresstiFy', 'tify' ) , __( 'PresstiFy', 'tify' ), 'manage_options', 'tify', null, null, 66 );
	}	
}