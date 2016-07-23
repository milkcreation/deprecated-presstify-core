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
		
		// Chargement des contrôleurs
		new Admin\Admin;
		new AjaxActions;
		new Control\Control;
		require_once __DIR__ .'/Annotations.php';		
		new Capabilities;
		new Entity\Entity;
		new Meta\Meta;
		new Options\Options;
		new Params;
		new ScriptLoader\ScriptLoader;
		new Taboox\Taboox;		
		/// Formulaires
		require_once __DIR__ .'/Forms/Forms.php';
	}
}