<?php
namespace tiFy\Components\TinyMCE\ExternalPlugins\VisualBlocks;

use tiFy\Environment\App;

class VisualBlocks extends App
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();

		// Déclaration du plugin
		\tiFy\Components\TinyMCE\TinyMCE::registerExternalPlugin( 'visualblocks', $this->Url .'/plugin.min.js' );
	}
}