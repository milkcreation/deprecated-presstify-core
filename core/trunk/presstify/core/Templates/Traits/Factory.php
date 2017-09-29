<?php
namespace tiFy\Core\Templates\Traits;

use \tiFy\tiFy;
use \tiFy\Apps;
use \tiFy\Lib\File;

trait Factory
{
    /**
     * Définition d'attributs de l'applicatif
     *
     * @param $attrs
     * @param object|string classname Instance (objet) ou Nom de la classe de l'applicatif
     *
     * @return bool
     */
    final public static function tFyAppAttrSetList($attrs, $classname = null)
    {
        if (!$classname) :
            $classname = get_called_class();
        endif;

        return Apps::setAttrList($attrs, $classname);
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
    final public static function tFyAppAttrList($classname = null)
    {
        if (!$classname) :
            $classname = get_called_class();
        endif;

        if (!Apps::is($classname)) :
            // Déclaration de l'application
            Apps::register($classname);

            // Définition des attributs de l'application parente
            Apps::setParent($classname);

            // Définition des espaces de nom de surcharge
            Apps::setOverrideNamespace($classname);

            // Définition de la liste des chemins vers les repertoires de surcharge
            Apps::setOverridePath($classname);
        endif;

        return Apps::getAttrList($classname);
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
        $attrs = self::tFyAppAttrList($classname);

        if (isset($attrs[$attr])) :
            return $attrs[$attr];
        endif;
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
     * Récupération des chemins vers le repertoire des assets (stockage des ressources de type feuilles de styles CSS, scripts JS, images, SVG)
     *
     * @param object|string classname Instance (objet) ou Nom de la classe de l'applicatif
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
    final public static function tFyAppConfigSetAttr($name, $value, $classname = null)
    {
        if (!$classname) :
            $classname = get_called_class();
        endif;

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
    final public static function tFyAppGetTemplatePart($slug, $name = null, $args = [], $classname = null)
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

        if (! $_template_file = self::tFyAppQueryTemplate(current($templates), $templates, $classname)) :
            return;
        endif;

        self::tFyAppTemplateLoad($_template_file, $args);
    }

    /**
     * Récupération d'un gabarit d'affichage
     */
    public static function tFyAppQueryTemplate($template, $templates = array(), $classname = null)
    {
        // Récupération du nom de la classe d'affilitation
        if (is_object($classname)) :
            $classname = get_class($classname);
        endif;
        if (!$classname) :
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
        if (!\is_wp_error($OverridePath['theme_templates']['error'])) :
            foreach ((array)$templates as $template_name) :
                // Bypass
                if (!$template_name)
                    continue;

                $template_file = $OverridePath['theme_templates']['path'] . '/' . $template_name;

                // Bypass - le fichier n'existe pas physiquement
                if (! file_exists($template_file))
                    continue;

                $located = $template_file;
                break;
            endforeach;
        endif;

        // Cas particulier à traiter - App déclaré dans le répertoire de surcharge des apps du thème
        if(!$located && preg_match('#^'. preg_quote(get_template_directory() . '/app', '/') .'#', self::tFyAppDirname($classname))) :
            $subdir = preg_replace('#^'. preg_quote(get_template_directory() . '/app/', '/') .'#', '', self::tFyAppDirname($classname));

            reset($templates);
            // Récupération du gabarit depuis le thème
            foreach ((array)$templates as $template_name) :
                // Bypass
                if (! $template_name)
                    continue;

                $template_file = get_template_directory() . "/templates/{$subdir}/{$template_name}";
                // Bypass - le fichier n'existe pas physiquement
                if (! file_exists($template_file))
                    continue;

                $located = $template_file;
            endforeach;
        endif;


        // Récupération du gabarit original depuis l'application
        if (! $located) :
            reset($templates);
            // Récupération du gabarit depuis le thème
            foreach ((array)$templates as $template_name) :
                // Bypass
                if (! $template_name)
                    continue;

                $template_file = self::tFyAppDirname($classname) . '/templates/' . $template_name;

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
     *
     */
    public static function tFyAppTemplateLoad($__template_file, $args = [])
    {
        if(isset($args[$__template_file])) :
            unset($args[$__template_file]);
        endif;

        extract($args);
        require($__template_file);
    }
}