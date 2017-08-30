<?php
namespace tiFy\Environment\Traits;

use tiFy\tiFy;
use tiFy\Lib\File;
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
        Deprecated::addFunction('self::setConfig', '1.0.371', 'self::tFyAppConfigSet');
        return self::tFyAppConfigSet($index, $value);
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
     * Constructeur
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
    }
    
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
     * Liste des arguments pouvant être récupérés
     * @deprecated
     */
    private $GetPathAttrs       = array( 'ReflectionClass', 'ClassShortName', 'Filename', 'Dirname', 'Basename', 'Url' );
    
    /**
     * Définition des informations de la classe
     * @deprecated
     */
    private function setReflectionClass()
    {
        return $this->ReflectionClass = new \ReflectionClass(get_called_class());
    }
     
    /**
     * Définition du chemin absolu vers le fichier de déclaration de la classe fille
     * @deprecated
     */
    private function setFilename()
    {
        return $this->Filename = self::getFilename();
    }
    
    /**
     * Définition du chemin absolu vers le dossier racine de la classe fille
     * @deprecated
     */
    private function setDirname()
    {
        return $this->Dirname = self::tFyAppDirname();
    }
    
    /**
     * Définition du nom du dossier racine de la classe fille
     * @deprecated
     */
    private function setBasename()
    {                
        return $this->Basename = basename( self::tFyAppDirname() );
    }
    
    /**
     * Définition de l'url absolue vers le dossier racine de la classe fille
     * @deprecated
     */
    private function setUrl()
    {        
        return $this->Url = self::tFyAppUrl();
    }
}