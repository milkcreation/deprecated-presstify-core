<?php
namespace tiFy\Plugins\WebService;

use tiFy\Environment\Plugin;

class WebService extends Plugin
{
	/* = ARGUMENTS = */
	
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		require_once $this->Dirname .'/Helpers.php';
		
		// Chargement des contrôleurs
		if( self::getConfig('server') )
			new Server\Server;		
		if( self::getConfig('client') )
			new Client\Client;	
	}
}