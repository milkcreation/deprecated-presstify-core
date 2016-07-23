<?php
namespace tiFy\Core;

use tiFy\Environment\Core;

class Autoload extends Core
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		// Déclaration des chemins vers la racine de PresstiFy
		$this->AbsDir = \tiFy\tiFy::$AbsDir;
		$this->AbsUrl = \tiFy\tiFy::$AbsUrl;		
		
		add_action( 'after_setup_tify', array( $this, 'after_setup_tify' ), 0 );
				
		// Chargement des contrôleurs sans configuration
		new Admin\Admin;
		new AjaxActions;
		new Control\Control;
		require_once __DIR__ .'/Annotations.php';	
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
		new View\View;
		new Options\Options;		
		new Taboox\Taboox;
		new Upload\Upload;
		/// Formulaires
		require_once __DIR__ .'/Forms/Forms.php';
		new \tiFy_Forms;
	}
}