<?php
namespace tiFy;

use tiFy\tiFy;

class Core extends \tiFy\Environment\App
{
    /**
     * Liste des actions à déclencher
     * @var string[]
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference
     */
    protected $CallActions                = array(
        'after_setup_tify'
    );

    /**
     * Ordre de priorité d'exécution des actions
     * @var mixed
     */
    protected $CallActionsPriorityMap    = array(
        // Après l'instanciation des plugins et des sets
        'after_setup_tify' => 1
    );
    
    /**
     * Classes de rappel des composants natifs déclarés
     */
    private static $Registered = array();
    
    /**
     * DECLENCHEURS
     */
    /**
     * Au chargement de tiFy
     * 
     * @return void
     */
    final public function after_setup_tify()
    {        
        // Enregistrement des composants natifs inclus dans PresstiFy
        foreach(glob(tiFy::$AbsDir . '/core/*', GLOB_ONLYDIR) as $dirname) :
            $id = basename($dirname);
            self::register($id);
        endforeach;
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
     * 
     * @param string $id
     * 
     * @return object
     */
    public static function register($id)
    {
        $class_name = "tiFy\\Core\\{$id}\\{$id}";
        if(! class_exists($class_name))
            return;        
        if( isset(self::$Registered[$id]))
            return;
        
        tiFy::setApp(
            $class_name, 
            array(
                'id'    => $id,
                'type'  => 'core'
            )
        );    
            
        return self::$Registered[$id] = new $class_name;
    }
}