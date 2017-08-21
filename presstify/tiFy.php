<?php
/**
 * @name PresstiFy
 * @namespace tiFy
 * @author Jordy Manner
 * @copyright Tigre Blanc Digital
 * @version 1.0.371
 */
namespace tiFy;

final class tiFy
{
    
    /**
     * Chemin absolu vers la racine de l'environnement
     * @var string
     */
    public static $AbsPath;
    
    /**
     * Chemin absolu vers la racine de presstiFy
     * @var string
     */
    public static $AbsDir;
    
    /**
     * Url absolue vers la racine la racine de presstiFy
     * @var string
     */
    public static $AbsUrl;
    
    /**
     * Paramètres de configuration de presstiFy
     * @var mixed
     */
    public static $Params;
    
    /**
     * Liste des applicatifs chargés
     * @var mixed
     */
    public static $Apps                 = array();
    
    
    /**
     * Classe de chargement automatique
     */ 
    private static $ClassLoader         = null;
    
    /**
     * CONSTRUCTEUR
     * 
     * @return void
     */
    public function __construct($AbsPath = null)
    {
        if( defined( 'WP_INSTALLING' ) && ( WP_INSTALLING === true ) )
            return;

        // Définition des constantes d'environnement
        /// Attributs de configuration
        if( ! defined( 'TIFY_CONFIG_DIR' ) )
            define( 'TIFY_CONFIG_DIR', get_template_directory() .'/config' );
        if( ! defined( 'TIFY_CONFIG_EXT' ) )
            define( 'TIFY_CONFIG_EXT', 'yml' );
        /// Répertoire des plugins
        if( ! defined( 'TIFY_PLUGINS_DIR' ) )
            define( 'TIFY_PLUGINS_DIR', dirname( __DIR__ ) .'/presstify-plugins' );
        
        // Définition des chemins absolus
        self::$AbsPath = $AbsPath ? $AbsPath : ABSPATH;
        self::$AbsDir = dirname(__FILE__);
        
        // Instanciation du moteur
        self::classLoad('tiFy', self::$AbsDir .'/bin');
        
        // Instanciation de l'environnement
        self::classLoad('tiFy\Environment', self::$AbsDir .'/bin/env');
        
        // Instanciation des librairies proriétaires
        self::classLoad('tiFy\Lib', self::$AbsDir .'/bin/lib');
  
        // Chargement des librairies tierces
        require_once tiFy::$AbsDir .'/vendor/autoload.php';
        
        // Instanciation des fonctions d'aides au développement
        self::classLoad('tiFy\Helpers', __DIR__ .'/helpers');        
        
        // Définition de l'url absolue
        self::$AbsUrl = \tiFy\Lib\File::getFilenameUrl(self::$AbsDir, self::$AbsPath);

        // Instanciation des composants natifs
        self::classLoad('tiFy\Core', __DIR__ . '/core');
        
        // Instanciation des composants dynamiques
        self::classLoad('tiFy\Components', __DIR__ . '/components');
        
        // Instanciation des extensions
        self::classLoad('tiFy\Plugins', TIFY_PLUGINS_DIR);
        
        // Instanciation des jeux de fonctionnalités complémentaires
        self::classLoad('tiFy\Set', tiFy::$AbsDir . '/set');
        
        // Chargement des controleurs
        /// Librairies proriétaires
        new Libraries;
        /// Fonctions d'aide au développement
        new Helpers;
        /// Paramètres
        new Params;
        /// Jeux de fonctionnalités
        new Set;
        /// Extensions
        new Plugins;
        /// Composants dynamiques
        new Components;
        /// Composants natifs
        new Core;
    }
        
    /**
     * CONTROLEURS
     */
    /**
     * Chargement automatique des classes
     * 
     * @param string $namespace Espace de nom
     * @param string|NULL $base_dir Chemin vers le repertoire
     * @param string|NULL $bootstrap Nom de la classe à instancier
     * 
     * @return void
     */
    public static function classLoad($namespace, $base_dir = null, $bootstrap = null)
    {
        if(is_null(self::$ClassLoader)) :
            require_once __DIR__ . '/bin/lib/ClassLoader/Psr4ClassLoader.php';
            self::$ClassLoader = new \Psr4ClassLoader;
        endif;
        
        if(! $base_dir)
            $base_dir = dirname(__FILE__);
        
        self::$ClassLoader->addNamespace($namespace, $base_dir, false);
        self::$ClassLoader->register();
            
        if($bootstrap) :
            $class_name = "\\". ltrim( $namespace, '\\' ) ."\\". $bootstrap;
            if(class_exists($class_name)) :
                new $class_name;
            endif;
        endif;
    }
    
    /**
     * Récupération d'une liste de paramètres
     */
    public static function getParams($type = null)
    {
        if(is_null($type)) :
            return self::$Params;
        endif;
        
        $type = strtolower($type);
        if(isset(self::$Params[$type])) :
            return self::$Params[$type];
        endif;
    }
    
    /**
     * Définition d'un applicatif
     * 
     * @param string $class_name
     * @param mixed $attrs 
     * 
     * @return void
     */
    public static function setApp($classname, $attrs = array())
    {
        self::$Apps[$classname] = $attrs;
    }
    
    /**
     * Récupération des attributs d'un applicatif
     * 
     * @param string $class_name
     * 
     * @return void
     */
    public static function getApp($classname)
    {
        if(isset(self::$Apps[$classname]))
            return self::$Apps[$classname];
    }    
    
    /**
     * Requête de récupération d'applicatifs selon une liste d'argument 
     * 
     * @param string $class_name
     * @param mixed $attrs 
     * 
     * @return mixed
     */
    public static function queryApps($args = array())
    {
        $apps = array(); $res = array();
        if(empty($args)) :
            $apps = self::$Apps;
        endif;
        
        foreach($apps as $classname => $attrs) :
            $attrs = wp_parse_args(
                array(
                    'classname' => $classname
                ),
                $attrs    
            );
            $res[] = $attrs; 
        endforeach;
        
        return $res;
    }
    
    /**
     * Récupération des attributs de configuration
     * @param string $index
     * @param string $default
     * 
     * @return mixed|$default
     */
    public static function getConfig($index, $default = '')
    {
        if( isset( self::$Params['config'][$index] ) ) :
            return self::$Params['config'][$index];
        endif;
        
        return $default;
    }
    
    /**
     * Récupération de la liste des composants dynamiques
     * 
     * @return string[]
     */
    public static function getComponents()
    {
        if(! empty(self::$Params['components'])) :
            return array_keys(self::$Params['components']);
        endif;
    }
    
    /**
     * Récupération de la liste des plugins
     * 
     * @return string[]
     */
    public static function getPlugins()
    {
        if(! empty(self::$Params['plugins'])) :
            return array_keys(self::$Params['plugins']);
        endif;
    }
    
    /**
     * Récupération de la liste des sets
     * 
     * @return string[]
     */
    public static function getSets()
    {
        if(! empty(self::$Params['set'])) :
            return array_keys(self::$Params['set']);
        endif;
    }
}
