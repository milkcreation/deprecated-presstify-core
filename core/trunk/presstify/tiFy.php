<?php
/**
 * @name PresstiFy
 * @namespace tiFy
 * @author Jordy Manner
 * @copyright Tigre Blanc Digital
 * @version 1.2.543.180105
 */
namespace tiFy;

use \tiFy\Lib\File;
use Symfony\Component\HttpFoundation\Request;

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
     * Classe de rappel de la requête globale
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private static $GlobalRequest;
    
    /**
     * Attributs de configuration
     * @var mixed
     */
    protected static $Config            = array();

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
        if (defined('WP_INSTALLING') && (WP_INSTALLING === true))
            return;

        // Définition des chemins absolus
        self::$AbsPath = $AbsPath ? $AbsPath : ABSPATH;
        self::$AbsDir = dirname(__FILE__);

        // Définition des constantes d'environnement
        if (! defined('TIFY_CONFIG_DIR'))
            define( 'TIFY_CONFIG_DIR', get_template_directory() . '/config');
        if (! defined('TIFY_CONFIG_EXT'))
            define('TIFY_CONFIG_EXT', 'yml');
        /// Répertoire des plugins
        if (! defined('TIFY_PLUGINS_DIR'))
            define('TIFY_PLUGINS_DIR', self::$AbsDir . '/plugins');
        
        // Instanciation du moteur
        self::classLoad('tiFy', self::$AbsDir .'/bin');
        
        // Instanciation des depréciations
        self::classLoad('tiFy\Deprecated', self::$AbsDir . '/bin/deprecated', 'Deprecated');
        
        // Instanciation des l'environnement des applicatifs
        self::classLoad('tiFy\App', self::$AbsDir .'/bin/app');
        
        // Instanciation des librairies proriétaires
        new Libraries;

        // Chargement des librairies tierces
        if (file_exists(tiFy::$AbsDir .'/vendor/autoload.php')) :
            require_once tiFy::$AbsDir .'/vendor/autoload.php';
        endif;
        
        // Instanciation des fonctions d'aides au développement
        self::classLoad('tiFy\Helpers', __DIR__ .'/helpers');
        
        // Définition de l'url absolue
        self::$AbsUrl = File::getFilenameUrl(self::$AbsDir, self::$AbsPath);

        // Instanciation des composants natifs
        self::classLoad('tiFy\Core', __DIR__ . '/core');
        
        // Instanciation des composants dynamiques
        self::classLoad('tiFy\Components', __DIR__ . '/components');
        
        // Instanciation des extensions
        self::classLoad('tiFy\Plugins', TIFY_PLUGINS_DIR);
        
        // Instanciation des jeux de fonctionnalités complémentaires
        self::classLoad('tiFy\Set', tiFy::$AbsDir . '/set');
        
        // Instanciation des fonctions d'aide au développement
        new Helpers;
        
        // Instanciation des applicatifs
        new Apps;
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
        if (is_null(self::$ClassLoader)) :
            require_once __DIR__ . '/bin/lib/ClassLoader/Psr4ClassLoader.php';
            self::$ClassLoader = new \Psr4ClassLoader;
        endif;
        
        if (!$base_dir) :
            $base_dir = dirname(__FILE__);
        endif;

        self::$ClassLoader->addNamespace($namespace, $base_dir, false);
        self::$ClassLoader->register();
            
        if ($bootstrap) :
            $classname = "\\". ltrim( $namespace, '\\' ) ."\\". $bootstrap;

            if(class_exists($classname)) :
                new $classname;
            endif;
        endif;
    }

    /**
     * Récupération de la classe de rappel de la requête global
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public static function getGlobalRequest()
    {
        if (!self::$GlobalRequest) :
            self::$GlobalRequest = Request::createFromGlobals();
        endif;

        return self::$GlobalRequest;
    }

    /**
     * Appel d'une méthode de requête global
     * @see https://symfony.com/doc/current/components/http_foundation.html
     * @see http://api.symfony.com/4.0/Symfony/Component/HttpFoundation/ParameterBag.html
     *
     * @param string $method Nom de la méthode à appeler (all|keys|replace|add|get|set|has|remove|getAlpha|getAlnum|getBoolean|getDigits|getInt|filter)
     * @param array $args Tableau associatif des arguments passés dans la méthode.
     * @param string $type Type de requête à traiter POST|GET|COOKIE|FILES|SERVER ...
     *
     * @return mixed
     */
    public static function callGlobalRequestVar($method, $args = [], $type = '')
    {
        if (!$request = self::getGlobalRequest()) :
            return;
        endif;

        switch(strtolower($type)) :
            default :
                $object = $request;
                break;
            case 'post' :
            case 'request' :
                $object = $request->request;
                break;
            case 'get' :
            case 'query' :
                $object = $request->query;
                break;
            case 'cookie' :
            case 'cookies' :
                $object = $request->cookies;
                break;
            case 'files' :
                $object = $request->files;
                break;
            case 'server' :
                $object = $request->server;
                break;
            case 'headers' :
                $object = $request->headers;
                break;
            case 'attributes' :
                $object = $request->attributes;
                break;
        endswitch;

        if (method_exists($object, $method)) :
            return call_user_func_array([$object, $method], $args);
        endif;
    }
    
    /**
     * Récupération d'attributs de configuration globale
     * 
     * @param NULL|string $attr Attribut de configuration
     * @param string $default Valeur de retour par défaut
     * 
     * @return mixed|$default
     */
    public static function getConfig($attr = null, $default = '')
    {
        if (is_null($attr))
            return self::$Config;
        
        if (isset(self::$Config[$attr])) :
            return self::$Config[$attr];
        endif;
        
        return $default;
    }
    
    /**
     * Définition d'un attribut de configuration globale
     * 
     * 
     */
    public static function setConfig($key, $value = '')
    {
        self::$Config[$key] = $value;
    }
}
