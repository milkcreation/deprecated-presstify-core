<?php
namespace tiFy;

use tiFy\tiFy;
use tiFy\Environment\App;

class Plugins extends \tiFy\Environment\App
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
        'after_setup_tify' => 1
    );
    
    /**
     * Classes de rappel des composants déclarés
     */
    private static $Registered = array();
    
    /**
     * CONSTRUCTEUR
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->loadMustUse();
    }    
    
    /**
     * DECLENCHEURS
     */
    /**
     * Après l'initialisation de PresstiFy
     */
    final public function after_setup_tify()
    {
        // Enregistrement des extensions déclarées dans la configuration 
        foreach(tiFy::getPlugins() as $id) :
            self::register($id);
        endforeach;
        
        // Enregistrement dynamique
        do_action('tify_plugin_register');
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
        $class_name = "tiFy\\Plugins\\{$id}\\{$id}";
        if(! class_exists($class_name))
            return;        
        if( isset(self::$Registered[$id]))
            return;
        
        tiFy::setApp(
            $class_name, 
            array(
                'id'    => $id,
                'type'  => 'plugins'
            )
        );
            
        return self::$Registered[$id] = new $class_name;
    }
    
    /**
     * Chargement des must-use
     */
    protected function loadMustUse()
    {
        foreach(glob(TIFY_PLUGINS_DIR . '/*', GLOB_ONLYDIR) as $plugin_dir) :
            if(! file_exists($plugin_dir . '/MustUse'))
                continue;
            if(! $dh = opendir($plugin_dir . '/MustUse'))
                continue;
            while(($file = readdir( $dh )) !== false) :
                if(substr( $file, -4 ) == '.php') :
                    include_once($plugin_dir . '/MustUse/' . $file);
                endif;
            endwhile;
        endforeach;
    }
}