<?php
namespace tiFy;

class Libraries extends \tiFy\App\Factory
{
    /**
     * CONSTRUCTEUR
     * 
     * @return void
     */
    public function __construct()
    {
        tiFy::classLoad('tiFy\Lib', tiFy::$AbsDir .'/bin/lib');
        foreach (glob(tiFy::$AbsDir . '/bin/lib/*', GLOB_ONLYDIR) as $dirname) :
            tiFy::classLoad('tiFy\Lib', $dirname);
        endforeach;
        
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