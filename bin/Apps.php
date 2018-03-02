<?php
namespace tiFy;

use tiFy\tiFy;
use tiFy\Lib\File;
use Symfony\Component\Yaml\Yaml;
use tiFy\Core\Db\Db;
use tiFy\Core\Labels\Labels;
use tiFy\Core\Templates\Templates;

final class Apps
{
    /**
     * Attributs de configuration
     */
    private static $Config = [
        'Core'       => [],
        'Components' => [],
        'Plugins'    => [],
        'Set'        => [],
        'Schema'     => []
    ];

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
     *          @var mixed $ConfigDefault Attributs de configuration par défaut
     *          @var mixed $Config Attributs de configuration actifs
     *          @var string $OverrideNamespace Espace de nom de surcharge
     *          @var array $OverridePath {
     *              Liste des chemins vers les repertoires de surcharge de l'application
     *
     *              @var array $app {
     *                  Attributs du repertoire de surchage des applications connexes (là où l'application peut surcharger les controleurs des autres applications).
     *
     *                  @var string $url Url vers le repertoire des gabarits
     *                  @var string $path Chemin absolu vers le repertoire des gabarits
     *                  @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *                  @var string $baseurl Url vers le repertoire racine
     *                  @var string $basedir Chemin absolu vers le repertoire
     *                  @var \WP_Error $error Message d'erreur d'accessibilité aux chemins
     *              }
     *              @var array $theme {
     *                  Attributs du repertoire de surchage des gabarits connexes (là où l'application peut surcharger les gabarits des autres applications).
     *
     *                  @var string $url Url vers le repertoire des gabarits
     *                  @var string $path Chemin absolu vers le repertoire des gabarits
     *                  @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *                  @var string $baseurl Url vers le repertoire racine
     *                  @var string $basedir Chemin absolu vers le repertoire
     *                  @var \WP_Error $error Message d'erreur d'accessibilité aux chemins
     *              }
     *              @var array $theme_app {
     *                  Attributs du repertoire de surchage des controleurs de l'application via le theme (là où le thème courant peut surcharger les controleurs de l'application)
     *
     *                  @var string $url Url vers le repertoire des gabarits
     *                  @var string $path Chemin absolu vers le repertoire des gabarits
     *                  @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *                  @var string $baseurl Url vers le repertoire racine
     *                  @var string $basedir Chemin absolu vers le repertoire
     *                  @var \WP_Error $error Message d'erreur d'accessibilité aux chemins
     *              }
     *              @var array $theme_templates {
     *                  Attributs du repertoire de surchage des gabarits de l'application via le theme (là où le thème courant peut surcharger les gabarits de l'application)
     *
     *                  @var string $url Url vers le repertoire des gabarits
     *                  @var string $path Chemin absolu vers le repertoire des gabarits
     *                  @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *                  @var string $baseurl Url vers le repertoire racine
     *                  @var string $basedir Chemin absolu vers le repertoire
     *                  @var \WP_Error $error Message d'erreur d'accessibilité aux chemins
     *              }
     *              @var array $assets {
     *                  Attributs du repertoire de surchage des ressources de l'application (là où récupérer les feuilles de styles CSS, le scripts JS, les images, les SVG)
     *
     *                  @var string $url Url vers le repertoire des gabarits
     *                  @var string $path Chemin absolu vers le repertoire des gabarits
     *                  @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *                  @var string $baseurl Url vers le repertoire racine
     *                  @var string $basedir Chemin absolu vers le repertoire
     *                  @var \WP_Error $error Message d'erreur d'accessibilité aux chemins
     *              }
     *          }
     *          @var null|string $Parent Nom complet de la classe parente (Dans le cas où l'application ferait partie d'un composant natif ou dynamique ou d'une extension ou d'un jeu de fonctionnalité)
     *          @var null|string $ChildNamespace Espace de nom enfant (Dans le cas où l'application ferait partie d'un composant natif ou dynamique ou d'une extension ou d'un jeu de fonctionnalité)
     *      }
     * }
     */
    public static $Registered = [];

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
     *      @var string $Id Identifiant qualificatif de l'applicatif
     *      @var array $Config Attributs de configuration
     * }
     * 
     * @return null|array {
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
     *      @var mixed $ConfigDefault Attributs de configuration par défaut
     *      @var mixed $Config Attributs de configuration actifs
     *      @var string $OverrideNamespace Espace de nom de surcharge
     *      @var array $OverridePath {
     *          Liste des chemins vers les repertoires de surcharge de l'application
     *
     *          @var array $app {
     *              Attributs du repertoire de surchage des applications connexes (là où l'application peut surcharger les controleurs des autres applications).
     *
     *              @var string $url Url vers le repertoire des gabarits
     *              @var string $path Chemin absolu vers le repertoire des gabarits
     *              @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *              @var string $baseurl Url vers le repertoire racine
     *              @var string $basedir Chemin absolu vers le repertoire
     *          }
     *          @var array $theme {
     *              Attributs du repertoire de surchage des gabarits connexes (là où l'application peut surcharger les gabarits des autres applications).
     *
     *              @var string $url Url vers le repertoire des gabarits
     *              @var string $path Chemin absolu vers le repertoire des gabarits
     *              @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *              @var string $baseurl Url vers le repertoire racine
     *              @var string $basedir Chemin absolu vers le repertoire
     *          }
     *          @var array $theme_app {
     *              Attributs du repertoire de surchage des controleurs de l'application via le theme (là où le thème courant peut surcharger les controleurs de l'application)
     *
     *              @var string $url Url vers le repertoire des gabarits
     *              @var string $path Chemin absolu vers le repertoire des gabarits
     *              @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *              @var string $baseurl Url vers le repertoire racine
     *              @var string $basedir Chemin absolu vers le repertoire
     *          }
     *          @var array $theme_templates {
     *              Attributs du repertoire de surchage des gabarits de l'application via le theme (là où le thème courant peut surcharger les gabarits de l'application)
     *
     *              @var string $url Url vers le repertoire des gabarits
     *              @var string $path Chemin absolu vers le repertoire des gabarits
     *              @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *              @var string $baseurl Url vers le repertoire racine
     *              @var string $basedir Chemin absolu vers le repertoire
     *          }
     *      }
     *      @var string $Parent Nom complet de la classe parente (Dans le cas où l'application ferait partie d'un composant natif ou dynamique ou d'une extension ou d'un jeu de fonctionnalité)
     *      @var null|string $ChildNamespace Espace de nom enfant (Dans le cas où l'application ferait partie d'un composant natif ou dynamique ou d'une extension ou d'un jeu de fonctionnalité)
     */
    public static function register($classname, $type = null, $attrs = array())
    {
        if (is_object($classname)) :
            $classname = get_class($classname);
        endif;

        // Bypass
        if (! class_exists($classname)):
            return;
        endif;

        $config_attrs = isset($attrs['Config']) ? $attrs['Config'] : [];

        if (! isset(self::$Registered[$classname])) :
            $ReflectionClass = new \ReflectionClass($classname);
            $ClassName = $ReflectionClass->getName();
            $ShortName = $ReflectionClass->getShortName();
            $LowerName = join('_', array_map('lcfirst', preg_split('#(?=[A-Z])#', lcfirst($ShortName))));
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

            // Configuration par défaut
            if (in_array($Type, ['Core', 'Components', 'Plugins', 'Set'])) :
                $default_filename = $Dirname . '/config/config.yml';
                $ConfigDefault = file_exists($default_filename) ? self::parseAndEval($default_filename) : [];
            else :
                $ConfigDefault = [];
            endif;

            // Configuration
            $Config = \wp_parse_args($config_attrs, $ConfigDefault);

            // Espace de nom de surchage
            $OverrideNamespace = '';

            // Chemins de surcharge
            $OverridePath = [];

            //Nom complet de la classe parente
            $Parent = (isset($attrs['Parent'])) ? $attrs['Parent'] : null;

            //Espace de nom enfant
            $ChildNamespace = null;
        else :
            /**
             * @var mixed $Config Attributs de configuration actifs
             */
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
            'LowerName',
            'Namespace',
            // Chemins d'accès
            'Filename',
            'Dirname',
            'Url',
            'Rel',
            // Attributs de configuration par défaut
            'ConfigDefault',
            // Attributs de configuration actifs
            'Config',
            // Espace de nom de surchage
            'OverrideNamespace',
            // Chemins de surcharge
            'OverridePath',
            // Nom complet de la classe parente
            'Parent',
            // Espace de nom enfant
            'ChildNamespace',
            // Instance de la classe de l'application
            'Instance'
        );
    }
    
    /**
     * Vérification d'existance d'une application selon son type ou non
     * 
     * @param object|string $classname Objet ou Nom de la classe de l'application
     * @param null|string $type Attribut de test vérification du type d'application. Core|Components|Plugins|Set|Customs
     * 
     * @return bool
     */
    public static function is($classname, $type = null)
    {
        if (is_object($classname)) :
            $classname = get_class($classname);
        endif;

        // Test parmis la liste complète des applications
        if (!$type) :
            return isset(self::$Registered[$classname]);
        endif;

        // Vérifie si le type de l'application est un type permis
        if(!in_array($type, ['Core', 'Components', 'Plugins', 'Set', 'Customs'])) :
            return false;
        endif;

        // Vérifie d'abord si l'applications fait partie de la liste globale des applications déclarées
        if(!self::is($classname)) :
            return false;
        endif;

        $QueryClass = "self::query{$type}";
        $QueryApps = call_user_func($QueryClass);

        return isset($QueryApps[$classname]);
    }

    /**
     * Vérifie si une application fait partie des composants natifs déclarés
     *
     * @param object|string $classname Objet ou Nom de la classe de l'application
     *
     * @return bool
     */
    public static function isAppCore($classname)
    {
        return self::is($classname, 'Core');
    }

    /**
     * Vérifie si une application fait partie des composants dynamiques déclarés
     *
     * @param object|string $classname Objet ou Nom de la classe de l'application
     *
     * @return bool
     */
    public static function isAppComponent($classname)
    {
        return self::is($classname, 'Components');
    }

    /**
     * Vérifie si une application fait partie des plugins déclarés
     *
     * @param object|string $classname Objet ou Nom de la classe de l'application
     *
     * @return bool
     */
    public static function isAppPlugin($classname)
    {
        return self::is($classname, 'Plugins');
    }

    /**
     * Vérifie si une application fait partie des jeux de fonctionnalités déclarés
     *
     * @param object|string $classname Objet ou Nom de la classe de l'application
     *
     * @return bool
     */
    public static function isAppSet($classname)
    {
        return self::is($classname, 'Set');
    }

    /**
     * Vérifie si une application fait partie des applications personnalisées déclarées
     *
     * @param object|string $classname Objet ou Nom de la classe de l'application
     *
     * @return bool
     */
    public static function isAppCustom($classname)
    {
        return self::is($classname, 'Customs');
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
        
        foreach ((array)self::$Registered as $classname => $attrs) :
            foreach ($args as $attr => $value):
                if (! isset($attrs[$attr]) || ($attrs[$attr] !== $value)) :
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
     * Récupération la liste des applications personnalisées
     */
    public static function queryCustoms()
    {
        return self::query(array('Type' => 'Customs'));
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
     *      @var mixed $ConfigDefault Attributs de configuration par défaut
     *      @var mixed $Config Attributs de configuration actifs
     *      @var string $OverrideNamespace Espace de nom de surcharge
     *      @var array $OverridePath {
     *          Liste des chemins vers les repertoires de surcharge de l'application
     *
     *          @var array $app {
     *              Attributs du repertoire de surchage des applications connexes (là où l'application peut surcharger les controleurs des autres applications).
     *
     *              @var string $url Url vers le repertoire des gabarits
     *              @var string $path Chemin absolu vers le repertoire des gabarits
     *              @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *              @var string $baseurl Url vers le repertoire racine
     *              @var string $basedir Chemin absolu vers le repertoire
     *          }
     *          @var array $theme {
     *              Attributs du repertoire de surchage des gabarits connexes (là où l'application peut surcharger les gabarits des autres applications).
     *
     *              @var string $url Url vers le repertoire des gabarits
     *              @var string $path Chemin absolu vers le repertoire des gabarits
     *              @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *              @var string $baseurl Url vers le repertoire racine
     *               @var string $basedir Chemin absolu vers le repertoire
     *          }
     *          @var array $theme_app {
     *              Attributs du repertoire de surchage des controleurs de l'application via le theme (là où le thème courant peut surcharger les controleurs de l'application)
     *
     *              @var string $url Url vers le repertoire des gabarits
     *              @var string $path Chemin absolu vers le repertoire des gabarits
     *              @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *              @var string $baseurl Url vers le repertoire racine
     *              @var string $basedir Chemin absolu vers le repertoire
     *           }
     *           @var array $theme_templates {
     *              Attributs du repertoire de surchage des gabarits de l'application via le theme (là où le thème courant peut surcharger les gabarits de l'application)
     *
     *              @var string $url Url vers le repertoire des gabarits
     *              @var string $path Chemin absolu vers le repertoire des gabarits
     *              @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *              @var string $baseurl Url vers le repertoire racine
     *              @var string $basedir Chemin absolu vers le repertoire
     *          }
     *      }
     *      @var null|string $Parent Nom complet de la classe parente (Dans le cas où l'application ferait partie d'un composant natif ou dynamique ou d'une extension ou d'un jeu de fonctionnalité)
     *      @var null|string $ChildClassname Espace de nom enfant (Dans le cas où l'application ferait partie d'un composant natif ou dynamique ou d'une extension ou d'un jeu de fonctionnalité)
     *  }
     */
    public static function getAttrList($classname)
    {
        if (is_object($classname)) :
            $classname = get_class($classname);
        endif;
                
        if (isset(self::$Registered[$classname])) :
            return self::$Registered[$classname];
        endif;
    }

    /**
     * Récupération d'un attribut d'application déclarée
     *
     * @param $name
     * @param string $default Valeur de retour par défaut
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'applicatif
     *
     * @return mixed|string
     */
    public static function getAttr($name, $default = '', $classname)
    {
        if (!$attrs = self::getAttrList($classname)) :
            return $default;
        endif;

        if (isset($attrs[$name])) :
            return $attrs[$name];
        else :
            return $default;
        endif;
    }
    
    /**
     * Définition d'attributs pour une application déclarée
     * 
     * @param mixed $attrs Attributs de l'applicatif 
     * @param object|string $classname Objet ou Nom de la classe de l'applicatif
     * 
     * @return bool
     */
    public static function setAttrList($attrs = array(), $classname)
    {
        if (is_object($classname)) :
            $classname = get_class($classname);
        endif;
        
        if (! isset(self::$Registered[$classname])) :
            return false;
        endif;
        
        self::$Registered[$classname] = wp_parse_args($attrs,self::$Registered[$classname]);
        
        return true;
    }

    /**
     * Définition de la liste des attributs de configuration d'une application déclarée
     *
     * @param string $name Qualification de l'attribut de configuration
     * @param mixed $value Valeur de l'attribut de configuration
     * @param object|string $classname Objet ou Nom de la classe de l'applicatif
     *
     * @return bool
     */
    public static function setConfigAttrList($attrs, $classname)
    {
        if (is_object($classname)) :
            $classname = get_class($classname);
        endif;

        if (! isset(self::$Registered[$classname])) :
            return false;
        endif;

        self::$Registered[$classname]['Config'] = $attrs;

        return true;
    }
    
    /**
     * Définition d'un attributs de configuration d'une application déclarée
     * 
     * @param string $name Qualification de l'attribut de configuration
     * @param mixed $value Valeur de l'attribut de configuration
     * @param object|string $classname Objet ou Nom de la classe de l'applicatif
     * 
     * @return bool
     */
    public static function setConfigAttr($name, $value = '', $classname)
    {
        if (is_object($classname)) :
            $classname = get_class($classname);
        endif;

        if (! isset(self::$Registered[$classname])) :
            return false;
        endif;

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
     * @param string $filename
     * 
     * @return mixed|NULL|\Symfony\Component\Yaml\Tag\TaggedValue|string|\stdClass|NULL[]|\Symfony\Component\Yaml\Tag\TaggedValue[]|string[]|unknown[]|mixed[]
     */
    public static function parseFile($filename)
    {
        $output = Yaml::parse(file_get_contents($filename));

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

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        // Chargement des MustUse
        foreach(glob(TIFY_PLUGINS_DIR . '/*', GLOB_ONLYDIR) as $plugin_dir) :
            if(! file_exists("{$plugin_dir}/MustUse")) :
                continue;
            endif;
            if(! $dh = @ opendir("{$plugin_dir}/MustUse")) :
                continue;
            endif;
            while(($file = readdir( $dh )) !== false) :
                if(substr( $file, -4 ) == '.php') :
                    include_once("{$plugin_dir}/MustUse/{$file}");
                endif;
            endwhile;
        endforeach;

        add_action('after_setup_theme', [$this, 'after_setup_theme'], 0);
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

                $basename = basename($file, ".yml");

                // Bypass
                if (! in_array($basename, array('config', 'core', 'components', 'plugins', 'set', 'schema'))) :
                    continue;
                endif;
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

                $basename = basename($file, "." . TIFY_CONFIG_EXT);

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
                if (! file_exists("{$app['base_dir']}/{$dir}")) :
                    continue;
                endif;

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
        // Enregistrement des composants système
        foreach (glob(tiFy::$AbsDir . '/core/*', GLOB_ONLYDIR) as $dirname) :
            $id = basename($dirname);
            $attrs = isset(self::$Config['Core'][$id]) ? self::$Config['Core'][$id] : array();
            Core::register($id, $attrs);
        endforeach;

        // Enregistrement des composants système dépréciés
        foreach (glob(tiFy::$AbsDir . '/bin/deprecated/app/core/*', GLOB_ONLYDIR) as $dirname) :
            $id = basename($dirname);
            $attrs = isset(self::$Config['Core'][$id]) ? self::$Config['Core'][$id] : array();
            Core::register($id, $attrs);
        endforeach;

        // Chargement de la configuration dans l'environnement de surcharge
        if ($app = tiFy::getConfig('app')) :
            foreach (array('core', 'components', 'plugins', 'set') as $type) :
                $Type = ucfirst($type);

                foreach (glob($app['base_dir'] .'/'. $type .'/*/Config.php') as $filename):
                    $Id = basename(dirname($filename));
                    $overrideClass = "{$app['namespace']}\\{$Type}\\{$Id}\\Config";
                    if (class_exists($overrideClass) && is_subclass_of($overrideClass, 'tiFy\\App\\Config')) :
                        $attrs = call_user_func("tiFy\\{$Type}::register", $Id);
                        self::register($overrideClass, null, ['Parent' => $attrs['ClassName']]);
                        call_user_func([$overrideClass, '_ini_set']);
                    endif;
                endforeach;
            endforeach;
        endif;

        // Initialisation des schemas
        reset(self::$Registered);
        foreach (self::$Registered as $classname => $attrs) :
            if(!in_array($attrs['Type'],['Core', 'Components', 'Plugins', 'Set'])) :
                continue;
            endif;

            $dirname = $attrs['Dirname'] . '/config/';
            $schema = array();

            // Récupération du paramétrage natif
            $_dir = @ opendir($dirname);
            if ($_dir) :
                while (($file = readdir($_dir)) !== false) :
                    if (substr($file, 0, 1) == '.') :
                        continue;
                    endif;
                    $basename = basename($file, ".yml");
                    if ($basename !== 'schema') :
                        continue;
                    endif;

                    $schema += self::parseConfigFile("{$dirname}/{$file}", array(), 'yml', true);
                endwhile;
                closedir($_dir);
            endif;

            // Traitement du parametrage
            foreach ((array)$schema as $id => $entity) :
                /// Classe de rappel des données en base
                if (isset($entity['Db'])) :
                    Db::Register($id, $entity['Db']);
                endif;

                /// Classe de rappel des intitulés
                Labels::Register($id, (isset($entity['Labels']) ? $entity['Labels'] : array()));

                /// Gabarits de l'interface d'administration
                if (isset($entity['Admin'])) :
                    foreach ((array)$entity['Admin'] as $i => $tpl) :
                        if (!isset($tpl['db'])) :
                            $tpl['db'] = $id;
                        endif;
                        if (!isset($tpl['labels'])) :
                            $tpl['labels'] = $id;
                        endif;

                        Templates::Register($i, $tpl, 'admin');
                    endforeach;
                endif;

                /// Gabarits de l'interface utilisateur
                if (isset($entity['Front'])) :
                    foreach ((array)$entity['Front'] as $i => $tpl) :
                        if (!isset($tpl['db'])) :
                            $tpl['db'] = $id;
                        endif;
                        if (!isset($tpl['labels'])) :
                            $tpl['labels'] = $id;
                        endif;

                        Templates::Register($i, $tpl, 'front');
                    endforeach;
                endif;
            endforeach;
        endforeach;

        // Instanciation des applications déclarées
        foreach (array('set', 'plugins', 'components', 'core') as $type) :
            do_action("tify_{$type}_register");
            $Type = ucfirst($type);
            if (!$apps = self::query(['Type'=> $Type])) :
                continue;
            endif;

            foreach($apps as $classname => $attrs) :
                // Définition des attributs de l'application parente
                self::setParent($classname);

                // Définition des espaces de nom de surcharge
                self::setOverrideNamespace($classname);

                // Définition de la liste des chemins vers les repertoires de surcharge
                self::setOverridePath($classname);

                tiFy::getContainer()->share($attrs['ClassName'], new $attrs['ClassName']);
            endforeach;
        endforeach;

        // Déclenchement des actions post-paramétrage
        do_action('after_setup_tify');
    }

    /**
     * Définition de l'application parente
     *
     * @param $classname
     *
     * @return void
     */
    public static function setParent($classname)
    {
        $attrs = self::getAttrList($classname);

        if ($attrs['Type'] !== 'Customs') :
            return;
        endif;
        if (!$ParentAttrList = self::_smartAppParentAttrList($attrs['ClassName'])) :
            return;
        endif;

        if(! preg_match('#' . preg_quote($ParentAttrList['OverrideNamespace'], '\\') .'\\\(.*)#', $attrs['Namespace'], $matches) || ! isset($matches[1]) ) :
            return;
        endif;

        $Parent = $ParentAttrList['ClassName'];
        $ChildNamespace = $matches[1];
        $OverrideNamespace = $ParentAttrList['OverrideNamespace'] . '\\'. $ChildNamespace;

        self::setAttrList(compact('OverrideNamespace', 'Parent', 'ChildNamespace'), $attrs['ClassName']);
    }

    /**
     * Recupération intelligente des attributs de l'application parente
     *
     * @param object|string $classname Objet ou Nom de la classe de l'application
     *
     * @return false|array
     */
    private static function _smartAppParentAttrList($classname)
    {
        if (is_object($classname)) :
            $classname = get_class($classname);
        endif;

        // Recherche parmis
        foreach (['Core', 'Components', 'Plugins', 'Set'] as $type) :
            $QueryClass = "self::query{$type}";
            $QueryApps = call_user_func($QueryClass);
            foreach ((array)$QueryApps as $_classname => $_attrs) :
                if(preg_match('#^' . preg_quote($_attrs['Namespace'], '\\') . '#', $classname)) :
                    return $_attrs;
                endif;
            endforeach;
        endforeach;

        // Recherche de parentalité dans les surchages
        if(($ReflectionClass = self::getAttr('ReflectionClass', '', $classname)) && ($parent = $ReflectionClass->getParentClass())) :
            foreach (['Core', 'Components', 'Plugins', 'Set'] as $type) :
                $QueryClass = "self::query{$type}";
                $QueryApps = call_user_func($QueryClass);
                foreach ((array)$QueryApps as $_classname => $_attrs) :
                    if(preg_match('#^' . preg_quote($_attrs['Namespace'], '\\') . '#', $parent->getName())) :
                        return $_attrs;
                    endif;
                endforeach;
            endforeach;
        endif;

        return false;
    }

    /**
     * Définition des espaces de nom de surcharge
     *
     * @param $classname
     *
     * @return void
     */
    public static function setOverrideNamespace($classname)
    {
        $attrs = self::getAttrList($classname);

        switch($attrs['Type']) :
            case 'Set' :
                $OverrideNamespace = 'Set\\' . $attrs['Id'];
                break;
            case 'Plugins' :
            case 'Components' :
            case 'Core' :
                $OverrideNamespace = preg_replace('#^\\\?tiFy\\\#', '', $attrs['Namespace']);
                break;
        endswitch;
        self::setAttrList(compact('OverrideNamespace'), $classname);
    }

    /**
     * Définition de la liste des chemins vers les repertoires de surcharge
     *
     * @param $classname
     *
     * @return void
     */
    public static function setOverridePath($classname)
    {
        $attrs = self::getAttrList($classname);

        if (in_array($attrs['Type'], ['Set', 'Plugins'])) :
            // Attributs du repertoire de surchage des applications connexes (là où l'application peut surcharger les controleurs des autres applications).
            $OverridePath['override_apps'] = [
                'path'    => $attrs['Dirname'] . '/app',
                'url'     => $attrs['Url'] . '/app',
                'subdir'  => '',
                'basedir' => $attrs['Dirname'] . '/app',
                'baseurl' => $attrs['Url'] . '/app',
                'error'   => false
            ];
            // Attributs du repertoire de surchage des gabarits connexes (là où l'application peut surcharger les gabarits des autres applications).
            $OverridePath['override_apps_templates'] = [
                'path'    => $attrs['Dirname'] . '/templates',
                'url'     => $attrs['Url'] . '/templates',
                'subdir'  => '',
                'basedir' => $attrs['Dirname'] . '/templates',
                'baseurl' => $attrs['Url'] . '/templates',
                'error'   => false
            ];
        else :
            $OverridePath['override_apps']       = [
                'path'    => '',
                'url'     => '',
                'subdir'  => '',
                'basedir' => '',
                'baseurl' => '',
                'error'   => new \WP_Error('OverridePathAppsUnavailable',
                    __('Seules les extensions (plugins) et les jeux de fonctionnalités (set) sont en mesure de surcharger les controleurs des autres applications',
                        'tify'))
            ];
            $OverridePath['override_apps_templates'] = [
                'path'    => '',
                'url'     => '',
                'subdir'  => '',
                'basedir' => '',
                'baseurl' => '',
                'error'   => new \WP_Error('OverridePathAppsTemplatesUnavailable',
                    __('Seules les extensions (plugins) et les jeux de fonctionnalités (set) sont en mesure de surcharger les gabarits des autres applications',
                        'tify'))
            ];
        endif;

        $subdir  = \wp_normalize_path($attrs['OverrideNamespace']);
        $_subdir  = preg_replace_callback('#^(Core|Components|Plugins|Set)\/(.*)#',
            function ($m) use($subdir) {
                return (is_array($m) && (count($m) === 3)) ? strtolower($m[1]) . '/' . $m[2] : $subdir;
            }, $subdir);
        $subdir  = \untrailingslashit($_subdir);
        $_subdir = $subdir ? '/' . $subdir : '';

        // Chemins vers le repertoire de stockage des ressources commplémentaires (assets)
        if (in_array($attrs['Type'], ['Core', 'Components', 'Set', 'Plugins']) || $attrs['Parent']) :
            $OverridePath['assets'] = [
                'path'    => tiFy::$AbsDir . '/bin/assets' . $_subdir,
                'url'     => tiFy::$AbsUrl . '/bin/assets' . $_subdir,
                'subdir'  => $subdir,
                'basedir' => tiFy::$AbsDir . '/bin/assets',
                'baseurl' => tiFy::$AbsUrl . '/bin/assets',
                'error'   => false
            ];
        else :
            $subdir = 'assets';
            $OverridePath['assets'] = [
                'path'    => $attrs['Dirname'] . "/{$subdir}",
                'url'     => $attrs['Url'] . "/{$subdir}",
                'subdir'  => $subdir,
                'basedir' => $attrs['Dirname'],
                'baseurl' => $attrs['Url'],
                'error'   => false
            ];
        endif;

        // Chemins de gabarits de surchage du thème
        if (in_array($attrs['Type'], ['Core', 'Components','Set', 'Plugins']) || $attrs['Parent']) :
            $OverridePath['theme_app']       = [
                'path'    => get_template_directory() . '/app' . $_subdir,
                'url'     => get_template_directory_uri() . '/app' . $_subdir,
                'subdir'  => $subdir,
                'basedir' => get_template_directory() . '/app',
                'baseurl' => get_template_directory_uri() . '/app',
                'error'   => false
            ];
        else :
            $OverridePath['theme_app'] = [
                'path'    => '',
                'url'     => '',
                'subdir'  => '',
                'basedir' => '',
                'baseurl' => '',
                'error'   => new \WP_Error('OverridePathThemeAppUnavailable',
                    __('Seules les composants natifs (core), dynamiques (components), les extensions (plugins) et les jeux de fonctionnalités (set) et leurs héritiers sont en mesure d\'être surchargés dans le thème',
                        'tify'))
            ];
        endif;

        $OverridePath['theme_templates'] = [
            'path'    => get_template_directory() . '/templates' . $_subdir,
            'url'     => get_template_directory_uri() . '/templates' . $_subdir,
            'subdir'  => $subdir,
            'basedir' => get_template_directory() . '/templates',
            'baseurl' => get_template_directory_uri() . '/templates',
            'error'   => false
        ];

        if (($parent = $attrs['Parent']) && ($ParentAttrList = self::getAttrList($parent))) :
            $subdir  = \wp_normalize_path($attrs['ChildNamespace']);
            $_subdir = $subdir ? '/' . $subdir : '';

            $OverridePath['parent_templates'] = [
                'path'    => $ParentAttrList['Dirname'] . "{$_subdir}/templates",
                'url'     => $ParentAttrList['Url'] . "{$_subdir}/templates",
                'subdir'  => "{$subdir}/templates",
                'basedir' => $ParentAttrList['Dirname'],
                'baseurl' => $ParentAttrList['Url'],
                'error'   => false
            ];
        else :
            $OverridePath['parent_templates'] = [
                'path'    => '',
                'url'     => '',
                'subdir'  => '',
                'basedir' => '',
                'baseurl' => '',
                'error'   => new \WP_Error('OverridePathParentTemplateUnavailable',
                    __('Seuls les applications héritières sont en mesure de charger les gabarits dans le repertoire de l\'application parente',
                        'tify'))
            ];
        endif;

        self::setAttrList(compact('OverridePath'), $classname);
    }
}