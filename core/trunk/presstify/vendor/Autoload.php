<?php
namespace tiFy\Vendor;

class Autoload
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		// Autoload
		$loader = new \Psr4ClassLoader;
		
		// CSV
		$loader->addNamespace( 'League\Csv', __DIR__ .'/League/Csv' );
		
		// Crypto
		$loader->addNamespace( 'Defuse\Crypto', __DIR__ .'/Defuse/Crypto' );
		
		// Emojione
		$loader->addNamespace( 'Emojione', __DIR__ .'/Emojione' );
		
		// Emogrifier (Pelago)
		$loader->addNamespace( 'Pelago', __DIR__ .'/Pelago' );
		
		/// Facebook
		$loader->addNamespace( 'Facebook', __DIR__ .'/Facebook' );
		
		// GeoPattern
		$loader->addNamespace( 'RedeyeVentures\GeoPattern', __DIR__ .'/GeoPattern' ); 
		
		/// Html2Text
		$loader->addNamespace( 'Html2Text', __DIR__ .'/Html2Text/src' );
		
		/// Html2Text
		$loader->addNamespace( 'Detection', __DIR__ .'/Mobile-Detect/namespaced/Detection' );
		
		// PHPColors
		$loader->addNamespace( 'OAuth2', __DIR__ .'/OAuth2' );
		
		// PHPColors
		$loader->addNamespace( 'Mexitek\PHPColors', __DIR__ .'/Mexitek/PHPColors' );
		
		// PHPMailer
		require_once( __DIR__ .'/PHPMailer/PHPMailerAutoload.php' );
		
		// QrCode
		$loader->addNamespace( 'BaconQrCode', __DIR__ .'/BaconQrCode' );
		
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