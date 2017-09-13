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
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    public function init()
    {
        foreach (glob(self::tFyAppDirname() . '/*', GLOB_ONLYDIR) as $filename) :
            $FieldName = basename($filename);
            call_user_func("tiFy\\Core\\Fields\\$FieldName\\$FieldName::init");
        endforeach;
    }
}