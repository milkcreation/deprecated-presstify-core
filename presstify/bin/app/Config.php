<?php
namespace tiFy\App;

abstract class Config extends Factory
{
    /**
     * Liste des instances déclarées
     */
    private static $Instance    = [];

    /**
     * CONTROLEURS
     */
    /**
     * Initialisation et définition de la surchage de configuration
     *
     * @return array|mixed
     */
    final public static function _ini_set()
    {
        // Vérification d'existance d'une instance de la classe
        if (in_array(get_called_class(), self::$Instance)) :
            return;
        else :
            array_push(self::$Instance, get_called_class());
        endif;

        // Instanciation
        $inst = new static();

        // Bypass
        if (!$parent = self::tFyAppAttr('Parent')) :
            return;
        endif;

        // Récupération des attributs de configuration courant
        $config = self::getAttrList();

        // Traitement global des attributs de configuration
        $config = (array)call_user_func([$inst, 'sets'], $config);

        // Traitement par propriété des attributs de configuration
        if ($matches = preg_grep('#^set_(.*)#', get_class_methods($inst))) :
            foreach ($matches as $method) :
                $key = preg_replace('#^set_#', '', $method);
                $default = isset($config[$key]) ? $config[$key] : '';
                $config[$key] = call_user_func([$inst, $method], $default);
            endforeach;
        endif;

        self::setAttrList($config);
    }

    /**
     * Récupération de la liste des attributs
     *
     * @return mixed
     */
    final public static function getAttrList()
    {
        if (!$parent = self::tFyAppAttr('Parent')) :
            return;
        endif;

        return self::tFyAppConfig(null, null, $parent);
    }

    /**
     * Récupération d'un attribut de configuration
     *
     * @param string $name Nom de l'attribut de configuration
     * @param void|mixed $default Valeur de retour par défaut
     *
     * @return null|mixed
     */
    final public static function getAttr($name, $default)
    {
        if (!$parent = self::tFyAppAttr('Parent')) :
            return;
        endif;

        return self::tFyAppConfig($name, $default, $parent);
    }

    /**
     * Définition de la liste des attributs de configuration
     *
     * @return bool
     */
    final public static function setAttrList($attrs)
    {
        if (!$parent = self::tFyAppAttr('Parent')) :
            return;
        endif;

        return self::tFyAppConfigSetAttrList($attrs, $parent);
    }

    /**
     * Définition d'un attribut de configuration
     *
     * @param string $name Nom de de l'attribut de configuration
     * @param null|mixed $value Valeur de l'attribut de configuration
     *
     * @return bool
     */
    final public static function setAttr($name, $value = null)
    {
        if (!$parent = self::tFyAppAttr('Parent')) :
            return;
        endif;

        return self::tFyAppConfigSetAttr($name, $value, $parent);
    }

    /**
     * Définition globale des attributs de configuration
     * 
     * @param mixed $attrs Liste des attributs existants
     * 
     * @return array|mixed
     */
    public function sets($attrs = [])
    {
        return $attrs;
    }
}