<?php
namespace tiFy\Helpers;

class Autoload
{
	public function __construct()
	{
		require __DIR__ .'/Components.php';
		require __DIR__ .'/Core.php';
		require __DIR__ .'/Deprecated.php';
		require __DIR__ .'/Taboox.php';
	}
}