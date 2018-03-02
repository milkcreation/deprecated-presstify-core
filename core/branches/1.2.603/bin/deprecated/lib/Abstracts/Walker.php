<?php
namespace tiFy\Abstracts;

use tiFy\Deprecated\Deprecated;

abstract class Walker extends \tiFy\Lib\Walkers\Base
{
    public function __construct()
    {
        parent::__construct();
        Deprecated::add('function', '\tiFy\Abstracts\Walker', '1.0.384', '\tiFy\Lib\Walkers\Base');
    }
}