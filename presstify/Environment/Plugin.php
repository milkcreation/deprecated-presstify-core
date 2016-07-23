<?php
namespace tiFy\Environment;

use \tiFy\Environment\Config;

abstract class Plugin extends Config
{
	// Namespace
	protected $Namespace 	= 'tiFy\\Plugins\\';
	
	//
	protected $SubDir		= 'plugins';
	
	//
	protected $Schema		= true;
}