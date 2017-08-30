<?php
namespace tiFy\Core\Control;

class Control extends \tiFy\Environment\Core 
{
    /**
     * Liste des classes de rappel des controleurs
     */ 
    public static $Factory = array();

    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();

        foreach( glob( self::tFyAppDirname() .'/*/', GLOB_ONLYDIR ) as $filename ) :
            $basename     = basename( $filename );
            $ClassName    = "\\tiFy\\Core\\Control\\{$basename}\\{$basename}";
         
            self::register( $ClassName );
        endforeach;
        
        new _Deprecated\_Deprecated;
    }
        
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration des controleurs
     */
    final public static function register( $ClassName )
    {
        // Bypass
        if( ! class_exists( $ClassName ) ) :
            return;
        endif;
        $Class = self::loadOverride( $ClassName );
        
        if( ! empty( $Class->ID ) && ! isset( self::$Factory[$Class->ID] ) ) :
            self::$Factory[$Class->ID] = $Class;
        endif;
    }
}