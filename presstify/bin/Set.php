<?php 
namespace tiFy;

use tiFy\tiFy;

class Set extends \tiFy\Environment\App
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
     * Liste des jeux de fonctionnalités chargés
     */
    protected static $Loaded = array();
    
    /**
     * Liste des jeux de fonctionnalités déclarés
     */
    protected static $Registered = array();
    
    /**
     * Après l'initialisation de PresstiFy
     */
    final public function after_setup_tify()
    {
        // Enregistrement des sets inclus dans PresstiFy
        foreach(glob(tiFy::$AbsDir . '/set/*', GLOB_ONLYDIR) as $filename) :
            $id = basename( $filename );
            $namespace = "\\tiFy\\Set\\{$id}";
            $base_dir = dirname($filename);
            $bootstrap = $id;
            self::load($id, compact('namespace','base_dir', 'bootstrap'));
        endforeach;
       
        // Enregistrements des sets déclarés dans la configuration
        if($sets = tiFy::getConfig('set', array())) :
            foreach($sets as $id => $attrs) :
                if( self::get($id) )
                    continue;
                self::load($id, $attrs);
            endforeach;
        endif;

        // Enregistrements personnalisés
        do_action('tify_set_load');        

        // Enregistrement des jeux de fonctionnalités déclarés dans la configuration 
        foreach(tiFy::getSets() as $id) :
            self::register($id);
        endforeach;
        
        // Enregistrement dynamique
        do_action('tify_set_register');
    }
        
    /**
     * CONTROLEURS
     */
    /**
     * Chargement
     * 
     * @param string $id
     * @param array $attrs
     */
    final public static function load($id, $attrs)
    {
        $defaults = array(
            'namespace' => null,
            'base_dir'  => null,
            'bootstrap' => null
        );
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

        // Formatage de l'espace de nom
        if(! is_null($namespace)) :
            $namespace = trim($namespace, "\\") . "\\";
        else :
            $namespace = trim(self::getOverrideNamespace(), "\\") . "\\Set\\";
        endif;
        tiFy::classLoad($namespace, $base_dir);
        
        // Formatage du point d'entrée unique
        if(! is_null($bootstrap)) :
            $bootstrap = trim($bootstrap, "\\");
        else :
            $bootstrap = $id;
        endif;
        
        $classname = $namespace . $bootstrap;
        
        tiFy::setApp(
            $classname, 
            array(
                'id'    => $id,
                'type'  => 'set'
            )
        );

        return self::$Loaded[$id] = $classname;
    }
    
    /**
     * Déclaration
     * 
     * @param string $id
     * 
     * @return object
     */
    public static function register($id, $args = array())
    {
        
        if(! isset(self::$Loaded[$id]))
            return;
        
        $class_name = self::$Loaded[$id];
       
        if(! class_exists($class_name))
            return;   
        if( isset(self::$Registered[$id]))
            return;
        
        return self::$Registered[$id] = new $class_name;
    }
        
    /**
     * Récupération de la liste des jeux de fonctionnalités déclarés
     * 
     * @return mixed
     */
    final public static function getList()
    {
        return self::$Registered;
    }
    
    /**
     * Récupération d'un jeu de fonctionnalités déclaré
     * 
     * @param string $id
     * 
     * @return mixed
     */
    final public static function get($id)
    {
        if(isset(self::$Registered[$id]))
            return self::$Registered[$id];
    }
}