<?php
namespace Theme;

use \tiFy\Environment\App;

class Autoload extends App
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
	
		new ScriptLoader;
	}
}