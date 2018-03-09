<?php
namespace tiFy\App\Traits;

use tiFy\tiFy;
use tiFy\Apps;
use Illuminate\Support\Arr;

trait App
{
    /**
     * FORMATAGE
     */
    /**
     * Formatage lower_name d'une chaine de caractère
     * Converti une chaine de caractère CamelCase en snake_case
     *
     * @param null|string $name
     * @param string $separator
     *
     * @return string
     */
    final public function appLowerName($name = null, $separator = '-')
    {
        return self::tFyAppLowerName($name, $separator);
    }

    /**
     * Formatage UpperName d'une chaine de caratère
     * Converti une chaine de caractère snake_case en CamelCase
     *
     * @param null|string $name Chaine de caractère à traité. Nom de la classe par défaut.
     * @param bool $underscore Conservation des underscores
     *
     * @return string
     */
    final public function appUpperName($name = null, $underscore = true)
    {
        return self::tFyAppUpperName($name, $underscore);
    }

    /**
     * ATTRIBUTS
     */
    /**
     * Récupération de la liste des attributs de l'applicatif
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return array {
     *      Liste des attributs de configuration
     *
     *      @var null|string $Id Identifiant de qualification de l'applicatif
     *      @var string $Type Type d'applicatif Components|Core|Plugins|Set|Customs
     *      @var \ReflectionClass $ReflectionClass Informations sur la classe
     *      @var string $ClassName Nom complet et unique de la classe (espace de nom inclus)
     *      @var string $ShortName Nom court de la classe
     *      @var string $Namespace Espace de Nom
     *      @var string $Filename Chemin absolu vers le fichier de la classe
     *      @var string $Dirname Chemin absolu vers le repertoire racine de la classe
     *      @var string $Url Url absolue vers le repertoire racine de la classe
     *      @var string $Rel Chemin relatif vers le repertoire racine de la classe
     *      @var mixed $Config Attributs de configuration de configuration de l'applicatif
     *      @var array $OverridePath {
     *          Liste des chemins vers le repertoire de stockage des gabarits de l'applicatif
     *
     *          @var array $app {
     *              Attributs du repertoire des gabarits de l'application
     *
     *              @var string $url Url vers le repertoire des gabarits
     *              @var string $path Chemin absolu vers le repertoire des gabarits
     *              @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *              @var string $baseurl Url vers le repertoire racine
     *              @var string $basedir Chemin absolu vers le repertoire
     *          }
     *          @var array $theme {
     *              Attributs du repertoire des gabarits de surcharge du theme actif
     *
     *              @var string $url Url vers le repertoire des gabarits
     *              @var string $path Chemin absolu vers le repertoire des gabarits
     *              @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *              @var string $baseurl Url vers le repertoire racine
     *              @var string $basedir Chemin absolu vers le repertoire
     *          }
     *      }
     * }
     */
    final public function appAttrList($classname = null)
    {
        return self::tFyAppAttrList($classname);
    }

    /**
     * Récupération d'un attribut de l'applicatif
     *
     * @param string $attr Id|Type|ReflectionClass|ClassName|ShortName|Namespace|Filename|Dirname|Url|Rel|Config|OverridePath
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|mixed
     */
    final public function appAttr($attr, $classname = null)
    {
        return self::tFyAppAttr($attr, $classname);
    }

    /**
     * Récupération du nom complet de la classe (Espace de nom inclus)
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    final public function appClassname($classname = null)
    {
        return self::tFyAppClassname($classname);
    }

    /**
     * Récupération du nom cours de la classe (hors espace de nom)
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    final public function appShortname($classname = null)
    {
        return self::tFyAppShortname($classname);
    }

    /**
     * Récupération de l'espace de nom
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    final public function appNamespace($classname = null)
    {
        return self::tFyAppNamespace($classname);
    }

    /**
     * Récupération du chemin absolu de la racine de tiFy
     *
     * @return resource
     */
    final public function appAbsDir()
    {
        return self::tFyAppAbsDir();
    }

    /**
     * Récupération de l'url absolue de la racine de tiFy
     *
     * @return string
     */
    final public function appAbsUrl()
    {
        return self::tFyAppAbsUrl();
    }

    /**
     * Récupération du chemin absolu vers le repertoire racine de la classe
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    final public function appDirname($classname = null)
    {
        return self::tFyAppDirname($classname);
    }

    /**
     * Récupération de l'url absolue vers le repertoire racine de la classe
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    final public function appUrl($classname = null)
    {
        return self::tFyAppUrl($classname);
    }

    /**
     * Récupération du chemin relatif vers le repertoire racine de la classe
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    final public function appRel($classname = null)
    {
        return self::tFyAppRel($classname);
    }

    /**
     * Récupération d'attributs de configuration de l'application
     *
     * @param null|string $attr Attribut de configuration, renvoie la liste complète des attributs de configuration si non qualifié
     * @param void|mixed $default Valeur par défaut de retour
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return mixed
     */
    final public function appConfig($attr = null, $default = '', $classname = null)
    {
        return self::tFyAppConfig($attr, $default, $classname);
    }

    /**
     * EVENEMENTS
     */
    /**
     * Ajout d'un filtre
     *
     * @param string $tag Identification de l'accroche
     * @param string $class_method Méthode de la classe à executer.
     * @param int $priority Priorité d'execution
     * @param int $accepted_args Nombre d'argument permis
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|true
     */
    final public function appAddFilter($tag, $class_method = '', $priority = 10, $accepted_args = 1, $classname = null)
    {
        return self::tFyAppAddFilter($tag, $class_method, $priority, $accepted_args, $classname);
    }

    /**
     * Ajout d'une action
     *
     * @param string $tag Identification de l'accroche
     * @param string $method Méthode de la classe à executer
     * @param int $priority Priorité d'execution
     * @param int $accepted_args Nombre d'argument permis
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|true
     */
    final public function appAddAction($tag, $method = '', $priority = 10, $accepted_args = 1, $classname = null)
    {
        return self::tFyAppAddAction($tag, $method, $priority, $accepted_args, $classname);
    }

    /**
     * AIDE A LA SAISIE
     */
    /**
     * Ajout d'une fonction d'aide à la saisie
     *
     * @param string $tag Identification de l'accroche
     * @param string $method Méthode de la classe à executer
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|callable \add_filter()
     */
    final public function appAddHelper($tag, $method = '', $classname = null)
    {
        return self::tFyAppAddHelper($tag, $method, $classname);
    }

    /**
     * CONTENEUR
     */
    /**
     * Conteneur d’injection de dépendances
     * @see http://container.thephpleague.com/
     *
     * @return \League\Container\Container
     */
    public function appContainer()
    {
        return self::tFyAppContainer();
    }

    /**
     * Ajout d'un conteneur d’injection de dépendances
     *
     * @param string $alias
     *
     * @return \League\Container\Definition\DefinitionInterface
     */
    public function appAddContainer($alias, $concrete = null)
    {
        return self::tFyAppAddContainer($alias, $concrete);
    }

    /**
     * Déclaration d'un conteneur d’injection de dépendances unique
     *
     * @param string $alias
     *
     * @return mixed
     */
    public function appShareContainer($alias, $concrete = null)
    {
        return self::tFyAppShareContainer($alias, $concrete);
    }

    /**
     * Vérification d'existance conteneur d’injection de dépendances
     *
     * @param string $alias
     *
     * @return bool
     */
    public function appHasContainer($alias)
    {
        return self::tFyAppHasContainer($alias);
    }

    /**
     * Récupérateur d'un conteneur d’injection de dépendances
     *
     * @param string $alias
     *
     * @return \League\Container\Container
     */
    public function appGetContainer($alias, $args = [])
    {
        return self::tFyAppGetContainer($alias, $args);
    }

    /**
     * SURCHARGE
     */
    /**
     * Récupération d'une classe de surcharge
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     * @param array $path Liste des chemins à parcourir
     *
     * @return null|string
     */
    public function appGetOverride($classname = null, $path = [])
    {
        return self::tFyAppGetOverride($classname, $path);
    }

    /**
     * Instanciation d'une classe de surcharge
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     * @param array $path Liste des chemins à parcourir
     * @param mixed $passed_args Argument passé au moment de l'instantiaction de la class
     *
     * @return null|object
     */
    public function appLoadOverride($classname = null, $path = [], $passed_args = '')
    {
        return self::tFyAppLoadOverride($classname, $path, $passed_args);
    }

    /**
     * REQUETE
     */
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
    public function appRequestCall($method, $args = [], $type = '')
    {
        return self::tFyAppCallRequestVar($method, $args, $type);
    }

    /**
     * Vérification d'existance d'une variable de requête globale
     *
     * @param string $key Identifiant de qualification de l'argument de requête
     * @param string $type Type de requête à traiter POST|GET|COOKIE|FILES|SERVER ...
     *
     * @return mixed
     */
    public function appRequestHas($key, $type = '')
    {
        return self::tFyAppHasRequestVar($key, $type);
    }

    /**
     * Récupération d'une variable de requête globale
     *
     * @param string $key Identifiant de qualification de l'argument de requête
     * @param mixed $default Valeur de retour par défaut
     * @param string $type Type de requête à traiter POST|GET|COOKIE|FILES|SERVER ...
     *
     * @return mixed
     */
    public function appRequestGet($key, $default = '', $type = '')
    {
        return self::tFyAppGetRequestVar($key, $default, $type);
    }

    /**
     * Définition d'une variable de requête globale
     *
     * @param array $parameters Liste des paramètres. Tableau associatif
     * @param string $type Type de requête à traiter POST|GET|COOKIE|FILES|SERVER ...
     *
     * @return mixed
     */
    public function appRequestAdd($parameters = [], $type = '')
    {
        return self::tFyAppAddRequestVar($parameters, $type);
    }

    /**
     * METHODES STATIQUES
     */
    /**
     * Ajout d'un filtre
     *
     * @param string $tag Identification de l'accroche
     * @param string $method Méthode de la classe à executer.
     * @param int $priority Priorité d'execution
     * @param int $accepted_args Nombre d'argument permis
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|true
     */
    final public function tFyAppAddFilter($tag, $method = '', $priority = 10, $accepted_args = 1, $classname = null)
    {
        if (!$method) :
            $method = $tag;
        endif;

        if (is_string($method) && !preg_match('#::#', $method)) :
            if (!$classname) :
                if ((new \ReflectionMethod($this, $method))->isStatic()) :
                    $classname = get_called_class();
                else :
                    $classname = $this;
                endif;
            endif;
            $method = [$classname, $method];
        endif;

        if (!is_callable($method)) :
            return null;
        endif;

        return \add_filter($tag, $method, $priority, $accepted_args);
    }

    /**
     * Ajout d'une action
     *
     * @param string $tag Identification de l'accroche
     * @param string $method Méthode de la classe à executer
     * @param int $priority Priorité d'execution
     * @param int $accepted_args Nombre d'argument permis
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|true
     */
    final public function tFyAppAddAction($tag, $method = '', $priority = 10, $accepted_args = 1, $classname = null)
    {
        return self::tFyAppAddFilter($tag, $method, $priority, $accepted_args, $classname);
    }

    /**
     * Ajout d'une fonction d'aide à la saisie
     *
     * @param string $tag Identification de l'accroche
     * @param string $method Méthode de la classe à executer
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|callable \add_filter()
     */
    final public function tFyAppAddHelper($tag, $method = '', $classname = null)
    {
        if ($tag && !\function_exists($tag)) :
            $classname = self::_tFyAppParseClassname($classname);
            eval('function ' . $tag . '() { return call_user_func_array("' . $classname . '::' . $method . '", func_get_args());}');
        endif;
    }

    /**
     * ATTRIBUTS
     */
    /**
     * Définition d'attributs de l'applicatif
     *
     * @param array $attrs Liste des attributs à définir
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return bool
     */
    final public static function tFyAppAttrSetList($attrs, $classname = null)
    {
        $classname = self::_tFyAppParseClassname($classname);

        return Apps::setAttrList($attrs, $classname);
    }

    /**
     * Récupération de la liste des attributs de l'applicatif
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return array {
     *      Liste des attributs de configuration
     *
     *      @var null|string $Id Identifiant de qualification de l'applicatif
     *      @var string $Type Type d'applicatif Components|Core|Plugins|Set|Customs
     *      @var \ReflectionClass $ReflectionClass Informations sur la classe
     *      @var string $ClassName Nom complet et unique de la classe (espace de nom inclus)
     *      @var string $ShortName Nom court de la classe
     *      @var string $Namespace Espace de Nom
     *      @var string $Filename Chemin absolu vers le fichier de la classe
     *      @var string $Dirname Chemin absolu vers le repertoire racine de la classe
     *      @var string $Url Url absolue vers le repertoire racine de la classe
     *      @var string $Rel Chemin relatif vers le repertoire racine de la classe
     *      @var mixed $Config Attributs de configuration de configuration de l'applicatif
     *      @var array $OverridePath {
     *          Liste des chemins vers le repertoire de stockage des gabarits de l'applicatif
     *
     *          @var array $app {
     *              Attributs du repertoire des gabarits de l'application
     *
     *              @var string $url Url vers le repertoire des gabarits
     *              @var string $path Chemin absolu vers le repertoire des gabarits
     *              @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *              @var string $baseurl Url vers le repertoire racine
     *              @var string $basedir Chemin absolu vers le repertoire
     *          }
     *          @var array $theme {
     *              Attributs du repertoire des gabarits de surcharge du theme actif
     *
     *              @var string $url Url vers le repertoire des gabarits
     *              @var string $path Chemin absolu vers le repertoire des gabarits
     *              @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *              @var string $baseurl Url vers le repertoire racine
     *              @var string $basedir Chemin absolu vers le repertoire
     *          }
     *      }
     * }
     */
    final public static function tFyAppAttrList($classname = null)
    {
        $classname = self::_tFyAppParseClassname($classname);

        self::_tFyAppRegister($classname);

        return Apps::getAttrList($classname);
    }

    /**
     * Récupération d'un attribut de l'applicatif
     *
     * @param string $attr Id|Type|ReflectionClass|ClassName|ShortName|Namespace|Filename|Dirname|Url|Rel|Config|OverridePath
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|mixed
     */
    final public static function tFyAppAttr($attr, $classname = null)
    {
        $attrs = self::tFyAppAttrList($classname);

        if (isset($attrs[$attr])) :
            return $attrs[$attr];
        endif;
    }

    /**
     * Récupération du nom complet de la classe (Espace de nom inclus)
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    final public static function tFyAppClassname($classname = null)
    {
        return self::tFyAppAttr('ClassName', $classname);
    }

    /**
     * Récupération du nom cours de la classe (hors espace de nom)
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    final public static function tFyAppShortname($classname = null)
    {
        return self::tFyAppAttr('ShortName', $classname);
    }

    /**
     * Récupération de l'espace de nom
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    final public static function tFyAppNamespace($classname = null)
    {
        return self::tFyAppAttr('Namespace', $classname);
    }

    /**
     * Récupération du chemin absolu de la racine de tiFy
     *
     * @return resource
     */
    final public static function tFyAppAbsDir()
    {
        return tiFy::$AbsDir;
    }

    /**
     * Récupération de l'url absolue de la racine de tiFy
     *
     * @return string
     */
    final public function tFyAppAbsUrl()
    {
        return tiFy::$AbsUrl;
    }

    /**
     * Récupération du chemin absolu vers le repertoire racine de la classe
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    final public static function tFyAppDirname($classname = null)
    {
        return self::tFyAppAttr('Dirname', $classname);
    }

    /**
     * Récupération de l'url absolue vers le repertoire racine de la classe
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    final public static function tFyAppUrl($classname = null)
    {
        return self::tFyAppAttr('Url', $classname);
    }

    /**
     * Récupération du chemin relatif vers le repertoire racine de la classe
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    final public static function tFyAppRel($classname = null)
    {
        return self::tFyAppAttr('Rel', $classname);
    }

    /**
     * Récupération du chemin absolu vers le repertoire racine de presstiFy
     *
     * @return null|string
     */
    final public static function tFyAppRootDirname()
    {
        return tiFy::$AbsDir;
    }

    /**
     * Récupération de l'url absolue vers le repertoire racine de presstiFy
     *
     * @return null|string
     */
    final public static function tFyAppRootUrl()
    {
        return tiFy::$AbsUrl;
    }

    /**
     * Liste des chemins vers le repertoire de stockage des gabarits de l'applicatif
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return array
     */
    final public static function tFyAppOverridePath($classname = null)
    {
        return self::tFyAppAttr('OverridePath', $classname);
    }

    /**
     * Récupération des chemins vers le repertoire des assets (stockage des ressources de type feuilles de styles CSS, scripts JS, images, SVG)
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return array {
     *      Attributs du repertoire de surchage des ressources de l'application (là où récupérer les feuilles de styles CSS, le scripts JS, les images, les SVG)
     *
     *      @var string $url Url vers le repertoire des gabarits
     *      @var string $path Chemin absolu vers le repertoire des gabarits
     *      @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *      @var string $baseurl Url vers le repertoire racine
     *      @var string $basedir Chemin absolu vers le repertoire
     *      @var \WP_Error $error Message d'erreur d'accessibilité aux chemins
     * }
     */
    public static function tFyAppAssetsPath($classname = null)
    {
        $OverridePath = self::tFyAppAttr('OverridePath', $classname);

        if (!is_wp_error($OverridePath['assets']['error'])) :
            return $OverridePath['assets'];
        else :
            return $OverridePath['assets'];
        endif;
    }

    /**
     * @param null $asset
     * @param null $classname
     */
    public static function tFyAppAssetsUrl($asset = null, $classname = null)
    {
        $path = self::tFyAppAssetsPath($classname);

        if(!$asset) :
            return $path['url'];
        endif;

        $url = '';
        $_asset = ltrim($asset, '/');

        // Version minifiée de la ressource
        if($min = SCRIPT_DEBUG ? '' : '.min') :
            $ext = pathinfo($_asset, PATHINFO_EXTENSION);
            $min_asset = preg_replace_callback('#(\.' . $ext .')$#', function($m) use ($min) { return $min . $m[1];}, $_asset);

            if (file_exists($path['path'] . "/{$min_asset}")) :
                $url = $path['url'] . "/{$min_asset}";
            endif;
        // Version brute de la ressource
        else :
            if (file_exists($path['path'] . "/{$_asset}")) :
                $url = $path['url'] . "/{$_asset}";
            endif;
        endif;

        if(! $url) :
            if (file_exists(self::tFyAppDirname($classname) . "/{$_asset}")) :
                $url = self::tFyAppUrl($classname) . "/{$_asset}";
            endif;
        endif;
        if(! $url) :
            $url = $asset;
        endif;

        return $url;
    }

    /**
     * CONFIGURATION
     */
    /**
     * Récupération des attributs de configuration par défaut de l'app
     *
     * @param null|string $attr Attribut de configuration, renvoie la liste complète des attributs de configuration si non qualifié
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|mixed
     */
    final public static function tFyAppConfigDefault($attr = null, $classname = null)
    {
        $ConfigDefault = self::tFyAppAttr('ConfigDefault', $classname);

        if (!$attr) :
            return $ConfigDefault;
        elseif (isset($ConfigDefault[$attr])) :
            return $ConfigDefault[$attr];
        endif;
    }

    /**
     * Récupération d'attributs de configuration de l'application
     *
     * @param null|string $attr Attribut de configuration, renvoie la liste complète des attributs de configuration si non qualifié.
     * @param void|mixed $default Valeur par défaut de retour
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return mixed
     */
    final public static function tFyAppConfig($attr = null, $default = '', $classname = null)
    {
        $Config = self::tFyAppAttr('Config', $classname);

        if (!$attr) :
            return $Config;
        else :
            return Arr::get($Config, $attr, $default);
        endif;
    }

    /**
     * Définition de la liste des attributs de configuration de l'application
     *
     * @param mixed $attrs Liste des attributs de configuration
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return bool
     */
    final public static function tFyAppConfigSetAttrList($attrs, $classname = null)
    {
        $classname = self::_tFyAppParseClassname($classname);

        return Apps::setConfigAttrList($attrs, $classname);
    }

    /**
     * Définition d'un attribut de configuration de l'applicatif
     *
     * @param string $name Qualification de l'attribut de configuration
     * @param null|mixed $value Valeur de l'attribut de configuration
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return bool
     */
    final public static function tFyAppConfigSetAttr($name, $value = null, $classname = null)
    {
        $classname = self::_tFyAppParseClassname($classname);

        return Apps::setConfigAttr($name, $value, $classname);
    }

    /**
     * SURCHARGES
     */
    /**
     * FORMATAGE
     */
    /**
     * Formatage lower_name d'une chaine de caractère
     * Converti une chaine de caractère CamelCase en snake_case
     *
     * @param null|string $name
     * @param string $separator
     *
     * @return string
     */
    public static function tFyAppLowerName($name = null, $separator = '-')
    {
        if (!$name) :
            $name = self::tFyAppShortname();
        endif;

        return tiFy::formatLowerName($name, $separator);
    }

    /**
     * Formatage UpperName d'une chaine de caratère
     * Converti une chaine de caractère snake_case en CamelCase
     *
     * @param null|string $name Chaine de caractère à traité. Nom de la classe par défaut.
     * @param bool $underscore Conservation des underscore
     *
     * @return string
     */
    public static function tFyAppUpperName($name = null, $underscore = true)
    {
        if (!$name) :
            $name = self::tFyAppShortname();
        endif;

        return tiFy::formatUpperName($name, $underscore);
    }

    /**
     * Récupération d'une classe de surcharge
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     * @param array $path Liste des chemins à parcourir
     *
     * @return null|string
     */
    final public static function tFyAppGetOverride($classname = null, $path = [])
    {
        $classname = self::_tFyAppParseClassname($classname);

        if (empty($path)) :
            $path = self::tFyAppOverrideClassnameList($classname);
        endif;

        foreach ((array)$path as $override) :
            if (class_exists($override) && is_subclass_of($override, $classname)) :
                $classname = $override;
                break;
            endif;
        endforeach;

        if (class_exists($classname)) :
            return $classname;
        endif;
    }

    /**
     * Chargement d'une classe de surcharge
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     * @param array $path Liste des chemins à parcourir
     * @param mixed $passed_args Argument passé au moment de l'instantiaction de la class
     *
     * @return null|object
     */
    public static function tFyAppLoadOverride($classname = null, $path = [], $passed_args = '')
    {
        if ($classname = self::tFyAppGetOverride($classname, $path)) :
            if (!empty($passed_args)) :
                return new $classname(compact('passed_args'));
            else :
                return new $classname;
            endif;
        endif;
    }

    /**
     * Récupération de la liste des noms de classe de surcharge
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return string[]
     */
    public static function tFyAppOverrideClassnameList($classname = null)
    {
        $classnames = [];

        if ($app = self::tFyAppOverrideAppClassname($classname)) :
            $classnames[] = $app;
        endif;

        if ($sets = self::tFyAppOverrideSetsClassnameList($classname)) :
            foreach($sets as $set) :
                $classnames[] = $set;
            endforeach;
        endif;

        if ($plugins = self::tFyAppOverridePluginsClassnameList($classname)) :
            foreach($plugins as $plugin) :
                $classnames[] = $plugin;
            endforeach;
        endif;

        return $classnames;
    }

    /**
     * Récupération du nom d'appel de la classe dans l'espace de surcharge
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    public static function tFyAppOverrideAppClassname($classname = null)
    {
        if ($namespace = self::tFyAppOverrideAppNamespace($classname)) :
            return $namespace . '\\' . self::tFyAppShortname($classname);
        endif;
    }

    /**
     * Récupération de la liste des noms de classe de surcharge des jeux de fonctionnalités
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    public static function tFyAppOverrideSetsClassnameList($classname = null)
    {
        $classnames = [];

        if ($namespaces = self::tFyAppOverrideSetsNamespaceList($classname)) :
            $classname = self::tFyAppShortname($classname);

            foreach ($namespaces as $namespace) :
                $classnames[] = $namespace . '\\' . $classname;
            endforeach;
        endif;

        return $classnames;
    }

    /**
     * Récupération de la liste des noms de classe de surcharge des extensions
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    public static function tFyAppOverridePluginsClassnameList($classname = null)
    {
        $classnames = [];

        if ($namespaces = self::tFyAppOverridePluginsNamespaceList($classname)) :
            $classname = self::tFyAppShortname($classname);

            foreach ($namespaces as $namespace) :
                $classnames[] = $namespace . '\\' . $classname;
            endforeach;
        endif;

        return $classnames;
    }

    /**
     * Récupération de la liste des espaces de nom de surcharge
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return string[]
     */
    public static function tFyAppOverrideNamespaceList($classname = null)
    {
        $namespaces = [];

        if ($app = self::tFyAppOverrideAppNamespace($classname)) :
            $namespaces[] = $app;
        endif;

        if ($sets = self::tFyAppOverrideSetsNamespaceList($classname)) :
            foreach($sets as $set) :
                $namespaces[] = $set;
            endforeach;
        endif;

        if ($plugins = self::tFyAppOverridePluginsNamespaceList($classname)) :
            foreach($plugins as $plugin) :
                $namespaces[] = $plugin;
            endforeach;
        endif;

        return $namespaces;
    }

    /**
     * Récupération de l'espace de nom de surcharge principal
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    public static function tFyAppOverrideAppNamespace($classname = null)
    {
        $classname = self::_tFyAppParseClassname($classname);
        $sub = preg_replace("/^tiFy\\\/", "", ltrim(self::tFyAppNamespace($classname), '\\'));

        if (($app = tiFy::getConfig('app')) && !empty($app['namespace'])) :
            return $app['namespace'] . ($sub ? "\\{$sub}" : '');
        endif;
    }

    /**
     * Récupération de la liste des espaces de nom de surcharge des jeux de fonctionnalités
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    public static function tFyAppOverrideSetsNamespaceList($classname = null)
    {
        $classname = self::_tFyAppParseClassname($classname);
        $sub = preg_replace("/^tiFy\\\/", "", ltrim(self::tFyAppNamespace($classname), '\\'));

        $namespaces = [];
        foreach ((array)Apps::querySet() as $_classname => $attrs) :
            if($_classname === $classname) :
                continue;
            endif;
            $namespaces[] = "{$attrs['Namespace']}" . ($sub ? "\\App\\{$sub}" : '');
        endforeach;

        return $namespaces;
    }

    /**
     * Récupération de la liste des espaces de nom de surcharge des jeux de fonctionnalités
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return null|string
     */
    public static function tFyAppOverridePluginsNamespaceList($classname = null)
    {
        $classname = self::_tFyAppParseClassname($classname);
        $sub = preg_replace("/^tiFy\\\/", "", ltrim(self::tFyAppNamespace($classname), '\\'));

        $namespaces = [];
        foreach ((array)Apps::queryPlugins() as $_classname => $attrs) :
            if($_classname === $classname) :
                continue;
            endif;
            $namespaces[] = "tiFy\\Plugins\\" . $attrs['Id'] . ($sub ? "\\App\\{$sub}" : '');
        endforeach;

        return $namespaces;
    }

    /**
     * SURCHAGES - Gabarits
     */
    /**
     * Chargement d'un gabarit d'affichage
     *
     * @param string $slug Identification du template ou chemin relatif .
     * @param string $name Modifieur de template.
     * @param mixed $args Liste des arguments passés en variable dans le template
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @see get_template_part()
     *
     * @return null|string
     */
    final public static function tFyAppGetTemplatePart($slug, $name = null, $args = [], $classname = null)
    {
        $classname = self::_tFyAppParseClassname($classname);

        // Définition de la liste des templates
        $templates = [];
        if ($name) :
            $templates[] = "{$slug}-{$name}.php";
        endif;
        $templates[] = "{$slug}.php";

        if (!$_template_file = self::tFyAppQueryTemplate(current($templates), $templates, $classname)) :
            return;
        endif;

        self::tFyAppTemplateLoad($_template_file, $args);
    }

    /**
     * Récupération d'un gabarit d'affichage
     *
     * @param $template
     * @param $templates
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return string
     */
    final public static function tFyAppQueryTemplate($template, $templates = array(), $classname = null)
    {
        $classname = self::_tFyAppParseClassname($classname);

        // Récupération de la liste des chemin de gabarit
        $OverridePath = self::tFyAppOverridePath($classname);

        // Fusion de la liste des gabarits à vérifier
        if ($template && ! in_array($template, $templates)) :
            array_unshift($templates, $template);
        endif;

        $located = '';
        // Récupération du gabarit de surcharge depuis le thème
        if (!\is_wp_error($OverridePath['theme_templates']['error'])) :
            foreach ((array)$templates as $template_name) :
                // Bypass
                if (!$template_name) :
                    continue;
                endif;
                $template_file = $OverridePath['theme_templates']['path'] . '/' . $template_name;

                // Bypass - le fichier n'existe pas physiquement
                if (! file_exists($template_file)) :
                    continue;
                endif;

                $located = $template_file;
                break;
            endforeach;
        endif;

        // Cas particulier à traiter - App déclarée dans le répertoire de surcharge des apps du thème
        if (!$located && preg_match('#^'. preg_quote(get_template_directory() . '/app', '/') .'#', self::tFyAppDirname($classname))) :
            $subdir = preg_replace('#^'. preg_quote(get_template_directory() . '/app/', '/') .'#', '', self::tFyAppDirname($classname));

            reset($templates);
            // Récupération du gabarit depuis le thème
            foreach ((array)$templates as $template_name) :
                // Bypass
                if (!$template_name) :
                    continue;
                endif;

                $template_file = get_template_directory() . "/templates/{$subdir}/{$template_name}";
                // Bypass - le fichier n'existe pas physiquement
                if (!file_exists($template_file)) :
                    continue;
                endif;

                $located = $template_file;
                break;
            endforeach;
        endif;

        // Récupération du gabarit original depuis le repertoire de stockage de l'application
        if (! $located) :
            reset($templates);

            // Récupération du gabarit depuis le thème
            foreach ((array)$templates as $template_name) :
                // Bypass
                if (!$template_name) :
                    continue;
                endif;
                $template_file = self::tFyAppDirname($classname) . '/templates/' . $template_name;

                // Bypass - le fichier n'existe pas physiquement
                if (!file_exists($template_file)) :
                    continue;
                endif;

                $located = $template_file;
                break;
            endforeach;
        endif;

        // Récupération du gabarit de surcharge depuis le thème
        if (!$located && !\is_wp_error($OverridePath['parent_templates']['error'])) :
            reset($templates);

            foreach ((array)$templates as $template_name) :
                // Bypass
                if (!$template_name) :
                    continue;
                endif;
                $template_file = $OverridePath['parent_templates']['path'] . '/' . $template_name;

                // Bypass - le fichier n'existe pas physiquement
                if (! file_exists($template_file)) :
                    continue;
                endif;

                $located = $template_file;
                break;
            endforeach;
        endif;

        // Récupération du gabarit depuis la racine du repertoire de stockage des gabarits du thème
        if (! $located) :
            reset($templates);

            foreach ($templates as $template_name) :
                if (file_exists(get_template_directory() . '/templates/' . $template_name)) :
                    $located = get_template_directory() . '/templates/' . $template_name;
                    break;
                elseif (file_exists($template_name)) :
                    $located = $template_name;
                    break;
                endif;
            endforeach;
        endif;

        if (! $located) :
            reset($templates);

            foreach ($templates as $template_name) :
                if (file_exists(get_template_directory() . '/' . $template_name)) :
                    $located = get_template_directory() . '/' . $template_name;
                    break;
                elseif (file_exists($template_name)) :
                    $located = $template_name;
                    break;
                endif;
            endforeach;
        endif;

        return ($located ? $located : $template);
    }

    /**
     *
     */
    final public static function tFyAppTemplateLoad($__template_file, $args = [])
    {
        if(isset($args[$__template_file])) :
            unset($args[$__template_file]);
        endif;

        extract($args);
        require($__template_file);
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
    public static function tFyAppCallRequestVar($method, $args = [], $type = '')
    {
        return tiFy::callGlobalRequestVar($method, $args, $type);
    }

    /**
     * Vérification d'existance d'une variable de requête globale
     *
     * @param string $key Identifiant de qualification de l'argument de requête
     * @param string $type Type de requête à traiter POST|GET|COOKIE|FILES|SERVER ...
     *
     * @return mixed
     */
    public static function tFyAppHasRequestVar($key, $type = '')
    {
        return self::tFyAppCallRequestVar('has', compact('key'), $type);
    }

    /**
     * Récupération d'une variable de requête globale
     *
     * @param string $key Identifiant de qualification de l'argument de requête
     * @param mixed $default Valeur de retour par défaut
     * @param string $type Type de requête à traiter POST|GET|COOKIE|FILES|SERVER ...
     *
     * @return mixed
     */
    public static function tFyAppGetRequestVar($key, $default = '', $type = '')
    {
        return self::tFyAppCallRequestVar('get', compact('key', 'default'), $type);
    }

    /**
     * Définition d'une variable de requête globale
     *
     * @param string $key Identifiant de qualification de l'argument de requête
     * @param mixed $value Valeur de retour
     * @param string $type Type de requête à traiter POST|GET|COOKIE|FILES|SERVER ...
     *
     * @return mixed
     */
    public static function tFyAppAddRequestVar($parameters = [], $type = '')
    {
        return self::tFyAppCallRequestVar('add', compact('parameters'), $type);
    }

    /**
     * Conteneur d’injection de dépendances
     * @see http://container.thephpleague.com/
     *
     * @return \League\Container\Container
     */
    public static function tFyAppContainer()
    {
        return tiFy::getContainer();
    }

    /**
     * Ajout d'un conteneur d’injection de dépendances
     *
     * @param string $alias
     *
     * @return \League\Container\Definition\DefinitionInterface
     */
    public static function tFyAppAddContainer($alias, $concrete = null)
    {
        return self::tFyAppContainer()->add($alias, $concrete);
    }

    /**
     * Déclaration d'un conteneur d’injection de dépendances unique
     *
     * @param string $alias
     *
     * @return mixed
     */
    public static function tFyAppShareContainer($alias, $concrete = null)
    {
        return self::tFyAppContainer()->share($alias, $concrete);
    }

    /**
     * Vérification d'existance conteneur d’injection de dépendances
     *
     * @param string $alias
     *
     * @return bool
     */
    public static function tFyAppHasContainer($alias)
    {
        return self::tFyAppContainer()->has($alias);
    }

    /**
     * Récupérateur d'un conteneur d’injection de dépendances
     *
     * @param string $alias
     *
     * @return \League\Container\Container
     */
    public static function tFyAppGetContainer($alias, $args = [])
    {
        return self::tFyAppContainer()->get($alias, $args);
    }

    /**
     * Traitement du nom de la classe
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return string
     */
    final protected static function _tFyAppParseClassname($classname = null)
    {
        if (!$classname) :
            $classname = get_called_class();
        endif;

        if (is_object($classname)) :
            $classname = get_class($classname);
        endif;

        return $classname;
    }

    /**
     * Déclaration si nécessaire de l'application
     *
     * @param string $classname Nom de la classe de l'application
     *
     * @return void
     */
    final protected static function _tFyAppRegister($classname)
    {
        if (Apps::is($classname)) :
            return;
        endif;

        // Déclaration de l'application
        Apps::register($classname);

        // Définition des attributs de l'application parente
        Apps::setParent($classname);

        // Définition des espaces de nom de surcharge
        Apps::setOverrideNamespace($classname);

        // Définition de la liste des chemins vers les repertoires de surcharge
        Apps::setOverridePath($classname);
    }

    /**
     * Récupère les arguments d'une classe externe pour ventiler les arguments de l'application (experimental)
     * @see https://stackoverflow.com/questions/119281/how-do-you-copy-a-php-object-into-a-different-object-type
     *
     * @param object|string $classname Instance (objet) ou Nom de la classe de l'application
     *
     * @return void
     */
    final public function tFyAppCloneObjVars($classname)
    {
        if (is_object($classname)) :
            $objVars = get_object_vars($classname);
        elseif(class_exists($classname)) :
            $objVars = get_class_vars($classname);
        else :
            return;
        endif;

        foreach($objVars AS $key => $value) :
            $this->{$key} = $value;
        endforeach;
    }


    /**
     * DEPRECATED
     */
    /**
     * Lancement à l'initialisation de la classe
     * @deprecated
     *
     * @return void
     */
    public function tFyAppOnInit() { }

    /**
     * Ajout d'un filtre
     * @deprecated
     *
     * @param string $tag Identification de l'accroche
     * @param string $class_method Méthode de la classe à executer.
     * @param int $priority Priorité d'execution
     * @param int $accepted_args Nombre d'argument permis
     *
     * @return null|callable \add_filter()
     */
    final public function tFyAppFilterAdd($tag, $class_method = '', $priority = 10, $accepted_args = 1)
    {
        return self::tFyAppAddFilter($tag, $class_method, $priority, $accepted_args);
    }

    /**
     * Ajout d'une action
     * @deprecated
     *
     * @param string $tag Identification de l'accroche
     * @param string $class_method Méthode de la classe à executer
     * @param int $priority Priorité d'execution
     * @param int $accepted_args Nombre d'argument permis
     *
     * @return null|callable \add_action()
     */
    final public function tFyAppActionAdd($tag, $class_method = '', $priority = 10, $accepted_args = 1)
    {
        return self::tFyAppAddAction($tag, $class_method, $priority, $accepted_args);
    }
}