<?php
namespace tiFy\Core\Control;

class Deprecated
{
    public function __construct()
    {
        add_action('tify_control_register', [$this, 'tify_control_register']);
    }

    public function tify_control_register()
    {
        Control::register('tiFy\Core\Control\DynamicInputs\DynamicInputs');
        Control::register('tiFy\Core\Control\Token\Token');
    }
}