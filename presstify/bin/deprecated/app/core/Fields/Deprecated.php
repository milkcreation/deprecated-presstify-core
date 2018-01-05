<?php
namespace tiFy\Core\Fields;

class Deprecated
{
    public function __construct()
    {
        add_action('tify_fields_register', [$this, 'tify_fields_register']);
    }

    public function tify_fields_register()
    {

    }
}