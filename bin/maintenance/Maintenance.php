<?php

namespace tiFy\Maintenance;

use tiFy\tiFy;

class Maintenance
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        tiFy::classLoad('tiFy\Components', dirname(__FILE__) . '/app/components');
        tiFy::classLoad('tiFy\Core', dirname(__FILE__) . '/app/core');
    }
}