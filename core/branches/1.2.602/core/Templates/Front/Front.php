<?php
namespace tiFy\Core\Templates\Front;

class Front extends \tiFy\App\Core
{
    /**
     * 
     */ 
    private static $Route                = null;
        
    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();

        // Définition de la route courante
        $matches = preg_split( '/\?.*/', $_SERVER['REQUEST_URI'], 2 );
        self::$Route = current( $matches );
    }
        
    /**
     * CONTROLEURS
     */
    /**
     * 
     */
    public static function getRoute()
    {
        return self::$Route;
    }
}