<?php
namespace tiFy\Components\TinyMCE\ExternalPlugins\Table;

class Table extends \tiFy\App\Factory
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();

		// Déclaration du plugin
		\tiFy\Components\TinyMCE\TinyMCE::registerExternalPlugin( 'table', $this->Url .'/plugin.min.js' );
	}
}