<?php
namespace tiFy\Lib;

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
         * Emojione
         */
        $loader->addNamespace( 'Emojione', __DIR__ .'/Emojione' );
        
        /**
         * PresstiFy
         */
        /**
         * Lib
         * @deprecated
         */
        require_once( __DIR__ .'/Deprecated.php' );

        /**
         * Presstify Abstracts
         */
        $loader->addNamespace( 'tiFy\Abstracts', __DIR__ .'/Abstracts' );
        
        /**
         * Presstify Inherits
         */
        $loader->addNamespace( 'tiFy\Inherits', __DIR__ .'/Inherits' );
        
        /**
         * Presstify Statics
         */
        $loader->addNamespace( 'tiFy\Statics', __DIR__ .'/Statics' );
                        
        $loader->register();
    }
}