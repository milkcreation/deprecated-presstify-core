<?php
namespace tiFy\Abstracts;

use tiFy\Deprecated\Deprecated;

abstract class TabWalker extends \tiFy\Lib\Walkers\Tabs
{
    public function __construct()
    {
        parent::__construct();
        Deprecated::add('function', '\tiFy\Abstracts\TabWalker', '1.0.384', '\tiFy\Lib\Walkers\Tabs');
    }
}