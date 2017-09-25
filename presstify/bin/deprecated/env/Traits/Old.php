<?php
namespace tiFy\Environment\Traits;

use tiFy\Deprecated\Deprecated;

trait Old
{
    /**
     * Récupération de la configuration
     * @deprecated 1.0.371
     */
    public static function getConfig($index = null)
    {
        Deprecated::addFunction('self::getConfig', '1.0.371', 'self::tFyAppConfig');
        return self::tFyAppConfig($index, null);
    }
    
    /**
     * Récupération de la configuration par défaut
     * @deprecated 1.0.371
     */
    public static function getDefaultConfig($index = null)
    {
        Deprecated::addFunction('self::getDefaultConfig', '1.0.371', 'self::tFyAppConfigDefault');
        return self::tFyAppConfigDefault($index);
    }
    
    /**
     * Définition d'un attribut de configuration
     * @deprecated 1.0.371
     */
    public static function setConfig($index, $value)
    {
        Deprecated::addFunction('self::setConfig', '1.0.371', 'self::tFyAppConfigSetAttr');
        return self::tFyAppConfigSetAttr($index, $value);
    }
    
    /** 
     * Récupération du nom court de la classe
     * @deprecated 1.0.371
     */
    public static function classShortName($classname = null)
    {
        Deprecated::addFunction('self::getDirname', '1.0.371', 'self::tFyAppAttr("ShortName")');
        return self::tFyAppAttr('ShortName', $classname);
    }
    
    /**
     * Récupération du répertoire de déclaration de la classe
     * @deprecated 1.0.371
     */
    public static function getFilename($classname = null)
    {
        Deprecated::addFunction('self::getDirname', '1.0.371', 'self::tFyAppAttr("Filename")');
        return self::tFyAppAttr('Filename', $classname);
    }
    /**
     * Récupération du répertoire de déclaration de la classe
     * @deprecated 1.0.371
     */
    public static function getDirname($CalledClass = null)
    {
        Deprecated::addFunction('self::getDirname', '1.0.371', 'self::tFyAppDirname');
        return self::tFyAppDirname($CalledClass);
    }
    
    /**
     * Récupération du répertoire de déclaration de la classe
     * @deprecated 1.0.371
     */
    public static function getUrl( $CalledClass = null )
    {
        Deprecated::addFunction('self::getUrl', '1.0.371', 'self::tFyAppUrl');
        return self::tFyAppUrl($CalledClass);
    }
    
    /**
     * Récupération du répertoire de déclaration de la classe
     * @deprecated 1.0.371
     */
    public static function getRelPath( $CalledClass = null )
    {
        Deprecated::addFunction('self::getRelPath', '1.0.371', 'self::tFyAppRel');
        return self::tFyAppRel($CalledClass);
    }

    /**
     * Définition des informations de la classe
     * @deprecated
     */
    private function setReflectionClass()
    {
        Deprecated::addFunction('self::setReflectionClass', '1.0.412');
        return $this->ReflectionClass = self::tFyAppAttr('ReflectionClass');
    }

    /**
     * Définition du chemin absolu vers le fichier de déclaration de la classe fille
     * @deprecated
     */
    private function setFilename()
    {
        Deprecated::addFunction('self::setFilename', '1.0.412');
        return $this->Filename = self::tFyAppAttr('Filename');
    }

    /**
     * Définition du chemin absolu vers le dossier racine de la classe fille
     * @deprecated
     */
    private function setDirname()
    {
        Deprecated::addFunction('self::setDirname', '1.0.412');
        return $this->Dirname = self::tFyAppDirname();
    }

    /**
     * Définition du nom du dossier racine de la classe fille
     * @deprecated
     */
    private function setBasename()
    {
        Deprecated::addFunction('self::setBasename', '1.0.412');
        return $this->Basename = basename( self::tFyAppDirname());
    }

    /**
     * Définition de l'url absolue vers le dossier racine de la classe fille
     * @deprecated
     */
    private function setUrl()
    {
        Deprecated::addFunction('self::setUrl', '1.0.412');
        return $this->Url = self::tFyAppUrl();
    }

    /**
     * Récupération du répertoire de déclaration de la classe
     * @deprecated
     */
    public static function getAssetsUrl($classname = null)
    {
        Deprecated::addFunction('self::getAssetsUrl', '1.0.412', 'self::tFyAppAssetsUrl');
        return self::tFyAppAssetsUrl(null, $classname);
    }

    /**
     * Récupération du répertoire de stockage des gabarits de l'appli
     * @deprecated
     */
    public static function getAppTemplateDir($classname = null)
    {
        Deprecated::addFunction('self::getAppTemplateDir', '1.0.412');
    }

    /**
     * Récupération du répertoire de stockage des gabarits du theme pour l'appli
     * @deprecated
     */
    public static function getThemeTemplateDir($classname = null)
    {
        Deprecated::addFunction('self::getThemeTemplateDir', '1.0.412');
    }

    /**
     * Récupération du gabarit d'affichage
     * @deprecated
     */
    public static function getQueryTemplate($template = null, $type, $templates = array(), $classname = null)
    {
        Deprecated::addFunction('self::getQueryTemplate', '1.0.412');
    }

    /**
     * Chargement du gabarit d'affichage
     * @deprecated
     */
    public static function getTemplatePart($slug, $name = null, $args = array(), $classname = null)
    {
        Deprecated::addFunction('self::getTemplatePart', '1.0.412');
    }
    
    /**
     * Liste des actions à déclencher
     * @deprecated 1.0.371
     */
    protected $CallActions                = array(); 

    /**
     * Cartographie des méthodes de rappel des actions
     * @deprecated 1.0.371
     */
    protected $CallActionsFunctionsMap    = array();

    /**
     * Ordre de priorité d'exécution des actions
     * @deprecated 1.0.371
     */
    protected $CallActionsPriorityMap    = array();

    /**
     * Nombre d'arguments autorisés
     * @deprecated 1.0.371
     */ 
    protected $CallActionsArgsMap        = array();
    
    /**
     * Filtres à déclencher
     * @deprecated 1.0.371
     */
    protected $CallFilters                = array();
    
    /**
     * Fonctions de rappel des filtres
     * @deprecated 1.0.371
     */
    protected $CallFiltersFunctionsMap    = array();

    /**
     * Ordres de priorité d'exécution des filtres
     * @deprecated 1.0.371
     */
    protected $CallFiltersPriorityMap    = array();

    /**
     * Nombre d'arguments autorisés
     * @deprecated 1.0.371
     */
    protected $CallFiltersArgsMap        = array();

    /**
     * Informations sur la classe
     * @deprecated
     */
    private $ReflectionClass;

    /**
     * Nom court de la classe
     * @deprecated
     */
    private $ClassShortName;

    /**
     * Chemin absolu vers le fichier de déclaration de la classe
     * @deprecated
     */
    private $Filename;

    /**
     * Chemin absolu vers le dossier racine de la classe
     * @deprecated
     */
    private $Dirname;

    /**
     * Nom du dossier racine de la classe
     * @deprecated
     */
    private $Basename;

    /**
     * Url absolue vers la racine de la classe
     * @deprecated
     */
    private $Url;

    /**
     * Url absolue vers  la racine de la classe
     * @deprecated
     */
    protected static $_ClassShortName;

    /**
     * Url absolue vers  la racine de la classe
     * @deprecated
     */
    protected static $_Filename;

    /**
     * Chemin absolu vers le dossier racine de la classe
     * @deprecated
     */
    protected static $_Dirname;

    /**
     * Url absolue vers la racine de la classe
     * @deprecated
     */
    protected static $_Url;

    /**
     * Chemin relatif à la racine de la classe
     * @deprecated
     */
    protected static $_RelPath;

    /**
     * Url absolue vers la racine de la classe
     * @deprecated
     */
    protected static $_AssetsUrl;

    /**
     * Chemin vers un gabarit d'affichage en contexte
     * @deprecated
     */
    protected static $_TemplatePath;

    /**
     * Liste des arguments pouvant être récupérés
     * @deprecated
     */
    protected $GetAttrs = [];

    /**
     * Liste des arguments pouvant être récupérés
     * @deprecated
     */
    protected $GetPathAttrs = ['ReflectionClass', 'ClassShortName', 'Filename', 'Dirname', 'Basename', 'Url'];

    /**
     * Liste des arguments pouvant être récupérés
     * @deprecated
     */
    protected $getattrs = [];

    /**
     * Liste des arguments pouvant être défini
     * @deprecated
     */
    protected $SetAttrs = [];

    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        // Actions à déclencher
        if(! empty($this->CallActions)) :
            Deprecated::addArgument('\tiFy\App\Factory::CallActions', '1.0.371', __('Utiliser \tiFy\App\Factory::tFyAppActions en remplacement', 'tify'));
            $this->tFyAppActions = $this->CallActions;
        endif;
        if(! empty($this->CallActionsFunctionsMap)) :
            Deprecated::addArgument('\tiFy\App\Factory::CallActionsFunctionsMap', '1.0.371', __('Utiliser \tiFy\App\Factory::tFyAppActionsMethods en remplacement', 'tify'));
            $this->tFyAppActionsMethods = $this->CallActionsFunctionsMap;
        endif;
        if(! empty($this->CallActionsPriorityMap)) :
            Deprecated::addArgument('\tiFy\App\Factory::CallActionsPriorityMap', '1.0.371', __('Utiliser \tiFy\App\Factory::tFyAppActionsPriority en remplacement', 'tify'));
            $this->tFyAppActionsPriority = $this->CallActionsPriorityMap;
        endif;
        if(! empty($this->CallActionsArgsMap)) :
            Deprecated::addArgument('\tiFy\App\Factory::CallActionsArgsMap', '1.0.371', __('Utiliser \tiFy\App\Factory::tFyAppActionsArgs en remplacement', 'tify'));
            $this->tFyAppActionsArgs = $this->CallActionsArgsMap;
        endif;
        
        // Filtres à déclencher
        if(! empty($this->CallFilters)) :
            //Deprecated::addArgument('\tiFy\App\Factory::CallFilters', '1.0.371', __('Utiliser \tiFy\App\Factory::tFyAppActions en remplacement', 'tify'));
            $this->tFyAppFilters = $this->CallFilters;
        endif;
        if(! empty($this->CallFiltersFunctionsMap)) :
            //Deprecated::addArgument('\tiFy\App\Factory::CallFiltersFunctionsMap', '1.0.371', __('Utiliser \tiFy\App\Factory::tFyAppFiltersMethods en remplacement', 'tify'));
            $this->tFyAppFiltersMethods = $this->CallFiltersFunctionsMap;
        endif;
        if(! empty($this->CallFiltersPriorityMap)) :
            //Deprecated::addArgument('\tiFy\App\Factory::CallFiltersPriorityMap', '1.0.371', __('Utiliser \tiFy\App\Factory::tFyAppFiltersPriority en remplacement', 'tify'));
            $this->tFyAppFiltersPriority = $this->CallFiltersPriorityMap;
        endif;
        if(! empty($this->CallFiltersArgsMap)) :
            //Deprecated::addArgument('\tiFy\App\Factory::CallFiltersArgsMap', '1.0.371', __('Utiliser \tiFy\App\Factory::tFyAppFiltersArgs en remplacement', 'tify'));
            $this->tFyAppFiltersArgs = $this->CallFiltersArgsMap;
        endif;

        // Attributs de configuration
        if(! empty($this->ReflectionClass)) :
            Deprecated::addArgument('\tiFy\App\Factory::ReflectionClass', '1.0.412', __('Utiliser \tiFy\App\Factory::tFyAppAttr(\'ReflectionClass\') en remplacement', 'tify'));
        endif;
        if(! empty($this->ClassShortName)) :
            Deprecated::addArgument('\tiFy\App\Factory::ClassShortName', '1.0.412', __('Utiliser \tiFy\App\Factory::tFyAppAttr(\'ClassShortName\') en remplacement', 'tify'));
        endif;
        if(! empty($this->Filename)) :
            Deprecated::addArgument('\tiFy\App\Factory::Filename', '1.0.412', __('Utiliser \tiFy\App\Factory::tFyAppAttr(\'Filename\') en remplacement', 'tify'));
        endif;
        if(! empty($this->Dirname)) :
            Deprecated::addArgument('\tiFy\App\Factory::Dirname', '1.0.412', __('Utiliser \tiFy\App\Factory::tFyAppDirname() en remplacement', 'tify'));
        endif;
        if(! empty($this->Basename)) :
            Deprecated::addArgument('\tiFy\App\Factory::Basename', '1.0.412', __('Utiliser basename(\tiFy\App\Factory::tFyAppAttr(\'Dirname\')) en remplacement', 'tify'));
        endif;
        if(! empty($this->Url)) :
            Deprecated::addArgument('\tiFy\App\Factory::Dirname', '1.0.412', __('Utiliser \tiFy\App\Factory::tFyAppUrl() en remplacement', 'tify'));
        endif;

        // Chemin
        if(! empty(static::$_ClassShortName)) :
            Deprecated::addArgument('\tiFy\App\Factory::$_ClassShortName', '1.0.412');
        endif;
        if(! empty(static::$_Filename)) :
            Deprecated::addArgument('\tiFy\App\Factory::$_Filename', '1.0.412');
        endif;
        if(! empty(static::$_Dirname)) :
            Deprecated::addArgument('\tiFy\App\Factory::$_Dirname', '1.0.412');
        endif;
        if(! empty(static::$_Url)) :
            Deprecated::addArgument('\tiFy\App\Factory::$_Url', '1.0.412');
        endif;
        if(! empty(static::$_RelPath)) :
            Deprecated::addArgument('\tiFy\App\Factory::$_RelPath', '1.0.412');
        endif;
        if(! empty(static::$_AssetsUrl)) :
            Deprecated::addArgument('\tiFy\App\Factory::$_AssetsUrl', '1.0.412');
        endif;
        if(! empty(static::$_TemplatePath)) :
            Deprecated::addArgument('\tiFy\App\Factory::$_TemplatePath', '1.0.412');
        endif;
        /** @todo Helpers */
        /*if(! empty($this->GetPathAttrs)) :
            Deprecated::addArgument('\tiFy\App\Factory::GetPathAttrs', '1.0.412');
        endif;
        if(! empty($this->GetAttrs)) :
            Deprecated::addArgument('\tiFy\App\Factory::GetAttrs', '1.0.412');
        endif;
        if(! empty($this->SetAttrs)) :
            Deprecated::addArgument('\tiFy\App\Factory::SetAttrs', '1.0.412');
        endif;
        if(! empty($this->getattrs)) :
            Deprecated::addArgument('\tiFy\App\Factory::getattrs', '1.0.412');
        endif;*/
    }

    /**
     * Récupération des données accessibles
     * @deprecated
     */
    public function __get($name)
    {
        if (in_array($name, $this->GetPathAttrs)) :
            if ( ! $this->{$name}) :
                if (method_exists($this, 'set' . $name)) :
                    return call_user_func([$this, 'set' . $name]);
                endif;
            else :
                return $this->{$name};
            endif;
        elseif (in_array($name, $this->GetAttrs)) :
            return $this->{$name};
        elseif (in_array($name, $this->getattrs)) :
            return $this->{$name};
        endif;

        return false;
    }

    /**
     * Vérification d'existance des données accessibles
     * @deprecated
     */
    public function __isset( $name )
    {
        if (in_array($name, $this->GetPathAttrs)) :
            if ( ! $this->{$name}) :
                if (method_exists($this, 'set' . $name)) :
                    return call_user_func([$this, 'set' . $name]);
                endif;
            endif;

            return isset($this->{$name});
        elseif (in_array($name, $this->GetAttrs)) :
            return isset($this->{$name});
        elseif (in_array($name, $this->getattrs)) :
            return isset($this->{$name});
        endif;

        return false;
    }

    /**
     * Définition des données permises
     * @deprecated
     */
    public function __set($name, $value)
    {
        if (in_array($name, $this->SetAttrs)) :
            return $this->{$name} = $value;
        endif;

        return false;
    }
}