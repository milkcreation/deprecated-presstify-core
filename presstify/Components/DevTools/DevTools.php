<?php
namespace tiFy\Components\DevTools;

use tiFy\Environment\Component;

/** @Autoload */
class DevTools extends Component
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();

		new Tools\RestrictedAccess\RestrictedAccess;
		new Tools\ConfigConvertor\ConfigConvertor;
	}
}