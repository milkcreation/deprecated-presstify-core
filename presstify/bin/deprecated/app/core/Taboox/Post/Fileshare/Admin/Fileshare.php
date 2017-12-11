<?php
namespace tiFy\Core\Taboox\Post\Fileshare\Admin;

use tiFy\Deprecated\Deprecated;

class Fileshare extends \tiFy\Core\Taboox\PostType\Fileshare\Admin\Fileshare
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Post\Fileshare\Admin\Fileshare', '1.2.472', '\tiFy\Core\Taboox\PostType\Fileshare\Admin\Fileshare');
    }
}