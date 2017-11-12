<?php
namespace tiFy\Core\Fields;

class Fields extends \tiFy\App\Core
{
    /**
     * Liste des actions à déclencher
     * @var string[]
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference
     */
    protected $tFyAppActions = ['init'];

    /**
     * Liste des types de champs déclarés
     * @var array
     */
    private static $Registered = [];

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    public function init()
    {
        foreach (glob(self::tFyAppDirname() . '/*', GLOB_ONLYDIR) as $filename) :
            $FieldName = basename($filename);
            array_push(static::$Registered, $FieldName);
            call_user_func("tiFy\\Core\\Fields\\$FieldName\\$FieldName::init");
        endforeach;
    }

    /**
     * Appel d'un champ
     *
     * @param $name
     * @param $arguments
     *
     * return null|callable
     */
    public static function __callStatic($name, $args)
    {
        $FieldName = ucfirst($name);
        if (!in_array($FieldName, static::$Registered)) :
            return;
        endif;

        $echo = isset($args[1]) ? $args[1] : true;

        if (!isset($args[0])) :
            $args[0] = [];
        endif;

        if ($echo) :
            call_user_func("tiFy\\Core\\Fields\\$FieldName\\$FieldName::display", $args[0]);
        else :
            return call_user_func("tiFy\\Core\\Fields\\$FieldName\\$FieldName::content", $args[0]);
        endif;
    }
}