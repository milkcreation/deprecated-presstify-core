<?php
namespace tiFy\Environment;

abstract class Plugin extends \tiFy\Environment\App
{
    /**
     * Données de plugin
     * @var mixed
     */
    protected static $PluginData    = array();

    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();
        self::initOverrideAutoloader();
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération des données du plugin
     */
    public static function getData($data = null)
    {
        $classname = get_called_class();
        $attrs = self::__tFyAppGetAttrs($classname);
        
        if (! static::$PluginData[$classname]) :
            static::$PluginData[$classname] = \get_plugin_data($attrs['Filename']);
        endif;
        
        if (! $data) :
            return isset(static::$PluginData[$classname]) ? static::$PluginData[$classname] : array();
         elseif (isset(static::$PluginData[$classname][$data])) :
            return static::$PluginData[$classname][$data];
        endif;
    }

    /**
     * Récupération du numéro de version
     */
    public static function getVersion()
    {
        return static::getData('Version');
    }
}