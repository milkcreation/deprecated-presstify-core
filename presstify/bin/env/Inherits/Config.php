<?php
namespace tiFy\Environment\Inherits;

use \tiFy\tiFy;
use \tiFy\Environment\App;
use \tiFy\Params;
use \tiFy\Lib\StdClass;

class Config
{
    /**
     * Configuration par défaut
     * @var null|mixed
     */
    private $Defaults           = null;
    
    /**
     * Attributs de configuration
     * @var null|mixed
     */
    private $Attrs              = null;
    
    /**
     * CONSTRUCTEUR
     */
    public function __construct($classname)
    {
        $attrs = App::__tFyAppGetAttrs($classname);

        // Définition de la configuration par défaut
        $filename = $attrs['Dirname'] .'/config/config.yml';
        $this->Defaults = file_exists($filename) ? Params::parseAndEval($filename) : array();

        // Traitement de la configuration
        if(!empty(tiFy::$Params[$attrs['Type']][$classname])) :
            $this->Attrs = wp_parse_args(tiFy::$Params[$attrs['Type']][$classname], $this->Defaults); 
        else :
            if(!empty(tiFy::$Params[$attrs['Type']][$attrs['ShortName']])) :
                $this->Attrs = wp_parse_args(tiFy::$Params[$attrs['Type']][$attrs['ShortName']], $this->Defaults);
            else :
                $this->Attrs = $this->Defaults;
            endif;
        endif;
        
        // Surcharge de configuration "Dynamique"
        if(preg_match("/^\\\?tiFy\\\((Components|Core|Plugins|Set)\\\.*)/", $attrs['Namespace'], $matches)) :
            foreach((array) StdClass::getOverrideNamespaceList() as $namespace) :
                $overrideClass = $namespace ."\\". $matches[1] ."\\Config"; 
                $abstractClass = "\\tiFy\\Abstracts\\Config"; 
                if(class_exists($overrideClass) && is_subclass_of($overrideClass, $abstractClass)) :
                    $overrideConf = new $overrideClass;
                    $this->Attrs = $overrideConf->filter($this->Attrs);
                endif;
            endforeach;
        endif;
    }
    
    /**
     * Récupération des attributs de configuration par défaut
     * @param null|string $name
     * 
     * @return NULL|mixed
     */
    public function getDefaults($name = null)
    {
        if(is_null($name)) :
            return $this->Defaults;
        endif;
        
        if(isset($this->Defaults[$name])) :
            return $this->Defaults[$name];
        endif;
    }
    
    /**
     * Récupération de la liste complète des attributs de configuration
     * 
     * @return mixed
     */
    public function getList()
    {
        return $this->Attrs;
    }
    
    /**
     * Récupération d'un attribut de configuration
     * @param string $name
     * @param void|mixed $default
     * 
     * @return mixed
     */
    public function get($name, $default = '')
    {
        if(isset($this->Attrs[$name]))
            return $this->Attrs[$name];
        return $default;
    }
    
    /**
     * Définition d'un attribut de configuration
     * @param string $name
     * @param mixed $value
     * 
     * @return void
     */
    public function set($name, $value)
    {
        $this->Attrs[$name] = $value;
    }
}