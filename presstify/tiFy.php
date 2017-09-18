<?php
/**
 * @name PresstiFy
 * @namespace tiFy
 * @author Jordy Manner
 * @copyright Tigre Blanc Digital
 * @version 1.2.420
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
        if (file_exists(tiFy::$AbsDir .'/vendor/autoload.php'))
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
