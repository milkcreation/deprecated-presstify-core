<?php
namespace tiFy\Core\Control\_Deprecated;

class _Deprecated
{
    public function __construct()
    {
        \tiFy\Core\Control\Control::register( 'tiFy\Core\Control\_Deprecated\DynamicInputs\DynamicInputs' );

        \tiFy\Core\Control\Control::register( 'tiFy\Core\Control\_Deprecated\Token\Token' );
    }
}