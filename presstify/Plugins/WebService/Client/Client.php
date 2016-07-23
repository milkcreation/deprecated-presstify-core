<?php
namespace tiFy\Plugins\WebService\Client;

use tiFy\Environment\App;

class Client extends App
{
	// Configuration
	public static $Namespace;
	
	/* = CONSTRUCTEUR */
	public function __construct()
	{
		parent::__construct();
		
		$config = \tiFy\Plugins\WebService\WebService::getConfig( 'client' );
		
		$defaults = array(
			'namespace'	=> 'tiFyAPI/v1'	
		);
		$config = ( is_bool( $config ) ) ? : wp_parse_args( (array) $config, $defaults );
			
		foreach( array_keys( $defaults ) as $key ) :
			$attr = ucfirst( $key );
			self::${$attr} = $config[$key];
		endforeach;	
	}
}