<?php
namespace tiFy\Environment\Traits;


trait Config
{
    /**
     * CONTROLEURS
     */
    /**
     * Récupération de la configuration
     * @deprecated
     */
    public static function getConfig($index = null)
    {
        if(is_null($index)) :
            return self::__tFyAppGetConfigList();
        else :
            return self::__tFyAppGetConfig($index, null);
        endif;
    }
    
    /**
     * Récupération de la configuration par défaut
     * @deprecated
     */
    public static function getDefaultConfig($index = null)
    {
        return self::__tFyAppGetDefaultConfig($index);
    }
    
    /**
     * Définition d'un attribut de configuration
     * @deprecated
     */
    public static function setConfig($index, $value)
    {
        self::__tFyAppSetConfig($index, $value);
    }
}