<?php
namespace tiFy\Core\Fields;

class Fields extends \tiFy\App\Core
{
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
     *
     * @return void
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
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        // Déclaration des événement de déclenchement
        $this->tFyAppActionAdd('init');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Appel d'un champ
     *
     * @param string $name Identifiant de qualification du type de champ appelé (Text|Select|Submit ...)
     * @param array $args {
     *      Liste des attributs de configuration
     *
     *      @var array $attrs Attributs de configuration du champ
     *      @var bool $echo Activation de l'affichage du champ
     *
     * return null|callable
     */
    public static function __callStatic($field_name, $args)
    {
        $FieldName = ucfirst($field_name);
        if (!in_array($FieldName, static::$Registered)) :
            return;
        endif;

        $echo = isset($args[1]) ? $args[1] : true;

        $id = null;
        if (!isset($args[0])) :
            $attrs = [];
        else :
            $attrs = $args[0];
        endif;

        if ($echo) :
            call_user_func_array("tiFy\\Core\\Fields\\{$FieldName}\\{$FieldName}::display", compact('id', 'attrs'));
        else :
            return call_user_func_array("tiFy\\Core\\Fields\\{$FieldName}\\{$FieldName}::content", compact('id', 'attrs'));
        endif;
    }
}