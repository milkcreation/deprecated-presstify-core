<?php
namespace tiFy\Core;

use tiFy\Environment\Core;

class Autoload extends Core
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		add_action( 'after_setup_tify', array( $this, 'after_setup_tify' ), 0 );
				
		// Chargement des contrôleurs sans configuration
		new AjaxActions;
		new Control\Control;
		new Capabilities;		
		new Meta\Meta;		
		new Params;
		new ScriptLoader\ScriptLoader;		
	}
	
	final public function after_setup_tify()
	{
		// Chargement des contrôleurs configurable
		new	CustomType\CustomType;
		new Db\Db;
		new Admin\Admin;
		new Options\Options;		
		new Taboox\Taboox;
		new Upload\Upload;
		/// Formulaires
		require_once __DIR__ .'/Forms/Forms.php';
		new \tiFy_Forms;
	}
}