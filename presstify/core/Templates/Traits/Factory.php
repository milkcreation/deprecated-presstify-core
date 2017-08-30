<?php
namespace tiFy\Core\Templates\Traits;

use \tiFy\tiFy;
use \tiFy\Apps;
use \tiFy\Lib\File;

trait Factory
{        
    /**
     * Récupération de la liste des attributs de l'applicatif
     * 
     * @param object|string classname
     * 
     * @return array {
     *      @var NULL|string $Id Identifiant de qualification de l'applicatif
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
     * }
     */
    final public static function tFyAppAttrs($classname = null)
    {
        if (! $classname)
            $classname = get_called_class();
        
        if(! Apps::is($classname))
            Apps::register($classname);
        
        return Apps::getAttrs($classname);
    }

    /**
     * Récupération d'un attribut de l'applicatif
     * 
     * @param string $attr Id|Type|ReflectionClass|ClassName|ShortName|Namespace|Filename|Dirname|Url|Rel|Config
     * @param object|string classname Instance (objet) ou Nom de la classe de l'applicatif
     *
     * @return NULL|mixed
     */
    final public static function tFyAppAttr($attr, $classname = null)
    {
        $attrs = self::tFyAppAttrs($classname);
        
        if(isset($attrs[$attr]))
            return $attrs[$attr];
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
     * Récupération du répertoire de déclaration de la classe
     * 
     * @param object|string classname Instance (objet) ou Nom de la classe de l'applicatif
     * 
     * @return NULL|string
     */
    public static function getAssetsUrl($classname = null)
    {                    
        return tiFy::$AbsUrl . '/bin/assets/' . untrailingslashit(File::getRelativeFilename(self::tFyAppDirname($classname), tiFy::$AbsDir));
    }
}