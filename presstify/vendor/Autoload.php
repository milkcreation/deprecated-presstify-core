<?php
namespace tiFy\Vendor;

class Autoload
{
    /* = CONSTRUCTEUR = */
    public function __construct()
    {
        /**
         * Autoloader
         * @var \Psr4ClassLoader $loader
         */
        $loader = new \Psr4ClassLoader;
        
        /**
         * Librairies Tierces
         */
        /**
         * CSV
         */
        $loader->addNamespace( 'League\Csv', __DIR__ .'/League/Csv' );
        
        /**
         * Crypto
         */
        $loader->addNamespace( 'Defuse\Crypto', __DIR__ .'/Defuse/Crypto' );
        
        /**
         * Emojione
         */
        $loader->addNamespace( 'Emojione', __DIR__ .'/Emojione' );
        
        /**
         * Pelago/Emogrifier
         */
        $loader->addNamespace( 'Pelago', __DIR__ .'/Pelago' );
        
        /**
         * Facebook
         */
        $loader->addNamespace( 'Facebook', __DIR__ .'/Facebook' );
        
        /**
         * GeoPattern
         */
        $loader->addNamespace( 'RedeyeVentures\GeoPattern', __DIR__ .'/GeoPattern' ); 
        
        /**
         * Html2Text
         */
        $loader->addNamespace( 'Html2Text', __DIR__ .'/Html2Text/src' );
        
        /**
         * Mobile-Detect
         */
        $loader->addNamespace( 'Detection', __DIR__ .'/Mobile-Detect/namespaced/Detection' );
        
        /**
         * Oauth2 ???
         */
        $loader->addNamespace( 'OAuth2', __DIR__ .'/OAuth2' );
        
        /**
         * PHPColors
         */
        $loader->addNamespace( 'Mexitek\PHPColors', __DIR__ .'/Mexitek/PHPColors' );
        
        /**
         * PHPMailer
         */
        require_once( __DIR__ .'/PHPMailer/PHPMailerAutoload.php' );
        
        /**
         * QrCode
         */
        $loader->addNamespace( 'BaconQrCode', __DIR__ .'/BaconQrCode' );
        
        /**
         * ReCaptcha
         */
        $loader->addNamespace( 'ReCaptcha', __DIR__ .'/ReCaptcha' );
                
        /**
         * Spyc
         */
        require_once( __DIR__ .'/Spyc/Spyc.php' );
        
        /**
         * PresstiFy
         */
        /**
         * Lib
         * @deprecated
         */
        $loader->addNamespace( 'tiFy\Lib', __DIR__ .'/tiFy' );
        require_once( __DIR__ .'/tiFy/Deprecated.php' );
        
        /**
         * Presstify Abstracts
         */
        $loader->addNamespace( 'tiFy\Abstracts', __DIR__ .'/tiFy/Abstracts' );
        
        /**
         * Presstify Inherits
         */
        $loader->addNamespace( 'tiFy\Inherits', __DIR__ .'/tiFy/Inherits' );
        
        /**
         * Presstify Statics
         */
        $loader->addNamespace( 'tiFy\Statics', __DIR__ .'/tiFy/Statics' );
        
                
        $loader->register(); 
    }
}