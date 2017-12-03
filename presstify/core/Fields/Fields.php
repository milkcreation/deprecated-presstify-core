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
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des champs
        foreach (glob(self::tFyAppDirname() . '/*', GLOB_ONLYDIR) as $filename) :
            $Name = basename($filename);
            array_push(static::$Registered, $Name);
        endforeach;

        // Déclaration des événement de déclenchement
        $this->tFyAppAddAction('init');
    }

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
        // Auto-chargement de l'initialisation globale des champs
        foreach (static::$Registered as $Name) :
            call_user_func("tiFy\\Core\\Fields\\$Name\\$Name::init");
        endforeach;
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
    final public static function __callStatic($field_name, $args)
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

    /**
     * Mise en file des scripts
     *
     * @param string $name Identifiant de qualification du type de champ appelé (Text|Select|Submit ...)
     *
     * @return void
     */
    final public static function enqueue_scripts($field_name)
    {
        $FieldName = ucfirst($field_name);
        if (!in_array($FieldName, static::$Registered)) :
            return;
        endif;

        $args = array_slice(func_get_args(), 1);

        call_user_func_array("tiFy\\Core\\Fields\\{$FieldName}\\{$FieldName}::enqueue_scripts");
    }
}