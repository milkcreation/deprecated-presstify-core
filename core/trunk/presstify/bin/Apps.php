<?php
namespace tiFy;

use tiFy\tiFy;
use tiFy\Lib\File;
use Symfony\Component\Yaml\Yaml;

final class Apps
{
    /**
     * Attributs de configuration
     */
    private static $Config              = array(
        'Core'          => array(),
        'Components'    => array(),
        'Plugins'       => array(),
        'Set'           => array(),
        'Schema'        => array()
    );
    
    /**
     * Liste des applicatifs déclarés
     * 
     * @var string[][] {
     *      @var array {
     *          @var NULL|string $Id Identifiant de qualification de l'applicatif
     *          @var string $Type Type d'applicatif Components|Core|Plugins|Set|Customs
     *          @var \ReflectionClass $ReflectionClass Informations sur la classe
     *          @var string $ClassName Nom complet et unique de la classe (espace de nom inclus)
     *          @var string $ShortName Nom court de la classe
     *          @var string $Namespace Espace de Nom
     *          @var string $Filename Chemin absolu vers le fichier de la classe
     *          @var string $Dirname Chemin absolu vers le repertoire racine de la classe
     *          @var string $Url Url absolu vers le repertoire racine de la classe
     *          @var string $Rel Chemin relatif vers le repertoire racine de la classe
     *          @var mixed $Config Attributs de configuration de configuration de l'applicatif
     *          @var array $OverridePath {
     *              Liste des chemins vers le repertoire de stockage des gabarits de l'applicatif
     *
     *              @var array $app {
     *                  Attributs du repertoire des gabarits de l'application
     *
     *                  @var string $url Url vers le repertoire des gabarits
     *                  @var string $path Chemin absolu vers le repertoire des gabarits
     *                  @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *                  @var string $baseurl Url vers le repertoire racine
     *                  @var string $basedir Chemin absolu vers le repertoire
     *
     *              }
     *              @var array $theme {
     *                  Attributs du repertoire des gabarits de surcharge du theme actif
     *
     *                  @var string $url Url vers le repertoire des gabarits
     *                  @var string $path Chemin absolu vers le repertoire des gabarits
     *                  @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *                  @var string $baseurl Url vers le repertoire racine
     *                  @var string $basedir Chemin absolu vers le repertoire
     *          }
     *      }
     * }
     */
    private static $Registered          = array();
    
    /**
     * CONSTRUCTEUR
     * 
     * @return void
     */
    public function __construct()
    {
        // Chargement des MustUse
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

        add_action('after_setup_theme', array($this, 'after_setup_theme'), 0);
    }
    
    /**
     * DECLENCHEURS
     */
    /**
     * Après l'initialisation du thème
     */
    final public function after_setup_theme()
    {
        // Récupération des fichier de configuration natifs de PresstiFy
        $_dir = @ opendir(tiFy::$AbsDir . "/bin/config");
        if ($_dir) :
            while (($file = readdir($_dir)) !== false) :
                // Bypass
                if (substr($file, 0, 1) == '.')
                    continue;
                
                $basename = basename( $file, ".yml" );
                
                // Bypass
                if (! in_array($basename, array('config', 'core', 'components', 'plugins', 'set', 'schema')))
                     continue;

                if (! isset(${$basename})) :
                    ${$basename} = array();
                endif;
                
                ${$basename} = self::parseConfigFile(tiFy::$AbsDir . "/bin/config/{$file}", ${$basename}, 'yml');
            endwhile;
            closedir($_dir);
        endif;

        // Récupération du fichier de configuration personnalisée globale de PresstiFy
        $_dir = @ opendir(TIFY_CONFIG_DIR);
        if ($_dir) :
            while (($file = readdir($_dir)) !== false) :
                // Bypass
                if (substr($file, 0, 1) == '.')
                        continue;
                
                $basename = basename($file, ".". TIFY_CONFIG_EXT);
                
                // Bypass
                if ($basename !== 'config')
                     continue;
                
                if(! isset($config)) :
                    $config = array();
                endif;

                $config = self::parseConfigFile(TIFY_CONFIG_DIR . "/" . $file, $config, TIFY_CONFIG_EXT);
            endwhile;
            closedir($_dir);
        endif;

        /// Définition de l'environnement de surcharge
        if ($app = $config['app']) :
            // Espace de nom
            if (! isset($app['namespace'])) :
                $app['namespace'] = 'App';
            endif;
            $app['namespace'] = trim($app['namespace'], '\\');
            
            // Répertoire de stockage
            if (! isset($app['base_dir'])) :
                $app['base_dir'] = get_template_directory() . "/app";
            endif;
            $app['base_dir'] = $app['base_dir'];
            
            // Point d'entrée unique
            if (! isset($app['bootstrap'])) :
                $app['bootstrap'] = 'Autoload';
            endif;
            $app['bootstrap'] = $app['bootstrap'];
            
            tiFy::classLoad($app['namespace'], $app['base_dir'], (! empty($app['bootstrap']) ? $app['bootstrap'] : false));
            
            // Chargement automatique
            foreach (array('components', 'core', 'plugins', 'set', 'schema') as $dir) :
                if (! file_exists($app['base_dir']. '/' .$dir))
                    continue;
                
                tiFy::classLoad($app['namespace']. "\\" . ucfirst($dir), $app['base_dir']. '/' . $dir, 'Autoload');
            endforeach;
            
            $config['app'] = $app;
        endif;

        // Enregistrement de la configuration globale de PresstiFy
        foreach ($config as $key => $value) :
            tiFy::setConfig($key, $value);
        endforeach;
        
        // Chargement des traductions
        do_action('tify_load_textdomain');
        
        // Récupération des fichiers de configuration personnalisés des applicatifs (core|components|plugins|set|schema)
        $_dir = @ opendir(TIFY_CONFIG_DIR);
        if ($_dir) :
            while (($file = readdir($_dir)) !== false) :
                // Bypass
                if (substr($file, 0, 1 ) == '.')
                    continue;
                
                $basename = basename($file, ".". TIFY_CONFIG_EXT);

                // Bypass
                if (! in_array($basename, array('core', 'components', 'plugins', 'set', 'schema')))
                     continue;

                if (! isset(${$basename})) :
                    ${$basename} = array();
                endif;
                
                ${$basename} += self::parseConfigFile(TIFY_CONFIG_DIR . "/" . $file, ${$basename}, TIFY_CONFIG_EXT);
            endwhile;
            closedir( $_dir );
        endif;
        
        foreach (array('core', 'components', 'plugins', 'set', 'schema') as $app) :
            // Bypass
            if (! isset(${$app}))
                continue;
            
            $App = ucfirst($app);
            self::$Config[$App] = ${$app};
        endforeach;

        // Chargement des applicatifs
        // Jeux de fonctionnalités
        new Set;
        // Enregistrement des jeux de fonctionnalités déclarés dans la configuration
        if (isset(self::$Config['Set'])) :
            foreach ((array) self::$Config['Set'] as $id => $attrs) :
                Set::register($id, $attrs);
            endforeach;
        endif;
        
        // Extensions
        new Plugins;
        // Enregistrement des extensions déclarées dans la configuration*
        if (isset(self::$Config['Plugins'])) :
            foreach ((array) self::$Config['Plugins'] as $id => $attrs) :
                Plugins::register($id, $attrs);
            endforeach;
        endif;
            
        // Composants dynamiques
        new Components;
        // Enregistrement des composants dynamiques déclarés dans la configuration
        if (isset(self::$Config['Components'])) :
            foreach ((array) self::$Config['Components'] as $id => $attrs) :
                Components::register($id, $attrs);
            endforeach;
        endif;
        
        // Composants natifs
        new Core;
        // Enregistrement des composants natifs inclus dans PresstiFy
        foreach (glob(tiFy::$AbsDir . '/core/*', GLOB_ONLYDIR) as $dirname) :
            $id = basename($dirname);
            $attrs = isset(self::$Config['Core'][$id]) ? self::$Config['Core'][$id] : array();
            Core::register($id, $attrs);
        endforeach;
        
        // Instanciation des applicatifs
        foreach (array('set', 'plugins', 'components', 'core') as $app) :
            do_action("tify_{$app}_register");
            $App = ucfirst($app);
            if ($apps = self::query(array('Type'=>$App))) :
                foreach($apps as $id => $attrs) :
                    new $attrs['ClassName'];
                endforeach;
            endif;
        endforeach;

        // Déclenchement des actions post-paramétrage
        do_action('after_setup_tify');
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration d'un applicatif
     * 
     * @param object|string $classname Object ou Nom de la classe
     * @param NULL|string $type Type de l'applicatif NULL|core|components|plugins|set
     * @param array $attrs {
     *      Attributs de paramétrage de l'applicatif
     *      
     *      @type string $Id Identifiant qualificatif de l'applicatif
     *      @type array $Config Attributs de configuration
     * }
     * 
     * @return NULL|mixed
     */
    public static function register($classname, $type = null, $attrs = array())
    {
        if (is_object($classname)) :
            $classname = get_class($classname);
        endif;
            
        // Bypass
        if (! class_exists($classname))
            return;
        
        $config_attrs = isset($attrs['Config']) ? $attrs['Config'] : array();
        
        if (! isset(self::$Registered[$classname])) :
            $ReflectionClass = new \ReflectionClass($classname);
            $ClassName = $ReflectionClass->getName();
            $ShortName = $ReflectionClass->getShortName();
            $Namespace = $ReflectionClass->getNamespaceName();
            
            // Chemins
            $Filename = $ReflectionClass->getFileName();
            $Dirname = dirname($Filename);
            $Url = untrailingslashit(File::getFilenameUrl($Dirname, tiFy::$AbsPath));
            $Rel = untrailingslashit(File::getRelativeFilename($Dirname, tiFy::$AbsPath));
            
            // Traitement de la définition
            if (in_array($type, array('core', 'components', 'plugins', 'set'))) :
                $Id = isset($attrs['Id']) ? $attrs['Id'] : basename($classname);
                $Type = ucfirst($type);
            else :
                $Id = $ClassName;
                $Type = 'Customs';
            endif;

            // Configuration
            $Config = $config_attrs;

            // Gabarits d'affichage
            // Chemins de gabarits de l'application
            if (in_array($Type, ['Set', 'Plugins'])) :
                $OverridePath['app'] = [
                    'path' => $Dirname . '/app',
                    'url' => $Url . '/app',
                    'subdir' => '',
                    'basedir' => $Dirname . '/app',
                    'baseurl' => $Url . '/app',
                    'error' => false
                ];
                $OverridePath['templates'] = [
                    'path' => $Dirname . '/templates',
                    'url' => $Url . '/templates',
                    'subdir' => '',
                    'basedir' => $Dirname . '/templates',
                    'baseurl' => $Url . '/templates',
                    'error' => false
                ];
            else :
                $OverridePath['app'] = [
                    'path' => '',
                    'url' => '',
                    'subdir' => '',
                    'basedir' => '',
                    'baseurl' => '',
                    'error' => new \WP_Error('OverridePathAppInvalidType', __('Seules les extensions (plugins) et les jeux de fonctionnalités (set) sont en mesure de surcharger les applications', 'tify'))
                ];
                $OverridePath['templates'] = [
                    'path' => '',
                    'url' => '',
                    'subdir' => '',
                    'basedir' => '',
                    'baseurl' => '',
                    'error' => new \WP_Error('OverridePathTemplatesInvalidType', __('Seules les extensions (plugins) et les jeux de fonctionnalités (set) sont en mesure de surcharger les gabarits', 'tify'))
                ];
            endif;

            // Chemins de gabarits de surchage du thème
            if ($subdir = File::getRelativeFilename($Dirname, tiFy::$AbsDir)) :
            //elseif (preg_match('#^(.*)\/app\/#', $Dirname) && ($subdir = preg_replace('#^(.*)\/app\/#', '', $Dirname))) :

                $subdir = \untrailingslashit($subdir);
                $_subdir = $subdir ? '/' . $subdir : '';

                $OverridePath['theme_app'] = [
                    'path' => get_template_directory() . '/app' . $_subdir,
                    'url' => get_template_directory_uri() . '/app' . $_subdir,
                    'subdir' => $subdir,
                    'basedir' => get_template_directory() . '/app',
                    'baseurl' => get_template_directory_uri() . '/app',
                    'error' => false
                ];
                $OverridePath['theme_templates'] = [
                    'path' => get_template_directory() . '/templates' . $_subdir,
                    'url' => get_template_directory_uri() . '/templates' . $_subdir,
                    'subdir' => $subdir,
                    'basedir' => get_template_directory() . '/templates',
                    'baseurl' => get_template_directory_uri() . '/templates',
                    'error' => false
                ];
            else :
                $OverridePath['theme_app'] = [
                    'path' => '',
                    'url' => '',
                    'subdir' => '',
                    'basedir' => '',
                    'baseurl' => '',
                    'error' => new \WP_Error('OverridePathAppTemplatesOut', __('Fonctionnalité non active pour le moment', 'tify'))
                ];
                $OverridePath['theme_templates'] = [
                    'path' => '',
                    'url' => '',
                    'subdir' => '',
                    'basedir' => '',
                    'baseurl' => '',
                    'error' => new \WP_Error('OverridePathThemeTemplatesOut', __('Fonctionnalité non active pour le moment', 'tify'))
                ];
            endif;
        else :
            extract(self::$Registered[$classname]);
            $Config = wp_parse_args($config_attrs, $Config);
        endif;
        
        // Traitement de la configuration
        return self::$Registered[$classname] = compact(
            // Définition
            'Id',
            'Type',
            // Informations sur la classe
            'ReflectionClass',
            'ClassName',
            'ShortName',
            'Namespace',
            // Chemins d'accès
            'Filename',
            'Dirname',
            'Url',
            'Rel',
            // Configuration
            'Config',
            // Gabarits d'affichage
            'OverridePath'
        );
    }
    
    /**
     * Vérification d'existance
     * 
     * @param object|string $classname Objet ou Nom de la classe de l'applicatif
     * 
     * @return bool
     */
    public static function is($classname)
    {
        if (is_object($classname))
            $classname = get_class($classname);
                
        return isset(self::$Registered[$classname]);
    }
    
    /**
     * Récupération d'une liste applicatifs déclarés selon une liste de critères relatif aux attributs
     * 
     * @param array {
     *      @var string $Type Type d'applicatif Components|Core|Plugins|Set|Customs
     * }
     * 
     */
    public static function query($args = array())
    {
        $results = array();
        
        foreach ((array) self::$Registered as $classname => $attrs) :
            foreach ($args as $attr => $value):
                if (! isset($attrs[$attr]) || ($attrs[$attr] != $value)) :
                    continue 2;
                endif;
            endforeach;
            $results[$classname] = $attrs;
        endforeach;
        
        return $results;
    }
    
    /**
     * Récupération la liste des composants natifs
     */
    public static function queryCore()
    {
        return self::query(array('Type' => 'Core'));
    }
    
    /**
     * Récupération la liste des composants dynamiques déclarés
     */
    public static function queryComponents()
    {
        return self::query(array('Type' => 'Components'));
    }
    
    /**
     * Récupération la liste des extensions déclarées
     */
    public static function queryPlugins()
    {
        return self::query(array('Type' => 'Plugins'));
    }
    
    /**
     * Récupération la liste des jeux de fonctionnalités déclarés
     */
    public static function querySet()
    {
        return self::query(array('Type' => 'Set'));
    }
    
    /**
     * Récupération de la liste des attributs d'un applicatif déclaré
     * 
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'applicatif
     * 
     * @return NULL|array {
     *      @var NULL|string $Id Identifiant de qualification de l'applicatif
     *      @var string $Type Type d'applicatif Components|Core|Plugins|Set|Customs
     *      @var \ReflectionClass $ReflectionClass Informations sur la classe
     *      @var string $ClassName Nom complet et unique de la classe (espace de nom inclus)
     *      @var string $ShortName Nom court de la classe
     *      @var string $Namespace Espace de Nom
     *      @var string $Filename Chemin absolu vers le fichier de la classe
     *      @var string $Dirname Chemin absolu vers le repertoire racine de la classe
     *      @var string $Url Url absolu vers le repertoire racine de la classe
     *      @var string $Rel Chemin relatif vers le repertoire racine de la classe
     *      @var mixed $Config Attributs de configuration de configuration de l'applicatif
     * }
     */
    public static function getAttrs($classname)
    {
        if (is_object($classname))
            $classname = get_class($classname);
                
        if (isset(self::$Registered[$classname]))
            return self::$Registered[$classname];
    }
    
    /**
     * Définition d'attributs d'un applicatif déclaré
     * 
     * @param mixed $attrs Attributs de l'applicatif 
     * @param object|string $classname Objet ou Nom de la classe de l'applicatif
     * 
     * @return bool
     */
    public static function setAttrs($attrs = array(), $classname)
    {
        if (is_object($classname))
            $classname = get_class($classname);
        
        if (! isset(self::$Registered[$classname]))
            return false;
        
        self::$Registered[$classname] = wp_parse_args($attrs,self::$Registered[$classname]);
        
        return true;
    }
    
    /**
     * Définition d'un attributs de configuration d'un applicatif déclaré
     * 
     * @param string $name Qualification de l'attribut de configuration
     * @param mixed $value Valeur de l'attribut de configuration
     * @param object|string $classname Objet ou Nom de la classe de l'applicatif
     * 
     * @return bool
     */
    public static function setConfigAttr($name, $value = '', $classname)
    {
        if (is_object($classname))
            $classname = get_class($classname);
        
        if (! isset(self::$Registered[$classname]))
            return false;
        
        self::$Registered[$classname]['Config'][$name] = $value;
        
        return true;
    }
    
    /**
     * Traitement d'un fichier de configuration
     * 
     * @param string $filename Chemin du fichier de configuration
     * @param array $current Attributs de configuration existant
     * @param string $ext Extension du fichier de configuration
     * @param bool $eval Traitement de la configuration de fichier
     * 
     * @return array|array[]|array[][]
     */
    public static function parseConfigFile($filename, $current,  $ext = 'yml', $eval = true)
    {
        if (! is_dir($filename)) :
            if (substr($filename, -4) == ".{$ext}") :
                if ($eval) :
                    return wp_parse_args(self::parseAndEval($filename), $current);
                else :
                    return wp_parse_args(self::parseFile($filename), $current);
                endif;
            endif;
        elseif ($subdir = @ opendir($filename)) :
            $res = array();
            while (($subfile = readdir($subdir)) !== false) :
                // Bypass
                if (substr( $subfile, 0, 1) == '.') 
                    continue;
                
                $subbasename = basename( $subfile, ".{$ext}" );

                $current[$subbasename] = isset($current[$subbasename]) ? $current[$subbasename] : array();
                $res[$subbasename] = self::parseConfigFile("$filename/{$subfile}", $current[$subbasename], $ext, $eval);
            endwhile;
            closedir($subdir);
            
            return $res;
        endif;
    }

    /**
     * Traitement du fichier de configuration
     * 
     * @param unknown $filename
     * 
     * @return mixed|NULL|\Symfony\Component\Yaml\Tag\TaggedValue|string|\stdClass|NULL[]|\Symfony\Component\Yaml\Tag\TaggedValue[]|string[]|unknown[]|mixed[]
     */
    public static function parseFile( $filename )
    {
        $output = Yaml::parse( file_get_contents( $filename ) );

        return $output;
    }
    
    /**
     * Traitement et interprétation PHP du fichier de configuration
     * 
     * @param unknown $filename
     * 
     * @return array|unknown
     */
    public static function parseAndEval( $filename )
    {
        $input = self::parseFile( $filename );
        
        return self::evalPHP( $input );
    }
    
    /**
     * Interprétation PHP
     */
    public static function evalPHP( $input )
    {
        if( empty( $input ) || ! is_array( $input ) )
            return array();
        
        array_walk_recursive( $input, array( __CLASS__, '_pregReplacePHP' ) );

        return $input;
    }
    
    /**
     * Remplacement du code PHP par sa valeur
     */
    private static function _pregReplacePHP( &$input )
    {
        if( preg_match( '/<\?php(.+?)\?>/is', $input ) )
            $input = preg_replace_callback( '/<\?php(.+?)\?>/is', function( $matches ){ return self::_phpEvalOutput( $matches );}, $input );

        return $input;
    }
    
    /**
     * Récupération de la valeur du code PHP trouvé
     */
    private static function _phpEvalOutput( $matches )
    {
        ob_start();
        eval( $matches[1] );
        $output = ob_get_clean();
        
        return $output;
    }
}