<?php
namespace tiFy\Components\TinyMCE\ExternalPlugins\Table;

use tiFy\Environment\App;

class Table extends App
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();

		// Déclaration du plugin
		\tiFy\Components\TinyMCE\TinyMCE::registerExternalPlugin( 'table', $this->Url .'/plugin.min.js' );
	}
}