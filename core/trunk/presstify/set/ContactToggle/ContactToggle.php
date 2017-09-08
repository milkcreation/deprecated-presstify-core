<?php
namespace tiFy\Set\ContactToggle;

use tiFy\Core\Control\Control;

class ContactToggle extends \tiFy\App\Set
{
    /**
     * Liste des actions à déclencher
     * @var string[]
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference
     */
    protected $tFyAppActions = array(
        'tify_control_register'
    );

    /**
     * DECLENCHEURS
     */
    /**
     * Déclaration de Control
     */
    final public function tify_control_register()
    {
        Control::register(self::getOverride('tiFy\Set\ContactToggle\Control\ContactToggle'));
    }
}