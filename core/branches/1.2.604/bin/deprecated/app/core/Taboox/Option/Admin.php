<?php
namespace tiFy\Core\Taboox\Option;

use tiFy\Deprecated\Deprecated;

abstract class Admin extends \tiFy\Core\Taboox\Options\Admin
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Option\Admin', '1.2.472', '\tiFy\Core\Taboox\Options\Admin');
    }
}