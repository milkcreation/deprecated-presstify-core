<?php
namespace tiFy\Libraries;

class Autoload
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		// Autoload
		$loader = new \Psr4ClassLoader;
		
		// Emojione
		$loader->addNamespace( 'Emojione', __DIR__.'/Emojione' );
		
		// Emogrifier (Pelago)
		$loader->addNamespace( 'Pelago', __DIR__.'/Pelago' );
		
		/// Facebook
		$loader->addNamespace( 'Facebook', __DIR__.'/Facebook' );
		
		// GeoPattern
		$loader->addNamespace( 'RedeyeVentures\GeoPattern', __DIR__.'/GeoPattern' ); 
		
		// PHPMailer
		require_once( __DIR__ .'/PHPMailer/PHPMailerAutoload.php' );
				
		// Spyc
		require_once( __DIR__ .'/Spyc/Spyc.php' );
		
		
		$loader->register();		
	}
}