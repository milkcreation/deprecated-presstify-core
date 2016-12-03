<?php
namespace tiFy\Core;

use tiFy\Environment\Core;

class Autoload extends Core
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		// Chargement des contrôleurs sans configuration
		new AjaxActions;
		new Control\Control;
		new Capabilities;		
		new Meta\Meta;		
		new Params;
		new ScriptLoader\ScriptLoader;
		
		add_action( 'after_setup_tify', array( $this, 'after_setup_tify' ), 0 );
	}
	
	final public function after_setup_tify()
	{
		// Chargement des contrôleurs configurable
		new	CustomType\CustomType;
		new Db\Db;
		new Templates\Templates;
		new Options\Options;		
		new Taboox\Taboox;
		new Upload\Upload;
		/// Formulaires
		new Forms\Forms;
	}
}