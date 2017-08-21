<?php
namespace tiFy;

use tiFy\tiFy;

class Libraries extends \tiFy\Environment\App
{
    /**
     * CONSTRUCTEUR
     * 
     * @return void
     */
    public function __construct()
    {        
        /**
         * Librairies Tierces
         */
        /**
         * Emojione
         */
        tiFy::classLoad('Emojione', tiFy::$AbsDir . '/bin/lib/Emojione');
        
        /**
         * PresstiFy
         */
        /**
         * Lib
         * @deprecated
         */
        require_once(tiFy::$AbsDir . '/bin/lib/Deprecated.php');

        /**
         * Abstracts
         */
        tiFy::classLoad('tiFy\Abstracts', tiFy::$AbsDir . '/bin/lib/Abstracts');
        
        /**
         * Inherits
         */
        tiFy::classLoad('tiFy\Inherits', tiFy::$AbsDir . '/bin/lib/Inherits');
        
        /**
         * Statics
         */
        tiFy::classLoad('tiFy\Statics', tiFy::$AbsDir . '/bin/lib/Statics');

        /**
         * Vidéo
         */
        new \tiFy\Lib\Video\Video;
    }
}