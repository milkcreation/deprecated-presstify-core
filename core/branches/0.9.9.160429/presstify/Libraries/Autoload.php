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
		require_once( __DIR__ .'/Addendum/annotations.php' );
		
		// Emojione
		$loader->addNamespace( 'Emojione', __DIR__ .'/Emojione' );
		
		// Emogrifier (Pelago)
		$loader->addNamespace( 'Pelago', __DIR__ .'/Pelago' );
		
		/// Facebook
		$loader->addNamespace( 'Facebook', __DIR__ .'/Facebook' );
		
		// GeoPattern
		$loader->addNamespace( 'RedeyeVentures\GeoPattern', __DIR__ .'/GeoPattern' ); 
		
		// PHPColors
		$loader->addNamespace( 'Mexitek\PHPColors', __DIR__ .'/Mexitek/PHPColors' );
		
		// PHPMailer
		require_once( __DIR__ .'/PHPMailer/PHPMailerAutoload.php' );
				
		/// ReCaptcha
		$loader->addNamespace( 'ReCaptcha', __DIR__ .'/ReCaptcha' );
				
		// Spyc
		require_once( __DIR__ .'/Spyc/Spyc.php' );
		
		// Librairie Tify
		$loader->addNamespace( 'tiFy\Lib', __DIR__ .'/tiFy' );
		require_once( __DIR__ .'/tiFy/Deprecated.php' );
		
		$loader->register();		
	}
}