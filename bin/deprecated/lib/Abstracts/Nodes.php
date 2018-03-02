<?php
namespace tiFy\Abstracts;

use tiFy\Deprecated\Deprecated;

abstract class Nodes extends \tiFy\Lib\Nodes\Base
{
    public function __construct()
    {
        Deprecated::add('function', '\tiFy\Abstracts\Nodes', '1.0.384', '\tiFy\Lib\Nodes\Base');
    }
}