<?php

/**
 * @name PresstiFy
 * @namespace tiFy
 * @author Jordy Manner
 * @copyright Tigre Blanc Digital
 * @version 1.2.617.180309
 */

namespace tiFy;

use \tiFy\Lib\File;
use Symfony\Component\HttpFoundation\Request;
use League\BooBoo\BooBoo;
use League\BooBoo\Formatter\HtmlFormatter;
use League\BooBoo\Handler\CallableHandler;
use League\Container\Container;

final class tiFy
{
    /**
     * Chemin absolu vers la racine de l'environnement
     * @var resource
     */
    public static $AbsPath;
    
    /**
     * Chemin absolu vers la racine de presstiFy
     * @var resource
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
     * Classe de rappel ddu conteneur d'injection de dépendance
     * @var \League\Container\Container
     */
    private static $Container;

    /**
     * Attributs de configuration
     * @var mixed
     */
    protected static $Config            = [];

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
        if (defined('WP_INSTALLING') && (WP_INSTALLING === true)) :
            return;
        endif;

        // Définition des chemins absolus
        self::$AbsPath = $AbsPath ? $AbsPath : ABSPATH;
        self::$AbsDir = dirname(__FILE__);

        // Définition des constantes d'environnement
        if (! defined('TIFY_CONFIG_DIR')) :
            define( 'TIFY_CONFIG_DIR', get_template_directory() . '/config');
        endif;
        if (! defined('TIFY_CONFIG_EXT')) :
            define('TIFY_CONFIG_EXT', 'yml');
        endif;
        /// Répertoire des plugins
        if (! defined('TIFY_PLUGINS_DIR')) :
            define('TIFY_PLUGINS_DIR', self::$AbsDir . '/plugins');
        endif;

        // Instanciation du moteur
        self::classLoad('tiFy', self::$AbsDir .'/bin');

        // Instanciation des controleurs en maintenance
        self::classLoad('tiFy\Maintenance', self::$AbsDir . '/bin/maintenance', 'Maintenance');

        // Instanciation des controleurs dépréciés
        self::classLoad('tiFy\Deprecated', self::$AbsDir . '/bin/deprecated', 'Deprecated');
        
        // Instanciation des l'environnement des applicatifs
        self::classLoad('tiFy\App', self::$AbsDir .'/bin/app');
        
        // Instanciation des librairies proriétaires
        new Libraries;

        // Initialisation de la gestion des traductions
        new Languages;

        // Chargement des librairies tierces
        if (file_exists(tiFy::$AbsDir .'/vendor/autoload.php')) :
            require_once tiFy::$AbsDir .'/vendor/autoload.php';
        endif;

        // Affichage des erreurs
        /*$formatter = new HtmlFormatter;
        $handler = new CallableHandler([$this, 'displayError']);
        $formatter->setErrorLimit(E_ALL);

        $error_handler = new BooBoo([$formatter], [$handler]);
        $error_handler->register();*/

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

    public function displayError($e)
    {
        echo $e->getMessage();

        exit;
    }

    /**
     * Formatage lower_name d'une chaine de caratère
     * Converti une chaine de caractère CamelCase en snake_case
     * ex : _tiFyTest1_Test2 > _tiFy-test1_test2
     *
     * @param string $name
     * @param string $separator Caractère de séparation d'occurences
     *
     * @return string
     */
    public static function formatLowerName($name, $separator = '-')
    {
        $parts = [];
        if (preg_match('#^_?tiFy#', $name, $match)) :
            $parts[] = reset($match);
            $name = preg_replace('#^_?tiFy#', '', $name);
        endif;
        $parts += array_map('lcfirst', preg_split('#(?=[A-Z])#', $name));

        if ($parts) :
            $name = '';
            foreach($parts as $k => $part) :
                if (empty($part)) :
                    continue;
                elseif (!$k && preg_match('#^_?tiFy#', $part)) :
                    $name = $part;
                else :
                    $name .= preg_match('#_$#', $part) ? $part : "{$part}{$separator}";
                endif;
            endforeach;
            $name = rtrim($name, $separator);
        endif;

        return $name;
    }

    /**
     * Formatage UpperName d'une chaine de caratère
     * Converti une chaine de caractère snake_case en CamelCase
     * ex : _tiFy-test1_test2 > _tiFyTest1_Test2
     *
     * @param string $name Chaîne de caractère à traiter
     * @param bool $underscore Conservation des underscore
     *
     * @return string
     */
    public static function formatUpperName($name, $underscore = true)
    {
        $name = join(($underscore ? '_' : ''), array_map('ucfirst', preg_split('#_#', $name)));
        $name = join('', array_map('ucfirst', preg_split('#-#', $name)));
        $name = preg_replace('#^(_)?(T)(iFy)#', '$1t$3', $name);

        return $name;
    }

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
     * Conteneur d'injection de dépendances
     * @see http://container.thephpleague.com/
     *
     * @return \League\Container\Container
     */
    public static function getContainer()
    {
        if (!self::$Container) :
            self::$Container = new Container();
        endif;

        return self::$Container;
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
