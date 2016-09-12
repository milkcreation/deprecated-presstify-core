<?php
namespace tiFy\Environment;

use \tiFy\Environment\Config;

abstract class Component extends Config
{
	// Namespace
	protected $Namespace 	= 'tiFy\\Components\\';
	
	//
	protected $SubDir		= 'components';
}