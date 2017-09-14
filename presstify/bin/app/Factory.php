<?php

namespace tiFy\App;

use tiFy\Apps;
use tiFy\Lib\StdClass;
use tiFy\Core\Db\Db;
use tiFy\Core\Labels\Labels;
use tiFy\Core\Templates\Templates;
use tiFy\Environment\Traits\Old;

abstract class Factory
{
    use Traits\Controllers,
        Traits\Getter,
        Traits\Helpers,
        Traits\HelpersNew,
        Traits\Path,
        Traits\Setter,
        Old {
        Traits\Getter::__get            as private __GetterGet;
        Traits\Getter::__isset          as private __GetterIsset;
        Traits\Helpers::__construct     as private __HelpersConstruct;
        Traits\HelpersNew::__construct  as private __HelpersNewConstruct;
        Traits\Path::__get              as private __PathGet;
        Traits\Path::__isset            as private __PathIsset;
        Traits\Setter::__set            as private __SetterSet;
        Old::__construct                as private __OldConstruct;
    }

    /**
     * Liste des actions à déclencher
     * @var string[]
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference
     */
    protected $tFyAppActions = [];

    /**
     * Cartographie des méthodes de rappel des actions
     * @var mixed
     */
    protected $tFyAppActionsMethods = [];

    /**
     * Ordre de priorité d'exécution des actions
     * @var mixed
     */
    protected $tFyAppActionsPriority = [];

    /**
     * Nombre d'arguments autorisés
     * @var mixed
     */
    protected $tFyAppActionsArgs = [];

    /**
     * Liste des filtres à déclencher
     */
    protected $tFyAppFilters = [];

    /**
     * Cartographie des méthodes de rappel des filtres
     */
    protected $tFyAppFiltersMethods = [];

    /**
     * Ordres de priorité d'exécution des filtres
     */
    protected $tFyAppFiltersPriority = [];

    /**
     * Nombre d'arguments autorisés
     */
    protected $tFyAppFiltersArgs = [];

    /**
     * CONTROLEURS
     */
    /**
     * Définition d'attributs de l'applicatif
     *
     * @param $attrs
     * @param object|string classname Instance (objet) ou Nom de la classe de l'applicatif
     *
     * @return bool
     */
    final public static function tFyAppAttrsSet($attrs, $classname = null)
    {
        if (!$classname)
            $classname = get_called_class();

        return Apps::setAttrs($attrs, $classname);
    }

    /**
     * Récupération de la liste des attributs de l'applicatif
     *
     * @param object|string classname
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
    final public static function tFyAppAttrs($classname = null)
    {
        if (!$classname)
            $classname = get_called_class();

        if (!Apps::is($classname))
            Apps::register($classname);

        return Apps::getAttrs($classname);
    }

    /**
     * Récupération d'un attribut de l'applicatif
     *
     * @param string $attr Id|Type|ReflectionClass|ClassName|ShortName|Namespace|Filename|Dirname|Url|Rel|Config|OverridePath
     * @param object|string classname Instance (objet) ou Nom de la classe de l'applicatif
     *
     * @return NULL|mixed
     */
    final public static function tFyAppAttr($attr, $classname = null)
    {
        $attrs = self::tFyAppAttrs($classname);

        if (isset($attrs[$attr]))
            return $attrs[$attr];
    }

    /**
     * Récupération du nom complet de la classe (Espace de nom inclus)
     *
     * @param object|string classname Instance (objet) ou Nom de la classe de l'applicatif
     *
     * @return NULL|string
     */
    final public static function tFyAppClassname($classname = null)
    {
        return self::tFyAppAttr('ClassName', $classname);
    }

    /**
     * Récupération du chemin absolu vers le repertoire racine de la classe
     *
     * @param object|string classname Instance (objet) ou Nom de la classe de l'applicatif
     *
     * @return NULL|string
     */
    final public static function tFyAppDirname($classname = null)
    {
        return self::tFyAppAttr('Dirname', $classname);
    }

    /**
     * Récupération de l'url absolue vers le repertoire racine de la classe
     *
     * @param object|string classname Instance (objet) ou Nom de la classe de l'applicatif
     *
     * @return NULL|string
     */
    final public static function tFyAppUrl($classname = null)
    {
        return self::tFyAppAttr('Url', $classname);
    }

    /**
     * Récupération du chemin relatif vers le repertoire racine de la classe
     *
     * @param object|string classname Instance (objet) ou Nom de la classe de l'applicatif
     *
     * @return NULL|string
     */
    final public static function tFyAppRel($classname = null)
    {
        return self::tFyAppAttr('Rel', $classname);
    }

    /**
     * Récupération des attributs de configuration par défaut de l'app
     *
     * @param NULL|string $attr Attribut de configuration, renvoie la liste complète des attributs de configuration si non qualifié
     * @param object|string classname Instance (objet) ou Nom de la classe de l'applicatif
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
     * Récupération d'attributs de configuration de l'applicatif
     *
     * @param NULL|string $attr Attribut de configuration, renvoie la liste complète des attributs de configuration si non qualifié
     * @param void|mixed $default Valeur par défaut de retour
     * @param object|string classname Instance (objet) ou Nom de la classe de l'applicatif
     *
     * @return mixed
     */
    final public static function tFyAppConfig($attr = null, $default = '', $classname = null)
    {
        $Config = self::tFyAppAttr('Config', $classname);

        if (!$attr) :
            return $Config;
        elseif (isset($Config[$attr])) :
            return $Config[$attr];
        else :
            return $default;
        endif;
    }

    /**
     * Définition d'un attribut de configuration de l'applicatif
     *
     * @param string $name Qualification de l'attribut de configuration
     * @param mixed $value Valeur de l'attribut de configuration
     * @param object|string classname Instance (objet) ou Nom de la classe de l'applicatif
     *
     * @return bool
     */
    final public static function tFyAppConfigSet($name, $value, $classname = null)
    {
        if (!$classname)
            $classname = get_called_class();

        return Apps::setConfigAttr($name, $value, $classname);
    }

    /**
     * Liste des chemins vers le repertoire de stockage des gabarits de l'applicatif
     *
     * @param object|string classname Instance (objet) ou Nom de la classe de l'applicatif
     *
     * @return array {
     *      @var array $app {
     *          Attributs du repertoire des gabarits de l'application
     *
     *          @var string $url Url vers le repertoire des gabarits
     *          @var string $path Chemin absolu vers le repertoire des gabarits
     *          @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *          @var string $baseurl Url vers le repertoire racine
     *          @var string $basedir Chemin absolu vers le repertoire
     *      }
     *      @var array $theme {
     *         Attributs du repertoire des gabarits de surcharge du theme actif
     *
     *         @var string $url Url vers le repertoire des gabarits
     *         @var string $path Chemin absolu vers le repertoire des gabarits
     *         @var string $subdir Chemin relatif vers le sous-repertoire des gabarits
     *         @var string $baseurl Url vers le repertoire racine
     *         @var string $basedir Chemin absolu vers le repertoire
     *      }
     * }
     */
    final public static function tFyAppOverridePath($classname = null)
    {
        return self::tFyAppAttr('OverridePath', $classname);
    }

    /**
     * Chargement d'un gabarit d'affichage
     *
     * @param string $slug Identification du template ou chemin relatif .
     * @param string $name Modifieur de template.
     * @param mixed $args Liste des arguments passés en variable dans le template
     * @param object|string classname Instance (objet) ou Nom de la classe de l'applicatif
     *
     * @see get_template_part()
     *
     * @return null|string
     */
    final public static function tFyAppGetTemplatePart($slug, $name = null, $args = array(), $classname = null)
    {
        // Récupération du nom de la classe d'affilitation
        if (is_object($classname)) :
            $classname = get_class($classname);
        endif;
        if (! $classname) :
            $classname = get_called_class();
        endif;

        // Définition de la liste des templates
        $templates = [];
        if ($name) :
            $templates[] = "{$slug}-{$name}.php";
        endif;
        $templates[] = "{$slug}.php";

        
        if (! $_template_file = self::tFyAppQueryTemplate(current($templates), $templates, $classname))
            return;

        extract($args);
        require($_template_file);
    }

    /**
     * Récupération d'un gabarit d'affichage
     *
     *
     *
     */
    public static function tFyAppQueryTemplate($template, $templates = array(), $classname = null)
    {
        // Récupération du nom de la classe d'affilitation
        if (is_object($classname)) :
            $classname = get_class($classname);
        endif;
        if (! $classname) :
            $classname = get_called_class();
        endif;

        // Récupération de la liste des chemin de gabarit
        $OverridePath = self::tFyAppOverridePath($classname);

        // Fusion de la liste des gabarits à vérifier
        if ($template && ! in_array($template, $templates)) :
            array_unshift($templates, $template);
        endif;

        $located = '';
        // Récupération du gabarit de surcharge depuis le thème
        foreach ((array)$templates as $template_name) :
            // Bypass
            if (! $template_name)
                continue;

            $template_file = $OverridePath['theme_templates']['path'] . '/' . $template_name;

            // Bypass - le fichier n'existe pas physiquement
            if (! file_exists($template_file))
                continue;

            $located = $template_file;
            break;
        endforeach;

        // Récupération du gabarit original depuis l'application
        if (! $located) :
            reset($templates);
            // Récupération du gabarit depuis le thème
            foreach ((array)$templates as $template_name) :
                // Bypass
                if (! $template_name )
                    continue;

                $template_file = $OverridePath['theme_app']['path'] . '/' . $template_name;

                // Bypass - le fichier n'existe pas physiquement
                if (! file_exists($template_file))
                    continue;

                $located = $template_file;
            endforeach;
        endif;

        if (! $located ) :
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

        return ($located ? $located : $template);
    }

    /**
     * CONSTRUCTEUR
     *
     * @todo revoir la syntaxe pour simplifier
     *
     * @return void
     */
    public function __construct()
    {
        $this->__OldConstruct();

        // Récupération des attributs
        $attrs = self::tFyAppAttrs();

        // Initialisation de la configuration
        // Définition de la configuration par défaut
        $filename = $attrs['Dirname'] . '/config/config.yml';
        $ConfigDefault = file_exists($filename) ? Apps::parseAndEval($filename) : array();
        $Config = wp_parse_args($attrs['Config'], $ConfigDefault);

        // Surcharge de configuration "Dynamique"
        if (in_array($attrs['Type'], array('Core', 'Components', 'Plugins', 'Set'))) :
            foreach ((array)StdClass::getOverrideNamespaceList() as $namespace) :
                $overrideNamespace = preg_replace('#\\\?tiFy\\\#', '', $attrs['Namespace']);
                $overrideClass = $namespace . "\\" . $overrideNamespace . "\\Config";
                $abstractClass = "\\tiFy\\App\\Config";

                if (class_exists($overrideClass) && is_subclass_of($overrideClass, $abstractClass)) :
                    $overrideConf = new $overrideClass;
                    $Config = $overrideConf->filter($Config);
                endif;
            endforeach;
        endif;

        self::tFyAppAttrsSet(compact('Config', 'ConfigDefault'));

        // Initialisation des schemas
        $dirname = $attrs['Dirname'] . '/config/';
        $schema = array();

        // Récupération du paramétrage natif
        $_dir = @ opendir($dirname);
        if ($_dir) :
            while (($file = readdir($_dir)) !== false) :
                if (substr($file, 0, 1) == '.')
                    continue;
                $basename = basename($file, ".yml");
                if ($basename !== 'schema')
                    continue;

                $schema += Apps::parseConfigFile("{$dirname}/{$file}", array(), 'yml', true);
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
                    if (!isset($tpl['db']))
                        $tpl['db'] = $id;
                    if (!isset($tpl['labels']))
                        $tpl['labels'] = $id;

                    Templates::Register($i, $tpl, 'admin');
                endforeach;
            endif;

            /// Gabarits de l'interface utilisateur
            if (isset($entity['Front'])) :
                foreach ((array)$entity['Front'] as $i => $tpl) :
                    if (!isset($tpl['db']))
                        $tpl['db'] = $id;
                    if (!isset($tpl['labels']))
                        $tpl['labels'] = $id;

                    Templates::Register($i, $tpl, 'front');
                endforeach;
            endif;
        endforeach;

        // Définition des actions à déclencher
        foreach ($this->tFyAppActions as $tag) :
            $priority = isset($this->tFyAppActionsPriority[$tag]) ? (int)$this->tFyAppActionsPriority[$tag] : 10;
            $accepted_args = isset($this->tFyAppActionsArgs[$tag]) ? (int)$this->tFyAppActionsArgs[$tag] : 1;

            if (!isset($this->tFyAppActionsMethods[$tag])) :
                $function_to_add = array($this, (string)$tag);
            else :
                $function_to_add = array($this, (string)$this->tFyAppActionsMethods[$tag]);
            endif;

            \add_action($tag, $function_to_add, $priority, $accepted_args);
        endforeach;

        // Définition des filtres à déclencher
        foreach ($this->tFyAppFilters as $tag) :
            $priority = isset($this->tFyAppFiltersPriority[$tag]) ? (int)$this->tFyAppFiltersPriority[$tag] : 10;
            $accepted_args = isset($this->tFyAppFiltersArgs[$tag]) ? (int)$this->tFyAppFiltersArgs[$tag] : 1;

            if (!isset($this->tFyAppFiltersMethods[$tag])) :
                $function_to_add = array($this, (string)$tag);
            else :
                $function_to_add = array($this, (string)$this->tFyAppFiltersMethods[$tag]);
            endif;

            \add_filter($tag, $function_to_add, $priority, $accepted_args);
        endforeach;

        $this->__HelpersConstruct();
        $this->__HelpersNewConstruct();
    }

    /**
     * Appel de méthode
     */
    public function __call($method_name, $arguments)
    {
        // Exécution des actions à déclencher
        if (in_array($method_name, $this->tFyAppActions) && method_exists($this, $method_name)) :
            return call_user_func_array(array($this, $method_name), $arguments);
        // Exécution des filtres à déclencher
        elseif (in_array($method_name, $this->CallFilters) && method_exists($this, $method_name)) :
            return call_user_func_array(array($this, $method_name), $arguments);
        endif;
    }

    /**
     * Récupération d'attributs
     * @deprecated
     */
    public function __get($name)
    {
        if ($__get = $this->__EnvGet($name))
            return $__get;
        elseif ($__get = $this->__PathGet($name))
            return $__get;
        elseif ($__get = $this->__GetterGet($name))
            return $__get;

        return false;
    }

    /**
     * Vérification d'existance d'attribut
     * @deprecated
     */
    public function __isset($name)
    {
        if ($__isset = $this->__GetterIsset($name)) :
            return $__isset;
        elseif ($__isset = $this->__PathIsset($name)) :
            return $__isset;
        endif;

        return false;
    }

    /**
     * Définition d'attribut
     * @deprecated
     */
    public function __set($name, $value)
    {
        if ($__set = $this->__EnvSet($name, $value))
            return $__set;
        elseif ($__set = $this->__SetterSet($name, $value))
            return $__set;

        return null;
    }
}