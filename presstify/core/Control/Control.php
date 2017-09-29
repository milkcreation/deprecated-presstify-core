<?php
namespace tiFy\Core\Control;

class Control extends \tiFy\App\Core
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

        foreach(glob(self::tFyAppDirname() .'/*/', GLOB_ONLYDIR) as $filename) :
            $basename     = basename($filename);
            $ClassName    = "tiFy\\Core\\Control\\{$basename}\\{$basename}";
         
            self::register($ClassName);
        endforeach;

        do_action('tify_control_register');
    }
        
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration des controleurs
     *
     * @param string $classname
     *
     * @return void
     */
    final public static function register($classname)
    {
        // Bypass
        if(! class_exists($classname)) :
            return;
        endif;

        // Initialisation de la classe
        $Instance = self::loadOverride($classname);

        if(! empty($Instance->ID) && ! isset(self::$Factory[$Instance->ID])) :
            self::$Factory[$Instance->ID] = $Instance;
        endif;
    }
}