<?php
namespace tiFy;

use tiFy\tiFy;

class Components extends \tiFy\Environment\App
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
        // IMPORTANT - Après l'instanciation des plugins et des sets
        'after_setup_tify' => 1
    );
    
    /**
     * Classes de rappel des composants dynamiques déclarés
     */
    private static $Registered = array();
    
    /**
     * DECLENCHEURS
     */
    /**
     * Après l'initialisation de PresstiFy
     * 
     * @return void
     */
    final public function after_setup_tify()
    {
        // Enregistrement des composants dynamiques déclarés dans la configuration 
        foreach(tiFy::getComponents() as $id) :
            self::register($id);
        endforeach;
        
        // Enregistrement personnalisé
        do_action('tify_component_register');
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
     * @param string $id
     * 
     * @return object
     */
    public static function register($id)
    {
        $class_name = "tiFy\\Components\\{$id}\\{$id}";
        if(! class_exists($class_name))
            return;        
        if( isset(self::$Registered[$id]))
            return;
        
        tiFy::setApp(
            $class_name, 
            array(
                'id'    => $id,
                'type'  => 'components'
            )
        );    
            
        return self::$Registered[$id] = new $class_name;
    }
}