<?php
namespace tiFy\Components\TinyMCE\ExternalPlugins\VisualBlocks;

class VisualBlocks extends \tiFy\App\Factory
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();

		// Déclaration du plugin
		\tiFy\Components\TinyMCE\TinyMCE::registerExternalPlugin( 'visualblocks', $this->Url .'/plugin.min.js' );
	}
}