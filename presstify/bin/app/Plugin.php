<?php

namespace tiFy\App;

use tiFy\App\Factory;

abstract class Plugin extends Factory
{
    /**
     * Données de plugin
     * @var mixed
     */
    protected static $PluginData = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
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
        $attrs = self::tFyAppAttrList($classname);

        if (!static::$PluginData[$classname]) :
            static::$PluginData[$classname] = \get_plugin_data($attrs['Filename']);
        endif;

        if (!$data) :
            return isset(static::$PluginData[$classname]) ? static::$PluginData[$classname] : [];
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