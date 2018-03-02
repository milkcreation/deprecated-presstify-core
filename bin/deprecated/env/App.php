<?php
namespace tiFy\Environment;

use tiFy\Deprecated\Deprecated;

abstract class App extends \tiFy\App\Factory
{
    public function __construct()
    {
        parent::__construct();
        Deprecated::add('function', '\tiFy\Environment\App', '1.0.371', '\tiFy\App\Factory');
    }
}